<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idprocedimiento"])) $idprocedimiento=$_POST["idprocedimiento"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["identificador"])) $idprocedimiento=$_POST["identificador"];

$tablanodo=""; // Arbol para nodos insertados
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
if($opcion!=$op_movida){
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo '<BODY>';
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_procedimientos.js"></SCRIPT>';
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($resul){
		echo 'var oHTML'.chr(13);
		echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
		echo 'o=cTBODY.item(1);'.chr(13);
	}
}
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
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idprocedimiento.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idprocedimiento.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
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
