<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: servidores.php
// Descripción : 
//		Administra los servidores dhcp y rembo de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/servidores_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos los datos del Centro
// Creación del árbol
$baseurlimg="../images/signos"; // Url de las imágenes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,2,0,5);
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/servidores.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/servidores_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	 // Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

// Crea contextual de servidores rembo
$XMLcontextual=CreacontextualXMLServidoresRembo(); 
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLGruposServidoresRembo(); // Grupos de servidores
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLServidorRembo(); // Servidor rembo
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de servidores dhcp
$XMLcontextual=CreacontextualXMLServidoresdhcp();
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLGruposServidoresdhcp(); // Grupos de servidores
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLServidorDhcp(); // Servidor dhcp
echo $flotante->CreaMenuContextual($XMLcontextual);
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY OnContextMenu="return false">
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML de toda la información de los servidores rembo y dhcp de un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	$cadenaXML='<SERVIDORES';
	// Atributos		
	$cadenaXML.=' imagenid=imgcentros';
	$cadenaXML.=' imagenodo="../images/iconos/servidores.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[14];
	$cadenaXML.=' nodoid="servidores"';
	$cadenaXML.=' classnodo=texto_arbol';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolServidoresRemboXML($cmd,$idcentro);
	//$cadenaXML.=SubarbolServidoresDhcpXML($cmd,$idcentro);
	$cadenaXML.='</SERVIDORES>'; 
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolServidoresRemboXML($cmd,$idcentro){
	global $TbMsg;
	global $LITAMBITO_SERVIDORESREMBO;
	$cadenaXML='<SERVIDORESREMBO';
	// Atributos		
	$cadenaXML.=' imagenodo="../images/iconos/servidoresrembo.gif"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_SERVIDORESREMBO."'" .')"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_SERVIDORESREMBO;
	$cadenaXML.=' infonodo='.$TbMsg[12];
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_servidoresrembo($cmd,$idcentro,0);
	$cadenaXML.='</SERVIDORESREMBO>';
	return($cadenaXML);
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_grupos_servidoresrembo($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSSERVIDORESREMBO;
	global $AMBITO_GRUPOSSERVIDORESREMBO;
	global $LITAMBITO_SERVIDORESREMBO;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSSERVIDORESREMBO." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSSERVIDORESREMBO ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSSERVIDORESREMBO."'" .');"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSSERVIDORESREMBO.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_servidoresrembo($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSSERVIDORESREMBO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cmd->texto="SELECT idservidorrembo,nombreservidorrembo FROM servidoresrembo WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." order by idservidorrembo desc" ;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<SERVIDORREMBO';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_SERVIDORESREMBO."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/servidorrembo.gif" ';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreservidorrembo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_SERVIDORESREMBO.'-'.$rs->campos["idservidorrembo"];
		$cadenaXML.='>';
		$cadenaXML.='</SERVIDORREMBO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolServidoresDhcpXML($cmd,$idcentro){
	global $TbMsg;
	global $LITAMBITO_SERVIDORESDHCP;
	$cadenaXML='<SERVIDORESDHCP';
	// Atributos		
	$cadenaXML.=' imagenodo="../images/iconos/servidoresdhcp.gif"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_SERVIDORESDHCP."'" .')"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_SERVIDORESDHCP;
	$cadenaXML.=' infonodo='.$TbMsg[13];
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_servidoresdhcp($cmd,$idcentro,0);
	$cadenaXML.='</SERVIDORESDHCP>';
	return($cadenaXML);
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_grupos_servidoresdhcp($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSSERVIDORESDHCP;
	global $AMBITO_GRUPOSSERVIDORESDHCP;
	global $LITAMBITO_SERVIDORESDHCP;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSSERVIDORESDHCP." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSSERVIDORESDHCP ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSSERVIDORESDHCP."'" .');"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSSERVIDORESDHCP.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_servidoresdhcp($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSSERVIDORESDHCP>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cmd->texto="SELECT idservidordhcp,nombreservidordhcp FROM servidoresdhcp WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." order by idservidordhcp desc" ;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<SERVIDORDHCP';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_SERVIDORESDHCP."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/servidordhcp.gif" ';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreservidordhcp"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_SERVIDORESDHCP.'-'.$rs->campos["idservidordhcp"];
		$cadenaXML.='>';
		$cadenaXML.='</SERVIDORDHCP>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Menús Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLServidoresRembo(){
	global $AMBITO_SERVIDORESREMBO;
	global $AMBITO_GRUPOSSERVIDORESREMBO;
	global $LITAMBITO_GRUPOSSERVIDORESREMBO;
	global $LITAMBITO_SERVIDORESREMBO;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_SERVIDORESREMBO.'"';
	$layerXML.=' maxanchu=185';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSERVIDORESREMBO.',' . "'".$LITAMBITO_GRUPOSSERVIDORESREMBO."'" . ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115; 
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresrembo.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_servidoresrembo.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SERVIDORESREMBO.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLGruposServidoresRembo(){
	global $AMBITO_SERVIDORESREMBO;
	global $AMBITO_GRUPOSSERVIDORESREMBO;
	global $LITAMBITO_GRUPOSSERVIDORESREMBO;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSSERVIDORESREMBO.'"';
	$layerXML.=' maxanchu=185';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSERVIDORESREMBO.',' ."'".$LITAMBITO_GRUPOSSERVIDORESREMBO."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresrembo.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_servidoresrembo.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SERVIDORESREMBO.')"';
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
//________________________________________________________________________________________________________
function CreacontextualXMLServidorRembo(){
	global $AMBITO_SERVIDORESREMBO;
	global $LITAMBITO_SERVIDORESREMBO;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_SERVIDORESREMBO.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_inforServidorrembo()"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_SERVIDORESREMBO.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresrembo.php";
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
//________________________________________________________________________________________________________
function CreacontextualXMLServidoresDhcp(){
	global $AMBITO_SERVIDORESDHCP;
	global $AMBITO_GRUPOSSERVIDORESDHCP;
	global $LITAMBITO_GRUPOSSERVIDORESDHCP;
	global $LITAMBITO_SERVIDORESDHCP;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_SERVIDORESDHCP.'"';
	$layerXML.=' maxanchu=190';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSERVIDORESDHCP.',' . "'".$LITAMBITO_GRUPOSSERVIDORESDHCP."'" . ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115; 
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresdhcp.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_servidoresdhcp.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SERVIDORESDHCP.')"';

	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLGruposServidoresDhcp(){
	global $AMBITO_SERVIDORESDHCP;
	global $AMBITO_GRUPOSSERVIDORESDHCP;
	global $LITAMBITO_GRUPOSSERVIDORESDHCP;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSSERVIDORESDHCP.'"';
	$layerXML.=' maxanchu=180';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSERVIDORESDHCP.',' ."'".$LITAMBITO_GRUPOSSERVIDORESDHCP."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';
	
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresdhcp.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_servidoresdhcp.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SERVIDORESDHCP.')"';
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
//________________________________________________________________________________________________________
function CreacontextualXMLServidorDhcp(){
	global $AMBITO_SERVIDORESDHCP;
	global $LITAMBITO_SERVIDORESDHCP;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_SERVIDORESDHCP.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_inforServidordhcp()"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_SERVIDORESDHCP.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_servidoresdhcp.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	

	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
