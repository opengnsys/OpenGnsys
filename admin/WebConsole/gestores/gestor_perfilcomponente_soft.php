<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_perfilcomponente_soft.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de perfilessoft_softwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idperfilsoft=0; 
$idsoftware=0; 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idperfilsoft"])) $idperfilsoft=$_POST["idperfilsoft"];
if (isset($_POST["idsoftware"])) $idsoftware=$_POST["idsoftware"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_perfilcomponente_soft";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_perfilcomponente_soft";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftware.");".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftware.");".chr(13);
}
else{
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idsoftware.")";
}
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idperfilsoft;
	global	$idsoftware;
	global   $urlimgth;
	global	$op_alta;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idperfilsoft",$idperfilsoft,1);
	$cmd->CreaParametro("@idsoftware",$idsoftware,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO perfilessoft_softwares (idperfilsoft,idsoftware) VALUES (@idperfilsoft,@idsoftware)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM perfilessoft_softwares WHERE idperfilsoft='.$idperfilsoft.' AND idsoftware='.$idsoftware;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>
