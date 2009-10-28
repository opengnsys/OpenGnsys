<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: ogAdmWebCon
// Autor: Jos�Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Febrero-2005
// Nombre del fichero: administracion.php
// Descripci� : 
//		Administra tablas varias : ADMINISTRACION,Campus,Instituciones,iconos, etc ...
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/administracion_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
$cadenaXML="";
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi� con servidor B.D.
else
	$arbolXML=CreaArbol($cmd); // Crea el arbol XML con todos los datos de administracion
// Creaci� del �bol
$baseurlimg="../images/signos"; // Url de las im�enes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del �bol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,2,0,5); // Crea el �bol (formato XML)
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/administracion.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/administracion_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	// Crea �bol (HTML) a partir del XML

$flotante=new MenuContextual();			// Crea objeto MenuContextual
// Crea contextual de las im�enes
 $XMLcontextual=CreacontextualXMLUniversidades();
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=CreacontextualXMLUsuarios();
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=CreacontextualXMLGruposEntidades();
 echo $flotante->CreaMenuContextual($XMLcontextual);
 $XMLcontextual=CreacontextualXMLEntidades();
 echo $flotante->CreaMenuContextual($XMLcontextual);
  $XMLcontextual=CreacontextualXMLCentros();
 echo $flotante->CreaMenuContextual($XMLcontextual);
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________

?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML con toda la informaci� de aulas y ordenadores registrados en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexi� abierta)  
//		- idcentro: El identificador del centro
//		- nombrecentro: El nombre del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd){
	global $TbMsg;
	global $LITAMBITO_ADMINISTRACION;
	global $cadenaXML;

	$cadenaXML='<RAIZ';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/administracion.gif"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_ADMINISTRACION;
	$cadenaXML.=' infonodo='.$TbMsg[0];
	$cadenaXML.='>';
	SubarbolXML_universidades($cmd);
	$cadenaXML.='</RAIZ>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_universidades($cmd){
	global $TbMsg;
	global $LITAMBITO_GRUPOSUNIVERSIDADES;
	global $LITAMBITO_UNIVERSIDADES;
	global $AMBITO_GRUPOSUNIVERSIDADES;
	global $cadenaXML;

	$rs=new Recordset; 
	$cmd->texto="SELECT iduniversidad,nombreuniversidad FROM universidades";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<UNIVERSIDAD ';
		// Atributos		
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_UNIVERSIDADES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/universidades.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreuniversidad"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_UNIVERSIDADES;
		$cadenaXML.='>';
		SubarbolXML_superadministradores($cmd,$rs->campos["iduniversidad"],0);
		SubarbolXML_universidades_entidades($cmd,$rs->campos["iduniversidad"],0);
		$cadenaXML.='</UNIVERSIDAD>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function SubarbolXML_superadministradores($cmd){
	global $TbMsg;
	global $LITAMBITO_USUARIOS;
	global $cadenaXML;
	global $SUPERADMINISTRADOR;
	$rs=new Recordset; 
	$cmd->texto="SELECT idusuario,nombre FROM usuarios WHERE idtipousuario=".$SUPERADMINISTRADOR." ORDER by nombre";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<USUARIO';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/superadministradores.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_USUARIOS."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombre"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_USUARIOS.'-'.$rs->campos["idusuario"];
		$cadenaXML.='></USUARIO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function SubarbolXML_universidades_entidades($cmd,$iduniversidad,$grupoid){
	global $TbMsg;
	global $LITAMBITO_GRUPOSENTIDADES;
	global $LITAMBITO_ENTIDADES;
	global $cadenaXML;
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=0 AND iduniversidad=".$iduniversidad." ORDER BY  nombregrupo";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSENTIDADES';
		// Atributos	
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_GRUPOSENTIDADES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSENTIDADES.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		SubarbolXML_universidades_entidades($cmd,$iduniversidad,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSENTIDADES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cmd->texto="SELECT identidad,nombreentidad FROM entidades WHERE grupoid=".$grupoid." AND iduniversidad=".$iduniversidad." ORDER by nombreentidad desc";
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<ENTIDAD';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/entidades.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_ENTIDADES."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreentidad"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_ENTIDADES.'-'.$rs->campos["identidad"];
		$cadenaXML.='>';
		SubarbolXML_entidades_centros($cmd,$rs->campos["identidad"]);
		$cadenaXML.='</ENTIDAD>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
}

