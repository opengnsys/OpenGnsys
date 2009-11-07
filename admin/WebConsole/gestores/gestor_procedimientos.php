<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_procedimientos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de procedimientos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/procedimientos_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idprocedimiento=0; 
$descripcion="";
$grupoid=0; 
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idprocedimiento"])) $idprocedimiento=$_GET["idprocedimiento"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["identificador"])) $idprocedimiento=$_GET["identificador"];

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
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_procedimientos";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_procedimientos";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_procedimientos";
			break;
		case $op_movida :
			$literal="resultado_mover";
			break;
		default:
			break;
	}
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idprocedimiento.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idprocedimiento.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla procedimientos
________________________________________________________________________________________________________*/
function Gestiona(){
	global $EJECUCION_PROCEDIMIENTO;
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idprocedimiento;
	global	$descripcion;
	global	$grupoid;
	global	$comentarios;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idprocedimiento",$idprocedimiento,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO procedimientos (descripcion,comentarios,idcentro,grupoid) VALUES (@descripcion,@comentarios,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idprocedimiento=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_procedimientos($idprocedimiento,$descripcion);
				$baseurlimg="../images/signos"; // Url de las procedimientos de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE procedimientos SET descripcion=@descripcion, comentarios=@comentarios WHERE idprocedimiento=@idprocedimiento";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaProcedimientos($cmd,$idprocedimiento,"idprocedimiento");
			break;
		case $op_movida :
			$cmd->texto="UPDATE procedimientos SET  grupoid=@grupoid WHERE idprocedimiento=@idprocedimiento";
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_procedimientos($idprocedimiento,$descripcion){
	global $LITAMBITO_PROCEDIMIENTOS;
	$cadenaXML='<PROCEDIMIENTO';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/procedimiento.gif"';
	$cadenaXML.=' infonodo="'.$descripcion.'"';
	$cadenaXML.=' nodoid='.$LITAMBITO_PROCEDIMIENTOS.'-'.$idprocedimiento;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PROCEDIMIENTOS."'" .')"';
	$cadenaXML.='>';
	$cadenaXML.='</PROCEDIMIENTO>';
	return($cadenaXML);
}
?>