<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: IniciarSesion.php
// Descripción : 
//		Implementación del comando "Iniciar Sesión"
// Version 0.1 - En ambito distinto a ordenador muestra los equipos agrupados en configuraciones iguales.
// 	Fecha: 2014-10-23
//	 Autora: Irina Gomez, ETSII Universidad de Sevilla
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../includes/pintaTablaConfiguraciones.php");
include_once("../idiomas/php/".$idioma."/comandos/iniciarsesion_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<TITLE>Administración web de aulas</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/IniciarSesion.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/arrays.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/iniciarsesion_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?php
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
	//
	include_once("./includes/FiltradoAmbito.php");
	//________________________________________________________________________________________________________
        if($ambito!=$AMBITO_ORDENADORES){
                $cadenaid="";
                $cadenaip="";
                $cadenamac="";
                RecopilaIpesMacs($cmd,$ambito,$idambito);

        ?>
	<P align=center>
	<SPAN align=center class=subcabeceras><?php echo $TbMsg[7] ?></SPAN>
	<br>
<form  align=center name="fdatos" method="POST"> 
	<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
	<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">
	<INPUT type="hidden" name="cadenaid" value="<?php echo $cadenaid?>">
</form>
	<?php } // fin if $ambito!=$AMBITO_ORDENADORES

		tablaConfiguracionesIniciarSesion($cmd,$idambito,$ambito); ?>
<?php
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
<?php
/**************************************************************************************************************************************************
	Recupera los datos de un ordenador
		Parámetros:
		- cmd: Una comando ya operativo (con conexiónabierta)  
		- ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function toma_propiedades($cmd,$idordenador){
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;

	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard FROM ordenadores WHERE idordenador='".$idordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/
function tabla_configuraciones($cmd,$idordenador){
	global $idcentro;

	$tablaHtml="";
	$cmd->texto="SELECT	ordenadores_particiones.numpar,
				ordenadores_particiones.codpar, ordenadores_particiones.tamano,
				ordenadores_particiones.idnombreso, nombresos.nombreso,
				imagenes.descripcion AS imagen,
				perfilessoft.descripcion AS perfilsoft,
				sistemasficheros.descripcion AS sistemafichero
			FROM ordenadores
			INNER JOIN ordenadores_particiones ON ordenadores_particiones.idordenador=ordenadores.idordenador
			LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
			LEFT OUTER JOIN imagenes ON imagenes.idimagen=ordenadores_particiones.idimagen
			LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft
			LEFT OUTER JOIN sistemasficheros ON sistemasficheros.idsistemafichero=ordenadores_particiones.idsistemafichero
			WHERE ordenadores.idordenador=".$idordenador."
			  AND nombresos.nombreso!='DATA'
			ORDER BY ordenadores_particiones.numpar";
<<<<<<< HEAD
	$rs=new Recordset;
=======

	$rs->Comando=&$cmd; 
	$rs=new Recordset; 
>>>>>>> #812: Usar fichero de configuración JSON en comandos Configurar e Iniciar Sesión.
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		if(!empty($rs->campos["idnombreso"]) and isClonable($rs->campos["codpar"])){
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD ><input type="radio" name="particion"  value='.$rs->campos["numpar"].'></TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["numpar"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</TD>'.chr(13);
			$tablaHtml.='</TR>'.chr(13);
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($tablaHtml);
}
<<<<<<< HEAD
=======

>>>>>>> #812: Usar fichero de configuración JSON en comandos Configurar e Iniciar Sesión.