//________________________________________________________________________________________________________
function SubarbolXML_entidades_centros($cmd,$identidad){
	global $TbMsg;
	global $LITAMBITO_CENTROS;
	global $cadenaXML;

	$rs=new Recordset; 
	$cmd->texto="SELECT idcentro,nombrecentro FROM centros WHERE  identidad=".$identidad." ORDER by nombrecentro";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<CENTRO';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/centros.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_CENTROS."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombrecentro"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_CENTROS.'-'.$rs->campos["idcentro"];
		$cadenaXML.='>';
		SubarbolXML_administradores($cmd,$rs->campos["idcentro"]);
		$cadenaXML.='</CENTRO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function SubarbolXML_administradores($cmd,$idambito){
	global $TbMsg;
	global $LITAMBITO_USUARIOS;
	global $cadenaXML;
	global $ADMINISTRADOR;
	$rs=new Recordset; 
	$cmd->texto="SELECT idusuario,nombre FROM usuarios WHERE idtipousuario=".$ADMINISTRADOR." AND idambito=".$idambito." ORDER by nombre";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<USUARIO';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/administradores.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_USUARIOS."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombre"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_USUARIOS.'-'.$rs->campos["idusuario"];
		$cadenaXML.='></USUARIO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
//
//	Mens Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLUniversidades(){
	global $LITAMBITO_GRUPOSENTIDADES;
	global $AMBITO_GRUPOSENTIDADES;
	global $LITAMBITO_UNIVERSIDADES;
	global $SUPERADMINISTRADOR;
	global $TbMsg;
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_UNIVERSIDADES.'"';
	$layerXML.=' maxanchu=170';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	// Crear grupos de entidades
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSENTIDADES.',' ."'".$LITAMBITO_GRUPOSENTIDADES."'". ',1,1)"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	// Crear entidades
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_entidades.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',1,1)"';
	$layerXML.=' imgitem="../images/iconos/entidades.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	// Crear superadministrador
	$wLeft=140;
	$wTop=115;
	$wWidth=400;
	$wHeight=320;
	$wpages="../propiedades/propiedades_usuarios.php?idtipousuario=".$SUPERADMINISTRADOR;
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/superadministradores.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	// Modificar Universidad 
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_universidades.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLUsuarios(){
	global $LITAMBITO_USUARIOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_USUARIOS.'"';
	$layerXML.=' maxanchu=90';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	// Modificar usuarios
	$wLeft=140;
	$wTop=115;
	$wWidth=400;
	$wHeight=320;
	$wpages="../propiedades/propiedades_usuarios.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLGruposEntidades(){
	global $LITAMBITO_GRUPOSENTIDADES;
	global $AMBITO_GRUPOSENTIDADES;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSENTIDADES.'"';
	$layerXML.=' maxanchu=180';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	// Crear grupos de entidades
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSENTIDADES.',' ."'".$LITAMBITO_GRUPOSENTIDADES."'". ',1,1)"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	// Crear entidades
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_entidades.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',1,1)"';
	$layerXML.=' imgitem="../images/iconos/entidades.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
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
function CreacontextualXMLEntidades(){
	global $LITAMBITO_ENTIDADES;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_ENTIDADES.'"';
	$layerXML.=' maxanchu=170';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	// Crear centros
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_centros.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',1,2)"';
	$layerXML.=' imgitem="../images/iconos/centros.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

// Modificar entidades
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_entidades.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLCentros(){
	global $ADMINISTRADOR;
	global $LITAMBITO_CENTROS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_CENTROS.'"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	// Crear superadministrador
	$wLeft=140;
	$wTop=115;
	$wWidth=400;
	$wHeight=320;
	$wpages="../propiedades/propiedades_usuarios.php?idtipousuario=".$ADMINISTRADOR;
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',1,3)"';
	$layerXML.=' imgitem="../images/iconos/administradores.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=280;
	$wpages="../propiedades/propiedades_centros.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
