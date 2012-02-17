<?
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Marzo-2006
// Nombre del fichero: aulas.php
// Descripción : 
//		Administra grupos,aulas y ordenadores de un determinado Centro
// ****************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro,$nombrecentro); // Crea el arbol XML con todos los datos de aulas del Centro
// Creación del árbol
$baseurlimg="../images/signos"; // Url de las imágenes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,1,0,5); // Crea el árbol (formato XML)
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/aulas_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<FORM name="fcomandos" action="" method="post" target="frame_contenidos">
	<INPUT type="hidden" name="idcomando" value="">
	<INPUT type="hidden" name="descricomando" value="">
	<INPUT type="hidden" name="ambito" value="">
	<INPUT type="hidden" name="idambito" value="">
	<INPUT type="hidden" name="nombreambito" value="">
	<INPUT type="hidden" name="gestor" value="">
	<INPUT type="hidden" name="funcion" value="">
</FORM>
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	// Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

// Crea contextual de los Centros y aulas
$XMLcontextual=ContextualXMLCentros(); // Centros
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposAulas(); //  Grupos de aulas
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAulas();  // Aulas
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLUsuarios(); // Operadores
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLGruposOrdenadores();  // Grupos de ordenadores
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLOrdenadores();  // Ordenadores
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los comandos para los distintos �bitos
$XMLcontextual=ContextualXMLComandos($LITAMBITO_CENTROS,$AMBITO_CENTROS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_GRUPOSAULAS,$AMBITO_GRUPOSAULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_GRUPOSORDENADORES,$AMBITO_GRUPOSORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLComandos($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea submenu contextual de clase de gestion de arranque pxe
$XMLcontextual=ContextualXMLNetBoot();  // Crea submenu contextual de acciones
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea submenu contextual de la clase de asistentes.
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_CENTROS,$AMBITO_CENTROS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_GRUPOSAULAS,$AMBITO_GRUPOSAULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_AULAS,$AMBITO_AULAS);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_GRUPOSORDENADORES,$AMBITO_GRUPOSORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLAsistentes($LITAMBITO_ORDENADORES,$AMBITO_ORDENADORES);
echo $flotante->CreaMenuContextual($XMLcontextual);



//___________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML con toda la informaci� de aulas y ordenadores registrados en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexiónabierta)  
//		- idcentro: El identificador del centro
//		- nombrecentro: El nombre del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro,$nombrecentro){
	global $TbMsg;
	global $LITAMBITO_CENTROS;
	$cadenaXML='<CENTRO';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/centros.gif"';
	$cadenaXML.=' nodoid='.$LITAMBITO_CENTROS."-".$idcentro;
	$cadenaXML.=' infonodo="'.$nombrecentro.'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_CENTROS."'" .')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_aulas($cmd,$idcentro,0);
	$cadenaXML.='</CENTRO>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_aulas($cmd,$idcentro,$grupoid){
	global $TbMsg;
	global $LITAMBITO_GRUPOSAULAS;
	global $LITAMBITO_AULAS;
	global $AMBITO_GRUPOSAULAS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSAULAS." ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSAULAS';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSAULAS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSAULAS.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_aulas($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSAULAS>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cmd->texto="SELECT idaula,nombreaula FROM aulas WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." order by nombreaula";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<AULA ';
		// Atributos		
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_AULAS."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreaula"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_AULAS.'-'.$rs->campos["idaula"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_aulas_ordenadores($cmd,$rs->campos["idaula"],0);
		$cadenaXML.=SubarbolXML_aulas_operadores($cmd,$rs->campos["idaula"],&$cc);
		$cadenaXML.='</AULA>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_aulas_operadores($cmd,$idaula,$cont){
	global $TbMsg;
	global $LITAMBITO_USUARIOS;
	global $cadenaXML;
	global $OPERADOR;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idusuario,nombre FROM usuarios WHERE idtipousuario=".$OPERADOR." AND idambito=".$idaula." ORDER by nombre";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->EOF) return("");
	while (!$rs->EOF){
		$cont++;
		$cadenaXML.='<USUARIO';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/operadores.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_USUARIOS."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombre"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_USUARIOS.'-'.$rs->campos["idusuario"];
		$cadenaXML.='></USUARIO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_aulas_ordenadores($cmd,$idaula,$grupoid){
	global $TbMsg;
	global $LITAMBITO_GRUPOSORDENADORES;
	global $LITAMBITO_ORDENADORES;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupoordenador,grupoid FROM gruposordenadores WHERE grupoid=".$grupoid." AND idaula=".$idaula." ORDER BY  nombregrupoordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSORDENADORES';
		// Atributos	
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_GRUPOSORDENADORES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupoordenador"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSORDENADORES.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_aulas_ordenadores($cmd,$idaula,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSORDENADORES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cmd->texto="SELECT idordenador,nombreordenador FROM ordenadores WHERE grupoid=".$grupoid." AND idaula=".$idaula." order by nombreordenador desc";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<ORDENADOR';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/ordenador.gif"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_ORDENADORES."'" .')"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreordenador"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_ORDENADORES.'-'.$rs->campos["idordenador"];
		$cadenaXML.='></ORDENADOR>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Mens Contextuales
//________________________________________________________________________________________________________
function ContextualXMLCentros(){
	global $TbMsg;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSAULAS;
	global $LITAMBITO_GRUPOSAULAS;
	global $AMBITO_CENTROS;
	global $LITAMBITO_CENTROS;
	global $RESERVA_CONFIRMADA;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_CENTROS.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_aulas()"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSAULAS.',' ."'".$LITAMBITO_GRUPOSAULAS."',1". ')"';

	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_aulas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',1)"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota('.$AMBITO_CENTROS.')"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_CENTROS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_CENTROS.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[29];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposAulas(){
	global $TbMsg;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSAULAS;
	global $LITAMBITO_GRUPOSAULAS;
	global $RESERVA_CONFIRMADA;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSAULAS.'"';
	$layerXML.=' maxanchu=155';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';



	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_aulas()"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';


	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSAULAS.',' ."'".$LITAMBITO_GRUPOSAULAS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_aulas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[3];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota('.$AMBITO_GRUPOSAULAS.')"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_GRUPOSAULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_GRUPOSAULAS.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[29];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLAulas(){
	global $TbMsg;
	global $AMBITO_AULAS;
	global $LITAMBITO_AULAS;
	global $RESERVA_CONFIRMADA;
	global $OPERADOR;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' maxanchu=190';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	// Pasar al menú la única opción del submenú "NetBoot Gestión".
	//$layerXML.=' subflotante="flo_netboot"';
	$layerXML.=' alpulsar="ver_boot()"';
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.=' textoitem='.$TbMsg[40];
	$layerXML.='></ITEM>';

//adv compatiblidad Configurador de Startpages
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_ubicarordenadores()"';
	$layerXML.=' textoitem='.$TbMsg[41];
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.='></ITEM>';
//adv compatiblidad Configurador de Startpages

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_aulas()"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_AULAS.',' ."'".$LITAMBITO_AULAS."'". ')"';

	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar_ordenador(1)"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_ordenadores.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/ordenador.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="incorporarordenador()"';
	$layerXML.=' imgitem="../images/iconos/aula.gif"';
	$layerXML.=' textoitem='.$TbMsg[27];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota('.$AMBITO_AULAS.')"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_asistentes_'.$LITAMBITO_AULAS.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[38];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_AULAS.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="configuraciones('.$AMBITO_AULAS.')"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.=' imgitem="../images/iconos/configuraciones.gif"';
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_aulas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	// Crear operador
	$wLeft=140;
	$wTop=115;
	$wWidth=400;
	$wHeight=320;
	$wpages="../propiedades/propiedades_usuarios.php?idtipousuario=".$OPERADOR;
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.',3)"';
	$layerXML.=' imgitem="../images/iconos/operadores.gif"';
	$layerXML.=' textoitem='.$TbMsg[37];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_reservas('.$RESERVA_CONFIRMADA.')"';
	$layerXML.=' imgitem="../images/iconos/reservas.gif"';
	$layerXML.=' textoitem='.$TbMsg[29];
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
	$layerXML.=' maxanchu=130';
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
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[36];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposOrdenadores(){
	global $TbMsg;
	global $AMBITO_GRUPOSORDENADORES;
	global $LITAMBITO_GRUPOSORDENADORES;
	$layerXML='<MENUCONTEXTUAL';

	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSORDENADORES.'"';
	$layerXML.=' maxanchu=195';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	
	$layerXML.='<ITEM';
	// Pasar al menú la única opción del submenú "NetBoot Gestión".
	//$layerXML.=' subflotante="flo_netboot"';
	$layerXML.=' alpulsar="ver_boot()"';
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.=' textoitem='.$TbMsg[40];
	$layerXML.='></ITEM>';
	

        
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_aulas()"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

 	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSORDENADORES.',' ."'".$LITAMBITO_GRUPOSORDENADORES."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=480;
	$wpages="../propiedades/propiedades_ordenadores.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/ordenador.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota('.$AMBITO_GRUPOSORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_GRUPOSORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_asistentes_'.$LITAMBITO_GRUPOSORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[38];
	$layerXML.='></ITEM>';


	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_GRUPOSORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar_ordenador(0)"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="configuraciones('.$AMBITO_GRUPOSORDENADORES.')"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.=' imgitem="../images/iconos/configuraciones.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[16];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLOrdenadores(){
	global $TbMsg;
	global $AMBITO_ORDENADORES;
	global $LITAMBITO_ORDENADORES;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="cola_acciones()"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_log('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[47];
	$layerXML.='></ITEM>';
 
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_log_seguimiento('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem='.$TbMsg[48];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover_ordenador()"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[17];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="actualizar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/actualizar.gif"';
	$layerXML.=' textoitem='.$TbMsg[4];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="purgar_ordenadores()"';
	$layerXML.=' imgitem="../images/iconos/purgar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="consola_remota('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/shell.gif"';
	$layerXML.=' textoitem='.$TbMsg[33];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eco_remoto()"';
	$layerXML.=' imgitem="../images/iconos/ecocon.gif"';
	$layerXML.=' textoitem='.$TbMsg[39];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_comandos_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';


	$layerXML.='<ITEM';
	$layerXML.=' subflotante="flo_asistentes_'.$LITAMBITO_ORDENADORES.'"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[38];
	$layerXML.='></ITEM>';


	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="confirmarprocedimiento('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="configuraciones('.$AMBITO_ORDENADORES.')"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.=' imgitem="../images/iconos/configuraciones.gif"';
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=80;
	$wWidth=480;
	$wHeight=400;
	$wpages="../propiedades/propiedades_ordenadores.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	

	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';	
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[18];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLComandos($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM comandos 
			WHERE activo=1 AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["COMMAND_".$rs->campos["funcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["funcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if ($maxlongdescri < strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_comandos_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*7;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}


//________________________________________________________________________________________________________
function ContextualXMLAsistentes($litambito,$ambito){
	global $cmd;
	global $TbMsg;
 	$maxlongdescri=0;
	$rs=new Recordset; 
	$cmd->texto="SELECT  idcomando,descripcion,pagina,gestor,funcion 
			FROM asistentes 
			WHERE activo=1 AND aplicambito & ".$ambito.">0 
			ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if ($rs->Abrir()){
		$layerXML="";
		$rs->Primero(); 
		while (!$rs->EOF){
			$descrip=$TbMsg["WIZARD_".$rs->campos["descripcion"]];
			if (empty ($descrip)) {
				$descrip=$rs->campos["descripcion"];
			}
			$layerXML.='<ITEM';
			$layerXML.=' alpulsar="confirmarcomando('."'".$ambito."'".','.$rs->campos["idcomando"].',\''.$rs->campos["descripcion"].'\',\''.$rs->campos["pagina"]. '\',\''.$rs->campos["gestor"]. '\',\''.$rs->campos["funcion"]. '\')"';
			$layerXML.=' textoitem="'.$descrip.'"';
			$layerXML.='></ITEM>';
			if($maxlongdescri<strlen($descrip)) // Toma la Descripción de mayor longitud
				$maxlongdescri=strlen($descrip);
			$rs->Siguiente();
		}
	$layerXML.='</MENUCONTEXTUAL>';
	$prelayerXML='<MENUCONTEXTUAL';
	$prelayerXML.=' idctx="flo_asistentes_'.$litambito.'"';
	$prelayerXML.=' maxanchu='.$maxlongdescri*7;
	$prelayerXML.=' clase="menu_contextual"';
	$prelayerXML.='>';
	$finallayerXML=$prelayerXML.$layerXML;
	return($finallayerXML);
	}
}


function ContextualXMLNetBoot(){
        #global $TbMsg;
        #global $EJECUCION_COMANDO;
        #global $EJECUCION_TAREA;
        #global $EJECUCION_TRABAJO;

        $layerXML='<MENUCONTEXTUAL';
        $layerXML.=' idctx="flo_netboot"';
        $layerXML.=' maxanchu=190';
        $layerXML.=' swimg=1';
        $layerXML.=' clase="menu_contextual"';
        $layerXML.='>';


//adv compatiblidad Gestor de arranque remoto
        $layerXML.='<ITEM';
        $layerXML.=' alpulsar="ver_boot()"';
        $layerXML.=' textoitem="NetBoot AVANZADO"';
        $layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
        $layerXML.='></ITEM>';
//adv compatiblidad Gestor de arranque remoto
//adv compatiblidad Configurador de Startpages
  //      $layerXML.='<ITEM';
  //      $layerXML.=' alpulsar="ver_startpages()"';
  //      $layerXML.=' textoitem="Gestor Startpages"';
  //      $layerXML.=' imgitem="../images/iconos/ordenadores.gif"';
  //      $layerXML.='></ITEM>';
//adv compatiblidad Configurador de Startpages

        $layerXML.='</MENUCONTEXTUAL>';
        return($layerXML);
}

?>
