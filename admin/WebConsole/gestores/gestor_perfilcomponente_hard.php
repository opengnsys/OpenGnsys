<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idperfilhard"])) $idperfilhard=$_POST["idperfilhard"];
if (isset($_POST["idhardware"])) $idhardware=$_POST["idhardware"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
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
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idhardware.");".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idhardware.");".chr(13);
}
else{
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idhardware.")";
}
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
