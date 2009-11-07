<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: imagenes.php
// Descripción : 
//		Administra imágenes de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/imagenes_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos las acciones registradas en el Centro
// Creación del árbol
$baseurlimg="../images/signos"; // Url de las imágenes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,1,0,5);
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/imagenes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/imagenes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	// Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			// Crea objeto MenuContextual

// Crea contextual de las imágenes
 $XMLcontextual=CreacontextualXMLImagenes();
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=ContextualXMLGruposImagenes(); // Grupos de imágenes
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=CreacontextualXMLImagen(); // Imágenes
 echo $flotante->CreaMenuContextual($XMLcontextual);
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML con toda la información de las imáges registradas en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	global $LITAMBITO_IMAGENES;
	$cadenaXML='<RAIZ';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_IMAGENES;
	$cadenaXML.=' infonodo="'.$TbMsg[9].'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_IMAGENES."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_imagenes($cmd,$idcentro,0);
	$cadenaXML.='</RAIZ>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_imagenes($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSIMAGENES;
	global $AMBITO_GRUPOSIMAGENES;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSIMAGENES." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSIMAGENES';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSIMAGENES."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSIMAGENES.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_imagenes($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSIMAGENES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Imagenes($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Imagenes($cmd,$idcentro,$grupoid){
	global $LITAMBITO_IMAGENES;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idimagen,descripcion  FROM imagenes WHERE idcentro=".$idcentro." AND grupoid=".$grupoid." ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_IMAGENES.'-'.$rs->campos["idimagen"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_IMAGENES."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</IMAGEN>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Menús Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLImagenes(){
	global $AMBITO_IMAGENES;
	global $AMBITO_GRUPOSIMAGENES;
	global $LITAMBITO_GRUPOSIMAGENES;
	global $LITAMBITO_IMAGENES;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_IMAGENES.'"';
	$layerXML.=' maxanchu=170';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSIMAGENES.',' ."'".$LITAMBITO_GRUPOSIMAGENES."'".')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=300;
	$wpages="../propiedades/propiedades_imagenes.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_IMAGENES.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//__________________________________________________________________________________________
function ContextualXMLGruposImagenes(){
	global $AMBITO_IMAGENES;
	global $AMBITO_GRUPOSIMAGENES;
	global $LITAMBITO_GRUPOSIMAGENES;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSIMAGENES.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSIMAGENES.',' ."'".$LITAMBITO_GRUPOSIMAGENES."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=290;
	$wpages="../propiedades/propiedades_imagenes.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_IMAGENES.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//---------------------------------------------------------------------------------------------------------------------------
function CreacontextualXMLImagen(){
	global $AMBITO_IMAGENES;
	global $LITAMBITO_IMAGENES;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_IMAGENES.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_imagenincremental()"';
	$layerXML.=' imgitem="../images/iconos/incremental.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_informacion()"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_IMAGENES.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=290;
	$wpages="../propiedades/propiedades_imagenes.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
