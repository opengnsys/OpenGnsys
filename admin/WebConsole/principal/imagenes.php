<?php
// *******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2012
// Nombre del fichero: imagenes.php
// Descripción : 
//		Administra imágenes de un determinado Centro
// ********************************************************************************************************
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
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el código XML del arbol 
	
// Genera vista del árbol usando como origen de datos el XML anterior
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
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/imagenes_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?php
//________________________________________________________________________________________________________

echo $arbol->CreaArbolVistaXML(); // Muestra árbol en pantalla

// Crea contextual de las imágenes
$flotante=new MenuContextual(); 
 
$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLTiposImagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreaContextualXMLGruposImagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESMONOLITICAS,
					$LITAMBITO_IMAGENESMONOLITICAS,
					$IMAGENES_MONOLITICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESBASICAS,
					$LITAMBITO_IMAGENESBASICAS,
					$IMAGENES_BASICAS);
echo $flotante->CreaMenuContextual($XMLcontextual);										

$XMLcontextual=CreacontextualXMLImagen($AMBITO_IMAGENESINCREMENTALES,
					$LITAMBITO_IMAGENESINCREMENTALES,
					$IMAGENES_INCREMENTALES);
echo $flotante->CreaMenuContextual($XMLcontextual);											
?>
</BODY>
</HTML>
<?php
// ********************************************************************************************************
//	Devuelve una cadena con formato XML con toda la información de las imáges registradas en un Centro 
//	concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________

function CreaArbol($cmd,$idcentro)
{
	// Variables globales.
	global $TbMsg;

	global $LITAMBITO_IMAGENES;
	global $AMBITO_GRUPOSIMAGENESMONOLITICAS,
			$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
			$AMBITO_IMAGENESMONOLITICAS,
			$LITAMBITO_IMAGENESMONOLITICAS,
			$IMAGENES_MONOLITICAS;
			
	global $AMBITO_GRUPOSIMAGENESBASICAS,
			$LITAMBITO_GRUPOSIMAGENESBASICAS,
			$AMBITO_IMAGENESBASICAS,
			$LITAMBITO_IMAGENESBASICAS,
			$IMAGENES_BASICAS;
			
	global $AMBITO_GRUPOSIMAGENESINCREMENTALES,
			$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
			$AMBITO_IMAGENESINCREMENTALES,
			$LITAMBITO_IMAGENESINCREMENTALES,
			$IMAGENES_INCREMENTALES;
			
	$cadenaXML='<RAIZ';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_IMAGENES;
	$cadenaXML.=' infonodo="'.$TbMsg[9].'"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESMONOLITICAS,
						$LITAMBITO_GRUPOSIMAGENESMONOLITICAS,
						$AMBITO_IMAGENESMONOLITICAS,
						$LITAMBITO_IMAGENESMONOLITICAS,
						$IMAGENES_MONOLITICAS,
						$TbMsg[11]);

	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESBASICAS,
						$LITAMBITO_GRUPOSIMAGENESBASICAS,
						$AMBITO_IMAGENESBASICAS,
						$LITAMBITO_IMAGENESBASICAS,
						$IMAGENES_BASICAS,
						$TbMsg[12]);

	$cadenaXML.=SubarbolXML_tiposimagenes($AMBITO_GRUPOSIMAGENESINCREMENTALES,
						$LITAMBITO_GRUPOSIMAGENESINCREMENTALES,
						$AMBITO_IMAGENESINCREMENTALES,
						$LITAMBITO_IMAGENESINCREMENTALES,
						$IMAGENES_INCREMENTALES,
						$TbMsg[13]);											
	$cadenaXML.='</RAIZ>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_tiposimagenes($ambg,$litambg,$amb,$litamb,$tipo,$msg)
{
	$cadenaXML="";
	$cadenaXML.='<TIPOSIMAGENES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' nodoid=SubRaiz-0';
	$cadenaXML.=' infonodo='.$msg;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'TipoImagen_".$tipo."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_gruposimagenes(0,$ambg,$litambg,$amb,$litamb,$tipo);
	$cadenaXML.='</TIPOSIMAGENES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_gruposimagenes($grupoid,$ambg,$litambg,$amb,$litamb,$tipo)
{
	global $cmd;
	global $idcentro;
	
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid 
					FROM grupos WHERE grupoid=".$grupoid." 
					AND idcentro=".$idcentro." 
					AND tipo=".$ambg." 
					ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	//echo $cmd->texto;
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSIMAGENES';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$litambg."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid="'.$litambg."-".$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_gruposimagenes($rs->campos["idgrupo"],$ambg,$litambg,$amb,$litamb,$tipo);
		$cadenaXML.='</GRUPOSIMAGENES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Imagenes($grupoid,$amb,$litamb,$tipo);
	return($cadenaXML);
}
//________________________________________________________________________________________________________

function SubarbolXML_Imagenes($grupoid,$amb,$litamb,$tipo)
{
	global $cmd;
	global $idcentro;	
	
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idimagen,descripcion
				FROM imagenes 
				WHERE idcentro=".$idcentro." 
				AND grupoid=".$grupoid." 
				AND tipo=".$tipo." 
				ORDER BY descripcion";
	//echo "<br>".$cmd->texto;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$litamb.'-'.$rs->campos["idimagen"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$litamb."'" .')"';
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

function CreaContextualXMLTiposImagenes($ambg,$litambg,$amb,$litamb,$tipo)
{
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="TipoImagen_'.$tipo.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$ambg.',' ."'".$litambg."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_imagen(\''.$litamb.'\','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________

function CreaContextualXMLGruposImagenes($ambg,$litambg,$amb,$litamb,$tipo)
{
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$litambg.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$ambg.',' ."'".$litambg."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_imagen(\''.$litamb.'\','.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/imagen.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_imagenes.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$tipo.')"';
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
//__________________________________________________________________________________________

function CreacontextualXMLImagen($amb,$litamb,$tipo)
{
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$litamb.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_informacion()"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$tipo.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_imagen('.$tipo.')"';	
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_imagen('.$tipo.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>

