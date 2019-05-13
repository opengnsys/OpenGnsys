<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_dministradores_centros.php
// Descripción :
//		Gestiona la asignación de administradores a las Unidades organizativas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idcentro=0; 
$idusuario=0; 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["idcentro"])) $idcentro=$_POST["idcentro"];
if (isset($_POST["idusuario"])) $idusuario=$_POST["idusuario"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_administradores_centros";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_administradores_centros";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idusuario.");".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idusuario.");".chr(13);
}
else{
	echo  $literal."(0,'".$cmd->DescripUltimoError()."',".$idusuario.")";
}
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idusuario;
	global	$idcentro;
	global  $urlimgth;
	global	$op_alta;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idusuario",$idusuario,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO administradores_centros(idusuario,idcentro) VALUES (@idusuario,@idcentro)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM administradores_centros WHERE idusuario='.$idusuario.' AND idcentro='.$idcentro;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}

