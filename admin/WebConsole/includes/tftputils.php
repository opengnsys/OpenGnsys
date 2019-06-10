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

// Directorio de ficheros PXE.
define("PXEDIRBIOS", "/opt/opengnsys/tftpboot/menu.lst");
define("PXEDIRUEFI", "/opt/opengnsys/tftpboot/grub");


/**
 * @brief    Sustituye espacio por "_" y quita acentos y tildes.
 * @param    string   Cadena a modificar.
 * @return   string   Cadena modificada.
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @author   
 * @date     
*/
function cleanString ($cadena) {
	return strtr(trim($cadena), " áéíóúñçÁÉÍÓÚÑÇ", "_aeiouncAEIOUNC");
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
 *           createBootMode ($cmd, $bootopt, $hostname, $lang)
 * @brief    Crea un fichero PXE para el ordenador basado en la plantilla indicada y usando
 *           los datos almacenados en la BD.
 * @param    Object   cmd       Objeto de conexión a la base de datos.
 * @param    String   bootopt   Plantilla de arranque PXE.
 * @param    String  hostname  Nombre del ordenador.
 * @param    String   lang      Idioma de arranque.
 * @version  1.0.5 - Primera versión, adaptada de NetBoot Avanzado (Antonio J. Doblas Viso - Universidad de Málaga)
 * @author  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 * @version  1.1.0 - Se incluye la unidad organizativa como parametro del kernel: ogunit=directorio_unidad (ticket #678).
 * @author   Irina Gómez - ETSII Universidad de Sevilla
 * @date     2015-12-16
 * @version  1.1.0 - La segunda fase de carga del ogLive se define en el SERVER para evitar erores de sincronismo entre versiones (ticket #787).
 * @author   Antonio J. Doblas Viso - Universidad de Malaga
 * @date     2017-06-01
 * @version  1.1.0 - Se incluye el nombre del perfil hardware y se elimina el winboot (ticket #828).
 * @author   Antonio J. Doblas Viso - Universidad de Malaga
 * @date     2018-01-21 
 * @version  1.1.1 - Se utiliza setclientmode. Gestiona plantilla bios y uefi (ticket #802 #888)
 * @author   Irina Gómez - ETSII Universidad de Sevilla
 * @date     2019-03-14
 */
function createBootMode ($cmd, $bootopt, $hostname, $lang) {
	global $cadenaconexion;

	// Datos para el acceso a mysql
	$strcn=explode(";",$cadenaconexion);
	$acceso="USUARIO=".$strcn[1]." PASSWORD=".$strcn[2]." CATALOG=".$strcn[3];

	// Plantilla con las opciones por defecto.
	if (empty ($bootopt))  $bootopt = "00unknown";

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

	// Descripción plantilla PXE
	$description=exec("awk 'NR==1 {print $2}' ".PXEDIRBIOS."/templates/".$bootopt);
	if ($description === "") $description=exec("awk 'NR==1 {print $2}' ".PXEDIRUEFI."/templates/".$bootopt);
	// Llamamos al script setclientmode
	shell_exec("export LANG=$lang $acceso; /opt/opengnsys/bin/setclientmode $description $hostname PERM $file");
}


/**
 *           deleteBootFile ($mac)
 * @brief    Borra el fichero PXE del ordenador con la dirección MAC correspondiente.
 * @param    String  mac     Dirección MAC del ordenador (sin caracteres ":").
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 */
function deleteBootFile ($mac) {	

	// Obtener nombre de fichero a partir de dirección MAC.
	$mac = strtoupper($mac);
	$macfile = "/01-" . substr($mac, 0, 2) . "-" . substr($mac, 2, 2) . "-" . substr($mac, 4, 2) . "-" . substr($mac, 6, 2) . "-" . substr($mac, 8, 2) . "-" . substr($mac, 10, 2);
	// Eliminar el fichero.
	@unlink(PXEDIRBIOS.$macfile);
	@unlink(PXEDIRUEFI.$macfile);
}

/**
 *           updateBootMode ($cmd, $idfield, $idvalue, $lang)
 * @brief    Ejecuta la función para componer fichero PXE para todos los clientes que cumplan
 *           con un determinado criterio de búsqueda basado en clave ejena.
 * @param    Object   cmd       Objeto de conexión con la base de datos.
 * @param    String   idfield   Campo identificador de la clave ajena para buscar ordenadores.
 * @param    Integer  idvalue   Valor a buscar en el ídentificador de la clave ajena.
 * @param    String   lang      Idioma de arranque.
 * @versión  1.0.5 - Primera versión, adaptada de NetBoot Avanzado.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2013-04-25
 */
function updateBootMode ($cmd, $idfield, $idvalue, $lang) {

	// Salir si los es nulo el campo de identificador y su valor de índice.
	if (empty ($idfield) or empty ($idvalue))
		return;
	// Control para evitar ataques XSS.
	$idfield = mysqli_real_escape_string ($cmd->Conexion->controlador, $idfield);
	$idvalue = mysqli_real_escape_string ($cmd->Conexion->controlador, $idvalue);

	// Obtener los ordenadores asociados al aula y sus plantillas de arranque.
	$cmd->texto = "SELECT nombreordenador AS hostname, arranque AS bootopt
			 FROM ordenadores
			WHERE $idfield=$idvalue";
	$rs = new Recordset;
	$rs->Comando=&$cmd;
	if ($rs->Abrir()) {
		$rs->Primero();
		while (! $rs->EOF) {
			$hostname=$rs->campos["hostname"];
			if (! empty ($hostname)) {
				$bootopt=$rs->campos["bootopt"];

				// Volver a crear el fichero de arranque.
				createBootMode ($cmd, $bootopt, $hostname, $lang);
			}
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
}

/**
 *           updateBootRepo ($cmd, $repoid)
 * @brief    Actualiza la IP del repositorio en los ficheros PXE de todos sus equipos asociados.
 * @param    Object  cmd      Objeto de conexión con la base de datos
 * @param    Integer repoid   Campo identificador del repositorio
 * @return   Integer          0, sin errores; -1, error acceso a BD; >0, ficheros no modificados
 * @versión  1.1.0 - Primera versión.
 * @authors  Ramón Gómez - ETSII Universidad de Sevilla
 * @date     2018-01-19
 */
function updateBootRepo ($cmd, $repoid) {
	$errors = 0;
	// Obtener todas las MAC de los ordenadores incluidos en el repositorio.
	$cmd->texto = "SELECT UPPER(ordenadores.mac) AS mac, repositorios.ip AS iprepo
			 FROM ordenadores
			 JOIN repositorios USING (idrepositorio)
			WHERE ordenadores.idrepositorio = '$repoid'";
	$rs = new Recordset;
	$rs->Comando=&$cmd;
	if ($rs->Abrir()) {
		$rs->Primero();
		while (! $rs->EOF) {
			$mac = $rs->campos["mac"];
			$repo = $rs->campos["iprepo"];
			// Obtener nombre de fichero PXE a partir de la MAC del ordenador cliente.
			$macfile = "/01-" . substr($mac, 0, 2) . "-" . substr($mac, 2, 2) . "-" . substr($mac, 4, 2) . "-" . substr($mac, 6, 2) . "-" . substr($mac, 8, 2) . "-" . substr($mac, 10, 2);
			// Actualizar parámetro "ogrepo" en el fichero PXE.
			foreach (array (PXEDIRBIOS,PXEDIRUEFI) as $bootdir) {
			    if ($pxecode = @file_get_contents($bootdir.$macfile)) {
				$pxecode = preg_replace("/ogrepo=[^ ]*/", "ogrepo=$repo", $pxecode);
				if (! @file_put_contents($bootdir.$macfile, $pxecode)) {
					$errors++;
				}
			    }
			}
			$rs->Siguiente();
		}
		$rs->Cerrar();
	} else {
		$errors = -1;
	}
	return($errors);
}
