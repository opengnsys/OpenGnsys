<?php
/**
 * @file      tftptools.php
 * @brief     Utilidades para menejar ficheros de arranque TFTP/PXE.
 * @version   1.0.5
 * @copyright GNU Public License v3+
 */


// Ficheros de inclusión.
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");


/**
 * @brief    Sustituye espacio por "_" y quita acentos y tildes.
 * @param    cadena   Cadena a modificar.
 * @return   string   Cadena modificada.
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @author   
 * @date     
*/
function cleanString ($cadena) {
	return strtr ($cadena, " áéíóúñçÁÉÍÓÚÑÇ", "_aeiouncAEIOUNC");
}


/**
 * Función que obtiene la versión del Kernel del cliente que se ejecuta durante el
 * proceso de arranque mediante TFTP/PXE.
 * @brief    Obtiene la versión del Kernel usada en arranque TFTP/PXE.
 * @return   float    Versión del Kernel (Versión.Revisión, con 2 decimales).
 * @versión  1.0.5 - Versión inicial.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-11
 */
function clientKernelVersion () {
	$tftpDir = "/opt/opengnsys/tftpboot";		// Directorio TFTP.
	$kernelFile = "$tftpDir/ogclient/ogvmlinuz";	// Fichero del Kernel

	// Devolver versión del Kernel (Versión.Revisión, con 2 decimales).
	return exec ("file -bkr $kernelFile 2>/dev/null | awk '/Linux/ {for(i=1;i<=NF;i++) if(\$i~/version/) {v=\$(i+1); printf(\"%d\",v); sub(/[0-9]*\./,\"\",v); printf(\".%02d\",v)}}'");
}


/**
 *           createBootMode ($cmd, $bootopt, $hostid, $lang) 
 * @brief    Crea un fichero PXE para el ordenador basado en la plantilla indicada y usando
 *           los datos almacenados en la BD.
 * @param    {Object}  cmd       Objeto de conexión a la base de datos.
 * @param    {String}  bootopt   Plantilla de arranque PXE.
 * @param    {Number}  hostid    Id. del ordenador.
 * @param    {String}  lang      Idioma de arranque.
 * @version  1.0.5 - Primera versión, adaptada de NetBoot Avanzado (Antonio J. Doblas Viso - Universidad de Málaga)
 * @author  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 * @version  1.1.0 - Se incluye la unidad organizativa como parametro del kernel: ogunit=directorio_unidad (ticket #678).
 * @author   Irina Gómez - ETSII Universidad de Sevilla
 * @date     2015-12-16
 * @version  1.1.0 - La segunda fase de carga del ogLive se define en el SERVER para evitar erores de sincronismo entre versiones (ticket #787).
 * @author   Antonio J. Doblas Viso - Universidad de Malaga
 * @date     2017-06-01
 */
