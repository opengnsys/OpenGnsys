<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_imagenincremental.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de imagenes_softincremental
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idimagen=0; 
$idsoftincremental=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"];
if (isset($_GET["idsoftincremental"])) $idsoftincremental=$_GET["idsoftincremental"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
?>
<HTML>
<HEAD>
<BODY>
<?
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_imagenincremental";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_imagenincremental";
		break;
	default:
		break;
}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftincremental.");".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftincremental.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idsoftincremental.")";
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
	global	$idimagen;
	global	$idsoftincremental;
	global	$op_alta;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idimagen",$idimagen,1);
	$cmd->CreaParametro("@idsoftincremental",$idsoftincremental,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO imagenes_softincremental (idimagen,idsoftincremental) VALUES (@idimagen,@idsoftincremental)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM imagenes_softincremental WHERE idimagen='.$idimagen.' AND idsoftincremental='.$idsoftincremental;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>