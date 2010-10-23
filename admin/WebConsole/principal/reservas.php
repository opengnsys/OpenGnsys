<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: reservas.php
// Descripción : 
//		Administra reservas de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/reservas_".$idioma.".php");
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
	<SCRIPT language="javascript" src="../jscripts/reservas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/reservas_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	// Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			// Crea objeto MenuContextual

// Crea contextual de las imágenes
 $XMLcontextual=CreacontextualXMLReservas();
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=ContextualXMLGruposReservas(); // Grupos de reservas
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=CreacontextualXMLReserva(); // Reervas
 echo $flotante->CreaMenuContextual($XMLcontextual);
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML con toda la información de las reservas registradas en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	global $LITAMBITO_RESERVAS;
	$cadenaXML='<RAIZ';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/reservas.gif"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_RESERVAS;
	$cadenaXML.=' infonodo="'.$TbMsg[9].'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_RESERVAS."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_reservas($cmd,$idcentro,0);
	$cadenaXML.='</RAIZ>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_reservas($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSRESERVAS;
	global $AMBITO_GRUPOSRESERVAS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSRESERVAS." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSRESERVAS';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSRESERVAS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSRESERVAS.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_reservas($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSRESERVAS>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Reservas($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Reservas($cmd,$idcentro,$grupoid){
	global $LITAMBITO_RESERVAS;
	global $RESERVA_PENDIENTE;
	global $RESERVA_CONFIRMADA;
	global $RESERVA_DENEGADA;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idreserva,descripcion,estado FROM reservas WHERE idcentro=".$idcentro." AND grupoid=".$grupoid." ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<RESERVA';
		// Atributos
		switch($rs->campos["estado"]){
			case $RESERVA_PENDIENTE :
				$urlimg='../images/iconos/pendientes.gif';
				break;
			case $RESERVA_CONFIRMADA :
				$urlimg='../images/iconos/confirmadas.gif';
				break;
			case $RESERVA_DENEGADA :
				$urlimg='../images/iconos/denegadas.gif';
				break;
			default:
							$urlimg="";
		}

		$cadenaXML.=' imagenodo="'.$urlimg.'"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_RESERVAS.'-'.$rs->campos["idreserva"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_RESERVAS."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</RESERVA>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Menús Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLReservas(){
	global $AMBITO_RESERVAS;
	global $AMBITO_GRUPOSRESERVAS;
	global $LITAMBITO_GRUPOSRESERVAS;
	global $LITAMBITO_RESERVAS;
	global $RESERVA_CONFIRMADA;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_RESERVAS.'"';
	$layerXML.=' maxanchu=165';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSRESERVAS.',' ."'".$LITAMBITO_GRUPOSRESERVAS."'".')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=500;
	$wpages="../propiedades/propiedades_reservas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_reservas.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_RESERVAS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//__________________________________________________________________________________________
function ContextualXMLGruposReservas(){
	global $AMBITO_RESERVAS;
	global $AMBITO_GRUPOSRESERVAS;
	global $LITAMBITO_GRUPOSRESERVAS;
	global $RESERVA_CONFIRMADA;

	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSRESERVAS.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSRESERVAS.',' ."'".$LITAMBITO_GRUPOSRESERVAS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=500;
	$wpages="../propiedades/propiedades_reservas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_reservas.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_RESERVAS.')"';
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

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//---------------------------------------------------------------------------------------------------------------------------
function CreacontextualXMLReserva(){
	global $EJECUCION_RESERVA;
	global $AMBITO_RESERVAS;
	global $LITAMBITO_RESERVAS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_RESERVAS.'"';
	$layerXML.=' maxanchu=120';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="programacion('.$EJECUCION_RESERVA.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_RESERVAS.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=500;
	$wpages="../propiedades/propiedades_reservas.php";
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