function createBootMode ($cmd, $bootopt, $hostid, $lang) {	

	// Plantilla con las opciones por defecto.
	if (empty ($bootopt))  $bootopt = "00unknown";

	// Actualizar opción de arranque para el equipo.
	$cmd->CreaParametro("@arranque",$bootopt,0);
	$cmd->CreaParametro("@idordenador",$hostid,1);
	$cmd->texto="UPDATE ordenadores SET arranque=@arranque WHERE idordenador=@idordenador";
	$cmd->Ejecutar();

	// Obtener información de la base de datos.
	$cmd->texto="SELECT ordenadores.nombreordenador AS hostname, ordenadores.ip AS ip,
			    ordenadores.mac AS mac, ordenadores.netiface AS netiface,
			    ordenadores.oglivedir AS oglivedir,
			    aulas.netmask AS netmask, aulas.router AS router,
			    aulas.ntp AS ntp, aulas.dns AS dns, aulas.proxy AS proxy,
			    aulas.nombreaula AS grupo, repositorios.ip AS iprepo,
			    (SELECT ipserveradm FROM entornos LIMIT 1) AS ipserveradm,
			    menus.resolucion AS vga, perfileshard.winboot AS winboot,
			    centros.directorio, entidades.ogunit
			FROM ordenadores 
			JOIN aulas USING (idaula)
			JOIN centros USING (idcentro)
			JOIN entidades USING (identidad)
			JOIN repositorios USING (idrepositorio)
			LEFT JOIN menus USING (idmenu)
			LEFT JOIN perfileshard USING (idperfilhard)
			WHERE ordenadores.idordenador='$hostid'";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir())  return;
	$rs->Primero(); 
	$hostname=$rs->campos["hostname"];
	$ip=$rs->campos["ip"];
	$mac=$rs->campos["mac"];
	$netiface=$rs->campos["netiface"];
	$netmask=$rs->campos["netmask"];
	$router=$rs->campos["router"];
	$ntp=$rs->campos["ntp"];
	$dns=$rs->campos["dns"];
	$proxy=$rs->campos["proxy"];
	$group=cleanString($rs->campos["grupo"]);
	$repo=$rs->campos["iprepo"];
	$server=$rs->campos["ipserveradm"];
	$vga=$rs->campos["vga"];
	$winboot=$rs->campos["winboot"];
	$oglivedir=$rs->campos["oglivedir"];
	$ogunit=$rs->campos["ogunit"];
	if ($ogunit == 0 or $rs->campos["directorio"] == null) {
		$directorio="" ;
	} else {
		$directorio=$rs->campos["directorio"];
	}

	$rs->Cerrar();

	// Componer código de idioma para el parámetro de arranque.
	switch ($lang) {
		case "eng":
			$lang="en_GB";
			break;
		case "esp":
			$lang="es_ES";
			break;
		case "cat":
			$lang="ca_ES";
			break;
	}

	// Componer parámetros del kernel.
	$infohost=" LANG=$lang".
		  " ip=$ip:$server:$router:$netmask:$hostname:$netiface:none" .
		  " group=$group" .
		  " ogrepo=$repo" .
		  " oglive=$server" .
		  " oglog=$server" .
		  " ogshare=$server" .
		  " oglivedir=$oglivedir";
	// Añadir parámetros opcionales.
	if (! empty ($ntp))	{ $infohost.=" ogntp=$ntp"; }
	if (! empty ($dns))	{ $infohost.=" ogdns=$dns"; }
	if (! empty ($proxy))	{ $infohost.=" ogproxy=$proxy"; }
	if (! empty ($winboot))	{ $infohost.=" winboot=$winboot"; }
	// Comprobar si se usa el parámetro "vga" (número de 3 cifras) o "video" (cadena).
	if (! empty ($vga)) {
		// UHU - Se sustituye la función is_int por is_numeric, ya que al ser un string no funciona bien con is_int
		if (is_numeric($vga) && strlen($vga) == 3) {
			$infohost.=" vga=$vga";
		} else {
			$infohost.=" video=$vga";
		}
	}
	if (! empty ($directorio)) { $infohost.=" ogunit=$directorio"; }
	
	// Obtener nombre de fichero PXE a partir de la MAC del ordenador cliente.
	$pxedir="/opt/opengnsys/tftpboot/menu.lst";
	$mac = substr($mac,0,2) . ":" . substr($mac,2,2) . ":" . substr($mac,4,2) . ":" . substr($mac,6,2) . ":" . substr($mac,8,2) . ":" . substr($mac,10,2);
	$macfile="$pxedir/01-" . str_replace(":","-",strtoupper($mac));	

	// Crear fichero de arranque a partir de la plantilla y los datos del cliente.
	// UHU - si el parametro vga no existe, no se quita.
	if (! empty ($vga)) {
		exec ("sed -e 's|vga=...||g; s|INFOHOST|$infohost|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g' $pxedir/templates/$bootopt > $macfile");
	}
	else{
		exec ("sed -e 's|INFOHOST|$infohost|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g' $pxedir/templates/$bootopt > $macfile");
	}
	exec ("chmod 777 $macfile");
}


/**
 *           deleteBootFile ($mac)
 * @brief    Borra el fichero PXE del ordenador con la dirección MAC correspondiente.
 * @param    {String}  mac     Dirección MAC del ordenador (sin caracteres ":").
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 */
function deleteBootFile ($mac) {	

	// Obtener nombre de fichero a partir de dirección MAC.
	$pxedir="/opt/opengnsys/tftpboot/menu.lst";
	$macfile = "$pxedir/01-" . substr($mac,0,2) . "-" . substr($mac,2,2) . "-" . substr($mac,4,2) . "-" . substr($mac,6,2) . "-" . substr($mac,8,2) . "-" . substr($mac,10,2);
	// Eliminar el fichero.
	exec ("rm -f $macfile");
}

/**
 *           updateBootMode ($cmd, $idfield, $idvalue, $lang)
 * @brief    Ejecuta la función para componer fichero PXE para todos los clientes que cumplan
 *           con un determinado criterio de búsqueda basado en clave ejena.
 * @param    {Object}  cmd       Objeto de conexión con la base de datos.
 * @param    {String}  idfield   Campo identificador de la clave ajena para buscar ordenadores.
 * @param    {Number}  idvalue   Valor a buscar en el ídentificador de la clave ajena.
 * @param    {String}  lang      Idioma de arranque.
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 */
function updateBootMode ($cmd, $idfield, $idvalue, $lang) {

	// Salir si los es nulo el campo de identificador y su valor de índice.
	if (empty ($idfield) or empty ($idvalue))
		return;
	// Control para evitar ataques XSS.
	$idfield = mysql_real_escape_string ($idfield);
	$idvalue = mysql_real_escape_string ($idvalue);

	// Obtener los ordenadores asociados al aula y sus plantillas de arranque.
	$cmd->texto = "SELECT idordenador AS hostid, arranque AS bootopt
			 FROM ordenadores
			WHERE $idfield=$idvalue";
	$rs = new Recordset;
	$rs->Comando=&$cmd;
	if ($rs->Abrir()) {
		$rs->Primero();
		while (! $rs->EOF) {
			$hostid=$rs->campos["hostid"];
			if (! empty ($hostid)) {
				$bootopt=$rs->campos["bootopt"];
				// Volver a crear el fichero de arranque.
				createBootMode ($cmd, $bootopt, $hostid, $lang);
			}
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
}

?>

