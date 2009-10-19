<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_procedimientoscomandos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de procedimientos_comandos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idprocedimientocomando=0; 
$orden=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idprocedimientocomando"])) $idprocedimientocomando=$_GET["idprocedimientocomando"];
if (isset($_GET["orden"])) $orden=$_GET["orden"];

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
	case $op_eliminacion :
		$literal="resultado_eliminar_procedimientocomando";
		break;
	case $op_modificacion :
		$literal="resultado_modificar_procedimientocomando";
		break;
	default:
		break;
}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idprocedimientocomando.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idprocedimientocomando.")";
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
	global	$op_modificacion;
	global	$op_eliminacion;
	global $idprocedimientocomando;
	global$orden;

	$cmd->CreaParametro("@orden",$orden,1);
	switch($opcion){
		case $op_modificacion :
			$cmd->texto='UPDATE procedimientos_comandos set orden=@orden WHERE idprocedimientocomando='.$idprocedimientocomando;
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto='DELETE  FROM procedimientos_comandos WHERE idprocedimientocomando='.$idprocedimientocomando;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>