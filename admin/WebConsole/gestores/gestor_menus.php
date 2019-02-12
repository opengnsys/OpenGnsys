<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
include_once("../includes/tftputils.php");
include_once("./relaciones/menus_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idmenu=0; 
$descripcion="";
$titulo="";
$modalidad=0;
$smodalidad=0;
$comentarios="";
$grupoid=0; 
$htmlmenupub="";
$htmlmenupri="";
$resolucion=0;
$idurlimg=0;

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idmenu"])) $idmenu=$_POST["idmenu"];
if (isset($_POST["identificador"])) $idmenu=$_POST["identificador"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["titulo"])) $titulo=$_POST["titulo"]; 

if (isset($_POST["modalidad"])) $modalidad=$_POST["modalidad"]; 
if (isset($_POST["smodalidad"])) $smodalidad=$_POST["smodalidad"]; 

if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["htmlmenupub"])) $htmlmenupub=$_POST["htmlmenupub"];
if (isset($_POST["htmlmenupri"])) $htmlmenupri=$_POST["htmlmenupri"];
if (isset($_POST["resolucion"])) $resolucion=$_POST["resolucion"];
if (isset($_POST["idicono"])) $idurlimg=$_POST["idicono"];

$tablanodo=""; // Arbol para nodos insertados

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
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_menus.js"></SCRIPT>';
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
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idmenu.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idmenu.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla menus
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idmenu;
	global	$descripcion;
	global	$titulo;
	global	$modalidad;
	global	$smodalidad;
	global	$comentarios;
	global	$grupoid;
	global	$htmlmenupub;
	global	$htmlmenupri;
	global	$resolucion;
	global	$idurlimg;
	global	$idioma;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idmenu",$idmenu,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@titulo",$titulo,0);
	$cmd->CreaParametro("@modalidad",$modalidad,1);
	$cmd->CreaParametro("@smodalidad",$smodalidad,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@htmlmenupub",$htmlmenupub,0);
	$cmd->CreaParametro("@htmlmenupri",$htmlmenupri,0);
	$cmd->CreaParametro("@resolucion",$resolucion,0);
	$cmd->CreaParametro("@idurlimg",$idurlimg,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO menus (descripcion,titulo,modalidad,smodalidad,
						comentarios,idcentro,grupoid,htmlmenupub,htmlmenupri,resolucion,idurlimg) 
						VALUES (@descripcion,@titulo,@modalidad,@smodalidad,
						@comentarios,@idcentro,@grupoid,@htmlmenupub,@htmlmenupri,@resolucion,@idurlimg)";
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
			$cmd->texto="UPDATE menus SET descripcion=@descripcion,titulo=@titulo,modalidad=@modalidad,smodalidad=@smodalidad,
						comentarios=@comentarios,htmlmenupub=@htmlmenupub ,htmlmenupri=@htmlmenupri,resolucion=@resolucion,idurlimg=@idurlimg
					WHERE idmenu=@idmenu";
			$resul=$cmd->Ejecutar();
			// Actualizar ficheros PXE de todos los ordenadores afectados.
			updateBootMode ($cmd, "idmenu", $idmenu, $idioma);
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
	$cadenaXML='<MENU';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/menu.gif"';	
	$cadenaXML.=' infonodo="' .$descripcion.'"';
	$cadenaXML.=' nodoid='.$LITAMBITO_MENUS.'-'.$idmenu;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_MENUS."'" .')"';
	$cadenaXML.='>';
	$cadenaXML.='</MENU>';
	return($cadenaXML);
}

