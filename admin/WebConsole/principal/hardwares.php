<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: hardwares.php
// Descripción : 
//		Administra el hardware de los ordenadores de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/hardwares_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexióncon servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos los datos del Centro
// Creación del árbol
$baseurlimg="../images/signos"; // Url de las im�enes de signo
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
	<SCRIPT language="javascript" src="../jscripts/hardwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/hardwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	 // Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

// Crea contextual de tipos de hardware
$XMLcontextual=CreacontextualXMLTipos_Hardware(); 
 echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLTipoHardware(); 
echo $flotante->CreaMenuContextual($XMLcontextual); 

// Crea contextual de componentes hardware
$XMLcontextual=CreacontextualXMLComponentes_Hardware(); 
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposComponentes(); // Grupos de componentes
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLComponente_Hardware(); // Componentes
 echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de perfiles hardware
$XMLcontextual=CreacontextualXMLPerfiles_Hardware(); 
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLGruposPerfiles(); // Grupos de perfiles
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLPerfil_Hardware(); // Perfiles
 echo $flotante->CreaMenuContextual($XMLcontextual);
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML de toda la informaci� del hardware registrado en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexiónabierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	$cadenaXML='<HARDWARES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/confihard.gif"';
	$cadenaXML.=' nodoid=RaizHardwares';
	$cadenaXML.=' infonodo="Hardware"';
	$cadenaXML.='>';
	$cadenaXML.='<TIPOS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[18];
	$cadenaXML.=' nodoid=RaizTipoHardwares';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizTipoHardwares'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_TiposHardwares($cmd);
	$cadenaXML.='</TIPOS>';
	$cadenaXML.='<COMPONENTES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[19];
	$cadenaXML.=' nodoid=RaizComponentesHardwares';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizComponentesHardwares'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_componenteshard($cmd,$idcentro,0);
	$cadenaXML.='</COMPONENTES>';
	$cadenaXML.='<PERFILES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[20];
	$cadenaXML.=' nodoid=RaizPerfilesHardwares';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizPerfilesHardwares'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_perfileshard($cmd,$idcentro,0);
	$cadenaXML.='</PERFILES>';
	$cadenaXML.='</HARDWARES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_TiposHardwares($cmd){
	global 	$LITAMBITO_TIPOHARDWARES;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idtipohardware,descripcion,urlimg FROM tipohardwares order by descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<TIPOHARDWARES';
		// Atributos
		if	($rs->campos["urlimg"]!="")
				$cadenaXML.=' imagenodo="'.$rs->campos["urlimg"].'"';
			else
				$cadenaXML.=' imagenodo="../images/iconos/confihard.gif"';		
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TIPOHARDWARES."'" .')"';
		$cadenaXML.=' nodoid='.$LITAMBITO_TIPOHARDWARES.'-'.$rs->campos["idtipohardware"];

		$cadenaXML.='>';
		$cadenaXML.='</TIPOHARDWARES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_componenteshard($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSCOMPONENTESHARD;
	global $AMBITO_GRUPOSCOMPONENTESHARD;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSCOMPONENTESHARD." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSCOMPONENTESHARD';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSCOMPONENTESHARD."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSCOMPONENTESHARD.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_componenteshard($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSCOMPONENTESHARD>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_ComponentesHardwares($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ComponentesHardwares($cmd,$idcentro,$grupoid){
	global $LITAMBITO_COMPONENTESHARD;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT hardwares.idhardware,hardwares.descripcion,tipohardwares.urlimg FROM hardwares INNER JOIN tipohardwares  ON hardwares.idtipohardware=tipohardwares.idtipohardware WHERE idcentro=".$idcentro." AND grupoid=". $grupoid." order by tipohardwares.idtipohardware,hardwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<COMPONENTES';
		// Atributos
		if ($rs->campos["urlimg"]!="")
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
		else
			$cadenaXML.=' imagenodo="../images/iconos/confihard.gif"';		


		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_COMPONENTESHARD.'-'.$rs->campos["idhardware"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_COMPONENTESHARD."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</COMPONENTES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_perfileshard($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSPERFILESHARD;
	global $AMBITO_GRUPOSPERFILESHARD;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSPERFILESHARD." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSPERFILESHARD';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSPERFILESHARD."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSPERFILESHARD.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_perfileshard($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSPERFILESHARD>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_PerfilesHardwares($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesHardwares($cmd,$idcentro,$grupoid){
	global $LITAMBITO_PERFILESHARD;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfileshard.idperfilhard ,perfileshard.descripcion FROM perfileshard WHERE perfileshard.idcentro=".$idcentro." AND perfileshard.grupoid=". $grupoid;
	$cmd->texto.=" ORDER by perfileshard.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<PERFILESHARDWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilhardware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_PERFILESHARD.'-'.$rs->campos["idperfilhard"];
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PERFILESHARD."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</PERFILESHARDWARES>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Mens Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLTipos_Hardware(){
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizTipoHardwares"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=240;
	$wpages="../propiedades/propiedades_tipohardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];

	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLTipoHardware(){
	global $LITAMBITO_TIPOHARDWARES;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_TIPOHARDWARES.'"';
	$layerXML.=' maxanchu=165';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=240;
	$wpages="../propiedades/propiedades_tipohardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLComponentes_Hardware(){
	global $AMBITO_COMPONENTESHARD;
	global $AMBITO_GRUPOSCOMPONENTESHARD;
	global $LITAMBITO_GRUPOSCOMPONENTESHARD;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizComponentesHardwares"';
	$layerXML.=' maxanchu=185';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSCOMPONENTESHARD.',' . "'".$LITAMBITO_GRUPOSCOMPONENTESHARD."'" . ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150; 
	$wWidth=480;
	$wHeight=230;
	$wpages="../propiedades/propiedades_componentehardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_componentehardwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_COMPONENTESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>'; 
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposComponentes(){
	global $AMBITO_COMPONENTESHARD;
	global $AMBITO_GRUPOSCOMPONENTESHARD;
	global $LITAMBITO_GRUPOSCOMPONENTESHARD;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSCOMPONENTESHARD.'"';
	$layerXML.=' maxanchu=195';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSCOMPONENTESHARD.',' ."'".$LITAMBITO_GRUPOSCOMPONENTESHARD."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=230;
	$wpages="../propiedades/propiedades_componentehardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_componentehardwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_COMPONENTESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLComponente_Hardware(){
	global $AMBITO_COMPONENTESHARD;
	global $LITAMBITO_COMPONENTESHARD;
	global $TbMsg;
 
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_COMPONENTESHARD.'"';
	$layerXML.=' maxanchu=145';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_COMPONENTESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=230;
	$wpages="../propiedades/propiedades_componentehardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLPerfiles_Hardware(){
	global $AMBITO_PERFILESHARD;
	global $AMBITO_GRUPOSPERFILESHARD;
	global $LITAMBITO_GRUPOSPERFILESHARD;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizPerfilesHardwares"';
	$layerXML.=' maxanchu=155';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPERFILESHARD.',' ."'".$LITAMBITO_GRUPOSPERFILESHARD."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150; 
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilhardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_perfilhardwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PERFILESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposPerfiles(){
	global $AMBITO_PERFILESHARD;
	global $AMBITO_GRUPOSPERFILESHARD;
	global $LITAMBITO_GRUPOSPERFILESHARD;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSPERFILESHARD.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPERFILESHARD.',' ."'".$LITAMBITO_GRUPOSPERFILESHARD."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilhardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_perfilhardwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PERFILESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLPerfil_Hardware(){
	global $AMBITO_PERFILESHARD;
	global $LITAMBITO_PERFILESHARD;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_PERFILESHARD.'"';
	$layerXML.=' maxanchu=155';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_perfilcomponente()"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="muestra_informacion()"';
	$layerXML.=' textoitem='.$TbMsg[15];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_PERFILESHARD.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[16];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilhardwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[17];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
