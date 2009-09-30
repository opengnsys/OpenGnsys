<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_universidades.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de iduniversidades
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$iduniversidad=0; 
$nombreuniversidad="";
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["iduniversidad"])) $iduniversidad=$_GET["iduniversidad"];
if (isset($_GET["nombreuniversidad"])) $nombreuniversidad=$_GET["nombreuniversidad"]; 
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["identificador"])) $iduniversidad=$_GET["identificador"];

$iduniversidad=1;

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
// *************************************************************************************************************************************************
?>
<HTML>
<HEAD>
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_modificacion:
			$literal="resultado_modificar_universidades";
			break;
		default:
			break;
	}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreuniversidad."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$iduniversidad.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla iduniversidades
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$iduniversidad;
	global	$nombreuniversidad;
	global	$comentarios;

	global	$op_modificacion;

	$cmd->CreaParametro("@iduniversidad",$iduniversidad,1);
	$cmd->CreaParametro("@nombreuniversidad",$nombreuniversidad,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_modificacion:
			$cmd->texto="UPDATE universidades SET nombreuniversidad=@nombreuniversidad,comentarios=@comentarios WHERE iduniversidad=@iduniversidad";
			echo $cmd->texto;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>