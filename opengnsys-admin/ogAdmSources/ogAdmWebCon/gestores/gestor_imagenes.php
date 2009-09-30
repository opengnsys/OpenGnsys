<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_imagenes.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de imagenes
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/imagenes_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
 
$idimagen=0; 
$descripcion="";
$grupoid=0; 
$idperfilsoft=0;
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idperfilsoft"])) $idperfilsoft=$_GET["idperfilsoft"]; 
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["identificador"])) $idimagen=$_GET["identificador"];

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
		case $op_alta :
			$literal="resultado_insertar_imagenes";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_imagenes";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_imagenes";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idimagen.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idimagen.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla imagenes
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idimagen;
	global	$descripcion;
	global	$grupoid;
	global	$comentarios;
	global	$idperfilsoft;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idimagen",$idimagen,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idperfilsoft",$idperfilsoft,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO imagenes (descripcion,idperfilsoft,comentarios,idcentro,grupoid) VALUES (@descripcion,@idperfilsoft,@comentarios,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idimagen=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_imagenes($idimagen,$descripcion);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE imagenes SET descripcion=@descripcion,idperfilsoft=@idperfilsoft, comentarios=@comentarios WHERE idimagen=@idimagen";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaImagenes($cmd,$idimagen,"idimagen");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE imagenes SET  grupoid=@grupoid WHERE idimagen=@idimagen";
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
function SubarbolXML_imagenes($idimagen,$descripcion){
		global 	$LITAMBITO_IMAGENES;
		$cadenaXML='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_IMAGENES."'" .')"';
		$cadenaXML.=' nodoid='.$LITAMBITO_IMAGENES.'-'.$idimagen;
		$cadenaXML.='>';
		$cadenaXML.='</IMAGEN>';
		return($cadenaXML);
}
?>