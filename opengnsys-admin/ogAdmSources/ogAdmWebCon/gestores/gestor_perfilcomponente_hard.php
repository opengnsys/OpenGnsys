<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_perfilcomponente_hard.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de perfileshard_hardwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idperfilhard=0; 
$idhardware=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"];
if (isset($_GET["idhardware"])) $idhardware=$_GET["idhardware"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<BODY>
<?
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_perfilcomponente_hard";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_perfilcomponente_hard";
		break;
	default:
		break;
}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idhardware.");".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idhardware.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idhardware.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idperfilhard;
	global	$idhardware;
	global   $urlimgth;
	global	$op_alta;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idperfilhard",$idperfilhard,1);
	$cmd->CreaParametro("@idhardware",$idhardware,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO perfileshard_hardwares (idperfilhard,idhardware) VALUES (@idperfilhard,@idhardware)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM perfileshard_hardwares WHERE idperfilhard='.$idperfilhard.' AND idhardware='.$idhardware;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>