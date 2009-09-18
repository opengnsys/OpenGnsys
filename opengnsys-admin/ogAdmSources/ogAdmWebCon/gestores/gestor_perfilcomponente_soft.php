<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
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

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idperfilsoft"])) $idperfilsoft=$_GET["idperfilsoft"];
if (isset($_GET["idsoftware"])) $idsoftware=$_GET["idsoftware"];

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
		$literal="resultado_insertar_perfilcomponente_soft";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_perfilcomponente_soft";
		break;
	default:
		break;
}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftware.");".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftware.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idsoftware.")";
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