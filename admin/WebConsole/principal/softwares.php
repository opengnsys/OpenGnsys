<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: software.php
// Descripción : 
//		Administra el software de los ordenadores de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/softwares_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos los datos del Centro
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
	<SCRIPT language="javascript" src="../jscripts/softwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/softwares_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	 // Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

// Crea contextual de componentes componentes software
$XMLcontextual=CreacontextualXMLComponentes_Software();
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposComponentes(); // Grupos de componentes
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLComponente_Software(); // Crea menu contextual de componentes softwares
echo $flotante->CreaMenuContextual($XMLcontextual); 

// Crea contextual de perfiles software
$XMLcontextual=CreacontextualXMLPerfiles_Software(); 
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposPerfiles();		// Grupos de perfiles
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLPerfil_Software(); // Crea menu contextual de perfiles softwares
echo $flotante->CreaMenuContextual($XMLcontextual); 

// Crea contextual de softtware incremental
$XMLcontextual=CreacontextualXMLSoftwares_Incrementales(); 
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposIncrementales(); // Grupos de  software incremental
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLSoftware_Incremental(); // Crea menu contextual de software incremental
echo $flotante->CreaMenuContextual($XMLcontextual); 

//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML de toda la información del software registrado en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexión abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	$cadenaXML='<SOFTWARES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/confisoft.gif"';
	$cadenaXML.=' infonodo="Software"';
	$cadenaXML.=' nodoid="RaizSoftwares"';
	$cadenaXML.='>';
	$cadenaXML.='<TIPOS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[18];
	$cadenaXML.=' nodoid="RaizTipoSoftwares"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_TiposSoftwares($cmd);
	$cadenaXML.='</TIPOS>';
	$cadenaXML.='<COMPONENTES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[19];
	$cadenaXML.=' nodoid="RaizComponentesSoftwares"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizComponentesSoftwares'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_componentessoft($cmd,$idcentro,0);
	$cadenaXML.='</COMPONENTES>';

	$cadenaXML.='<PERFILES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[20];
	$cadenaXML.=' nodoid="RaizPerfilesSoftwares"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizPerfilesSoftwares'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_perfilessoft($cmd,$idcentro,0);
	$cadenaXML.='</PERFILES>';

