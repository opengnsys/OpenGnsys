<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["iduniversidad"])) $iduniversidad=$_POST["iduniversidad"];
if (isset($_POST["nombreuniversidad"])) $nombreuniversidad=$_POST["nombreuniversidad"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["identificador"])) $iduniversidad=$_POST["identificador"];

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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
	<SCRIPT language="javascript" src="../jscripts/propiedades_universidades.js"></SCRIPT>
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
	echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreuniversidad."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$iduniversidad.")";
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
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>
