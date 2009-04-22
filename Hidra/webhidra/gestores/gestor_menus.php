<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_menus.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de menus
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/menus_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idmenu=0; 
$descripcion="";
$titulo="";
$coorx=0;
$coory=0;
$modalidad=0;
$scoorx=0;
$scoory=0;
$smodalidad=0;
$comentarios="";
$grupoid=0; 
$htmlmenupub="";
$htmlmenupri="";
$resolucion=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"];
if (isset($_GET["identificador"])) $idmenu=$_GET["identificador"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["titulo"])) $titulo=$_GET["titulo"]; 
if (isset($_GET["coorx"])) $coorx=$_GET["coorx"]; 
if (isset($_GET["coory"])) $coory=$_GET["coory"]; 
if (isset($_GET["modalidad"])) $modalidad=$_GET["modalidad"]; 

if (isset($_GET["scoorx"])) $scoorx=$_GET["scoorx"]; 
if (isset($_GET["scoory"])) $scoory=$_GET["scoory"]; 
if (isset($_GET["smodalidad"])) $smodalidad=$_GET["smodalidad"]; 

if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["htmlmenupub"])) $htmlmenupub=$_GET["htmlmenupub"];
if (isset($_GET["htmlmenupri"])) $htmlmenupri=$_GET["htmlmenupri"];
if (isset($_GET["resolucion"])) $resolucion=$_GET["resolucion"];

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
			$literal="resultado_insertar_menus";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_menus";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_menus";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idmenu.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idmenu.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla menus
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idmenu;
	global	$descripcion;
	global   $titulo;
	global   $coorx;
	global   $coory;
	global   $modalidad;
	global   $scoorx;
	global   $scoory;
	global   $smodalidad;
	global	$comentarios;
	global	$grupoid;
	global	$htmlmenupub;
	global	$htmlmenupri;
	global	$resolucion;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idmenu",$idmenu,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@titulo",$titulo,0);
	$cmd->CreaParametro("@coorx",$coorx,1);
	$cmd->CreaParametro("@coory",$coory,1);
	$cmd->CreaParametro("@modalidad",$modalidad,1);
	$cmd->CreaParametro("@scoorx",$scoorx,1);
	$cmd->CreaParametro("@scoory",$scoory,1);
	$cmd->CreaParametro("@smodalidad",$smodalidad,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@htmlmenupub",$htmlmenupub,0);
	$cmd->CreaParametro("@htmlmenupri",$htmlmenupri,0);
	$cmd->CreaParametro("@resolucion",$resolucion,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO menus (descripcion,titulo,coorx,coory,modalidad,scoorx,scoory,smodalidad,comentarios,idcentro,grupoid,htmlmenupub,htmlmenupri,resolucion) VALUES (@descripcion,@titulo,@coorx,@coory,@modalidad,@scoorx,@scoory,@smodalidad,@comentarios,@idcentro,@grupoid,@htmlmenupub,@htmlmenupri,@resolucion)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idmenu=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_menus($idmenu,$descripcion);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE menus SET descripcion=@descripcion,titulo=@titulo,coorx=@coorx,coory=@coory,modalidad=@modalidad,scoorx=@scoorx,scoory=@scoory,smodalidad=@smodalidad,comentarios=@comentarios,htmlmenupub=@htmlmenupub ,htmlmenupri=@htmlmenupri,resolucion=@resolucion  WHERE idmenu=@idmenu";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaMenus($cmd,$idmenu,"idmenu");
			break;
		case $op_movida :
			$cmd->texto="UPDATE menus SET  grupoid=@grupoid WHERE idmenu=@idmenu";
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
function SubarbolXML_menus($idmenu,$descripcion){
	global $LITAMBITO_MENUS;
	$cadenaXML.='<MENU';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/menu.gif"';	
	$cadenaXML.=' infonodo="' .$descripcion.'"';
	$cadenaXML.=' nodoid='.$LITAMBITO_MENUS.'-'.$idmenu;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_MENUS."'" .')"';
	$cadenaXML.='>';
	$cadenaXML.='</MENU>';
	return($cadenaXML);
}
?>