/*
	$cadenaXML.='<SOFTINCREMENTALES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[21];
	$cadenaXML.=' nodoid="RaizSoftwaresIncrementales"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_RaizSoftwaresIncrementales'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_softincremental($cmd,$idcentro,0);
	$cadenaXML.='</SOFTINCREMENTALES>';
*/
	$cadenaXML.='</SOFTWARES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_TiposSoftwares($cmd){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idtiposoftware,descripcion,urlimg FROM tiposoftwares order by descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<TIPOSOFTWARES';
		// Atributos
		$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid="TipoSoftwares"';
		$cadenaXML.='>';
		$cadenaXML.='</TIPOSOFTWARES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_componentessoft($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSCOMPONENTESSOFT;
	global $AMBITO_GRUPOSCOMPONENTESSOFT;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSCOMPONENTESSOFT." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSCOMPONENTESSOFT';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSCOMPONENTESSOFT."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSCOMPONENTESSOFT.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_componentessoft($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSCOMPONENTESSOFT>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_ComponentesSoftwares($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ComponentesSoftwares($cmd,$idcentro,$grupoid){
	global $LITAMBITO_COMPONENTESSOFT;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softwares.idsoftware,softwares.descripcion,tiposoftwares.urlimg FROM softwares INNER JOIN tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware WHERE idcentro=".$idcentro." AND grupoid=". $grupoid." order by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<COMPONENTES';
		// Atributos
		if ($rs->campos["urlimg"]!="")
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
		else
			$cadenaXML.=' imagenodo="../images/iconos/confisoft.gif"';	
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_COMPONENTESSOFT.'-'.$rs->campos["idsoftware"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_COMPONENTESSOFT."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</COMPONENTES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_perfilessoft($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSPERFILESSOFT;
	global $AMBITO_GRUPOSPERFILESSOFT;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSPERFILESSOFT." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSPERFILESSOFT';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSPERFILESSOFT."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSPERFILESSOFT.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_perfilessoft($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSPERFILESSOFT>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_PerfilesSoftwares($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesSoftwares($cmd,$idcentro,$grupoid){
	global $LITAMBITO_PERFILESSOFT;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion FROM perfilessoft WHERE perfilessoft.idcentro=".$idcentro." AND perfilessoft.grupoid=". $grupoid;
	$cmd->texto.=" ORDER by perfilessoft.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<PERFILESSOFTWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_PERFILESSOFT.'-'.$rs->campos["idperfilsoft"];
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PERFILESSOFT."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</PERFILESSOFTWARES>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_softincremental($cmd,$idcentro,$grupoid){
	global $LITAMBITO_GRUPOSSOFTINCREMENTAL;
	global $AMBITO_GRUPOSSOFTINCREMENTAL;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSSOFTINCREMENTAL." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSSOFTINCREMENTAL';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSSOFTINCREMENTAL."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSSOFTINCREMENTAL.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_softincremental($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSSOFTINCREMENTAL>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_SoftwaresIncrementales($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_SoftwaresIncrementales($cmd,$idcentro,$grupoid){
	global $LITAMBITO_SOFTINCREMENTAL;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softincrementales.idsoftincremental ,softincrementales.descripcion FROM softincrementales WHERE softincrementales.idcentro=".$idcentro." AND softincrementales.grupoid=". $grupoid;
	$cmd->texto.=" ORDER by softincrementales.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<SOFTINCREMENTAL';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/incremental.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_SOFTINCREMENTAL.'-'.$rs->campos["idsoftincremental"];
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_SOFTINCREMENTAL."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</SOFTINCREMENTAL>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Menús Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLComponentes_Software(){
	global $AMBITO_COMPONENTESSOFT;
	global $AMBITO_GRUPOSCOMPONENTESSOFT;
	global $LITAMBITO_GRUPOSCOMPONENTESSOFT;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizComponentesSoftwares"';
	$layerXML.=' maxanchu=185';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSCOMPONENTESSOFT.',' . "'".$LITAMBITO_GRUPOSCOMPONENTESSOFT."'" . ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115; 
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_componentesoftwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_componentesoftwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_COMPONENTESSOFT.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposComponentes(){
	global $AMBITO_COMPONENTESSOFT;
	global $AMBITO_GRUPOSCOMPONENTESSOFT;
	global $LITAMBITO_GRUPOSCOMPONENTESSOFT;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSCOMPONENTESSOFT.'"';
	$layerXML.=' maxanchu=195';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSCOMPONENTESSOFT.',' ."'".$LITAMBITO_GRUPOSCOMPONENTESSOFT."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';
	
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_componentesoftwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_componentesoftwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_COMPONENTESSOFT.')"';
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
function CreacontextualXMLComponente_Software(){
	global $AMBITO_COMPONENTESSOFT;
	global $LITAMBITO_COMPONENTESSOFT;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_COMPONENTESSOFT.'"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_COMPONENTESSOFT.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115; 
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_componentesoftwares.php";
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
function CreacontextualXMLPerfiles_Software(){
	global $AMBITO_PERFILESSOFT;
	global $AMBITO_GRUPOSPERFILESSOFT;
	global $LITAMBITO_GRUPOSPERFILESSOFT;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizPerfilesSoftwares"';
	$layerXML.=' maxanchu=155';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPERFILESSOFT.',' ."'".$LITAMBITO_GRUPOSPERFILESSOFT."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150; 
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilsoftwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_perfilsoftwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PERFILESSOFT.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposPerfiles(){
	global $AMBITO_PERFILESSOFT;
	global $AMBITO_GRUPOSPERFILESSOFT;
	global $LITAMBITO_GRUPOSPERFILESSOFT;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSPERFILESSOFT.'"';
	$layerXML.=' maxanchu=175';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPERFILESSOFT.',' ."'".$LITAMBITO_GRUPOSPERFILESSOFT."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilsoftwares.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_perfilsoftwares.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PERFILESSOFT.')"';
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
function CreacontextualXMLPerfil_Software(){
	global $AMBITO_PERFILESSOFT;
	global $LITAMBITO_PERFILESSOFT;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_PERFILESSOFT.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_perfilcomponente()"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="informacion_perfiles()"';
	$layerXML.=' textoitem='.$TbMsg[15];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_PERFILESSOFT.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[16];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_perfilsoftwares.php";
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
//________________________________________________________________________________________________________
function CreacontextualXMLSoftwares_Incrementales(){
	global $AMBITO_SOFTINCREMENTAL;
	global $AMBITO_GRUPOSSOFTINCREMENTAL;
	global $LITAMBITO_GRUPOSSOFTINCREMENTAL;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_RaizSoftwaresIncrementales"';
	$layerXML.=' maxanchu=190';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSOFTINCREMENTAL.',' ."'".$LITAMBITO_GRUPOSSOFTINCREMENTAL."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[22];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150; 
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_softincrementales.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[23];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_softincrementales.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SOFTINCREMENTAL.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[24];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposIncrementales(){
	global $AMBITO_SOFTINCREMENTAL;
	global $AMBITO_GRUPOSSOFTINCREMENTAL;
	global $LITAMBITO_GRUPOSSOFTINCREMENTAL;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSSOFTINCREMENTAL.'"';
	$layerXML.=' maxanchu=195';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSSOFTINCREMENTAL.',' ."'".$LITAMBITO_GRUPOSSOFTINCREMENTAL."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[22];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_softincrementales.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[23];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_softincrementales.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_SOFTINCREMENTAL.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[24];
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
	$layerXML.=' textoitem='.$TbMsg[25];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLSoftware_Incremental(){
	global $AMBITO_SOFTINCREMENTAL;
	global $LITAMBITO_SOFTINCREMENTAL;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_SOFTINCREMENTAL.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_incrementalcomponente()"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="informacion_incrementales()"';
	$layerXML.=' textoitem='.$TbMsg[26];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_SOFTINCREMENTAL.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[27];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=280;
	$wpages="../propiedades/propiedades_softincrementales.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
