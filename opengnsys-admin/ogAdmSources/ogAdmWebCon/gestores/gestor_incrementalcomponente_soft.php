<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_incrementalcomponente_soft.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de softincremental_softwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idsoftincremental=0; 
$idsoftware=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idsoftincremental"])) $idsoftincremental=$_GET["idsoftincremental"];
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
		$literal="resultado_insertar_incrementalcomponente_soft";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_incrementalcomponente_soft";
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
	global	$idsoftincremental;
	global	$idsoftware;
	global   $urlimgth;
	global	$op_alta;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idsoftincremental",$idsoftincremental,1);
	$cmd->CreaParametro("@idsoftware",$idsoftware,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO softincremental_softwares (idsoftincremental,idsoftware) VALUES (@idsoftincremental,@idsoftware)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM softincremental_softwares WHERE idsoftincremental='.$idsoftincremental.' AND idsoftware='.$idsoftware;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>