<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: acciones.php
// Descripción :
//		Administra procedimientos,y tareas de un determinado Centro
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../clases/MenuContextual.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/acciones_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexióncon servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos los datos de las acciones registradas en el Centro
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
	<META HTTP-EQUIV="Content-Type"  CONTENT="text/html;charset=ISO-8859-1"> 
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/acciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/acciones_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?php
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	 // Crea árbol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

$XMLcontextual=CreacontextualXMLComandos(); // comandos
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLComando(); // comando
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de los procedimientos
$XMLcontextual=CreacontextualXMLProcedimientos(); 
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=ContextualXMLGruposProcedimientos(); // Grupo de Procedimientos
echo $flotante->CreaMenuContextual($XMLcontextual);
$XMLcontextual=CreacontextualXMLProcedimiento(); // Procedimientos
echo $flotante->CreaMenuContextual($XMLcontextual);

// Crea contextual de las tareas
$XMLcontextual=CreacontextualXMLTareas(); 
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=ContextualXMLGruposTareas(); // Grupo de Tareas
echo $flotante->CreaMenuContextual($XMLcontextual);  
$XMLcontextual=CreacontextualXMLTarea(); // Tareas
echo $flotante->CreaMenuContextual($XMLcontextual); 

?>
</BODY>
</HTML>
<?php
// ********************************************************************************************************
//	Devuelve una cadena con formato XML con toda la informaci� de las acciones registradas en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexiónabierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro)
{
	global $TbMsg;
	global $LITAMBITO_COMANDOS;
	global $LITAMBITO_PROCEDIMIENTOS;
	global $LITAMBITO_TAREAS;

	$cadenaXML='<ACCIONES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/acciones.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[33].'"';
	$cadenaXML.=' nodoid="RaizAcciones"';
	$cadenaXML.='>';
	
	$cadenaXML.='<COMANDOS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[30].'"';
	$cadenaXML.=' nodoid="RaizComandos"';
	//$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_Raiz".$LITAMBITO_COMANDOS."'" .');"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_Comandos($cmd);
	$cadenaXML.='</COMANDOS>';

	$cadenaXML.='<PROCEDIMIENTOS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[29].'"';
	$cadenaXML.=' nodoid=Raizpro'.$LITAMBITO_PROCEDIMIENTOS;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_PROCEDIMIENTOS."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_procedimientos($cmd,$idcentro,0);
	$cadenaXML.='</PROCEDIMIENTOS>';

	$cadenaXML.='<TAREAS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[31].'"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_TAREAS;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_TAREAS."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_tareas($cmd,$idcentro,0);
	$cadenaXML.='</TAREAS>';

	$cadenaXML.='</ACCIONES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Comandos($cmd)
{
	global $LITAMBITO_COMANDOS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idcomando,descripcion,urlimg  FROM comandos  Where activo=1 order by descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<COMANDO';
		// Atributos
		//$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_COMANDOS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/comandos.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid=comando-'.$rs->campos["idcomando"];
		$cadenaXML.='>';
		$cadenaXML.='</COMANDO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_procedimientos($cmd,$idcentro,$grupoid){
	global $AMBITO_GRUPOSPROCEDIMIENTOS;
	global $LITAMBITO_GRUPOSPROCEDIMIENTOS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos
												 WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSPROCEDIMIENTOS." 
													ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSPROCEDIMIENTOS';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSPROCEDIMIENTOS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSPROCEDIMIENTOS.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_procedimientos($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSPROCEDIMIENTOS>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Procedimientos($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Procedimientos($cmd,$idcentro,$grupoid){
	global $LITAMBITO_PROCEDIMIENTOS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idprocedimiento,descripcion  FROM procedimientos 
												WHERE idcentro=".$idcentro." AND grupoid=".$grupoid." 
												ORDER BY descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<PROCEDIMIENTO';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/procedimiento.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_PROCEDIMIENTOS.'-'.$rs->campos["idprocedimiento"];
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PROCEDIMIENTOS."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</PROCEDIMIENTO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_tareas($cmd,$idcentro,$grupoid){
	global $AMBITO_GRUPOSTAREAS;
	global $LITAMBITO_GRUPOSTAREAS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid 
				FROM grupos 
				WHERE grupoid=".$grupoid."
				AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSTAREAS."  
				ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSTAREAS';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSTAREAS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSTAREAS.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_tareas($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSTAREAS>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Tareas($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Tareas($cmd,$idcentro,$grupoid)
{
	global $LITAMBITO_TAREAS;
	
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT  tareas.idtarea,tareas.descripcion,tareas.ambito 
				FROM tareas 
				WHERE tareas.idcentro=".$idcentro." 
				AND grupoid=".$grupoid."
				ORDER by tareas.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<TAREA';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_TAREAS.'-'.$rs->campos["idtarea"];
			$cadenaXML.=' nodovalue="'.$rs->campos["ambito"].'"';
			
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TAREAS."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</TAREA>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Mens Contextuales
//________________________________________________________________________________________________________
function CreacontextualXMLComandos()
{
	global $LITAMBITO_COMANDOS;
	global $EJECUCION_COMANDO;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_COMANDOS.'"';
	$layerXML.=' maxanchu=165'; 
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="programacion('.$EJECUCION_COMANDO.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem="'.$TbMsg[21].'"';
	$layerXML.='></ITEM>';
	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLComando()
{
	global $LITAMBITO_COMANDOS;
	global $EJECUCION_COMANDO;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_COMANDOS.'"';
	$layerXML.=' maxanchu=120'; 
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="programacion('.$EJECUCION_COMANDO.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem="'.$TbMsg[21].'"';
	$layerXML.='></ITEM>';
	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLProcedimientos(){
	global $AMBITO_PROCEDIMIENTOS;
	global $AMBITO_GRUPOSPROCEDIMIENTOS;
	global $LITAMBITO_GRUPOSPROCEDIMIENTOS;
	global $LITAMBITO_PROCEDIMIENTOS;
	global $TbMsg;
	
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_PROCEDIMIENTOS.'"';
	$layerXML.=' maxanchu=195'; 
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPROCEDIMIENTOS.',' . "'".$LITAMBITO_GRUPOSPROCEDIMIENTOS."'" . ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';

	$wLeft=140;
	$wTop=115; 
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_procedimientos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_procedimientos.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PROCEDIMIENTOS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposProcedimientos(){
	global $AMBITO_PROCEDIMIENTOS;
	global $AMBITO_GRUPOSPROCEDIMIENTOS;
	global $LITAMBITO_GRUPOSPROCEDIMIENTOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSPROCEDIMIENTOS.'"';
	$layerXML.=' maxanchu=200';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSPROCEDIMIENTOS.',' ."'".$LITAMBITO_GRUPOSPROCEDIMIENTOS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[0];
	$layerXML.='></ITEM>';
	
	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_procedimientos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/procedimiento.gif"';
	$layerXML.=' textoitem='.$TbMsg[1];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_procedimientos.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_PROCEDIMIENTOS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[2];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
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
function CreacontextualXMLProcedimiento(){
	global $EJECUCION_PROCEDIMIENTO;
	global $AMBITO_PROCEDIMIENTOS;
	global $LITAMBITO_PROCEDIMIENTOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_PROCEDIMIENTOS.'"';
	$layerXML.=' maxanchu=170';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="informacion_acciones('.$AMBITO_PROCEDIMIENTOS.')"';
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="inclusion_acciones('.$AMBITO_PROCEDIMIENTOS.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem="'.$TbMsg[20].'"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_PROCEDIMIENTOS.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[7];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=140;
	$wTop=115;
	$wWidth=550;
	$wHeight=250;
	$wpages="../propiedades/propiedades_procedimientos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';	
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[9];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_accionmenu('.$EJECUCION_PROCEDIMIENTO.')"';
	$layerXML.=' imgitem="../images/iconos/menus.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';
	
	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLTareas(){
	global $AMBITO_TAREAS;
	global $AMBITO_GRUPOSTAREAS;
	global $LITAMBITO_GRUPOSTAREAS;
	global $LITAMBITO_TAREAS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_TAREAS.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSTAREAS.',' ."'".$LITAMBITO_GRUPOSTAREAS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=270;
	$wpages="../propiedades/propiedades_tareas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_tareas.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_TAREAS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposTareas(){
	global $AMBITO_TAREAS;
	global $AMBITO_GRUPOSTAREAS;
	global $LITAMBITO_GRUPOSTAREAS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSTAREAS.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSTAREAS.',' ."'".$LITAMBITO_GRUPOSTAREAS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[11];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=270;
	$wpages="../propiedades/propiedades_tareas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[12];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_tareas.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_TAREAS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[13];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>'; 

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/modificar.gif"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_grupos()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[14];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLTarea(){
	global $EJECUCION_TAREA;
	global $AMBITO_TAREAS;
	global $LITAMBITO_TAREAS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_TAREAS.'"';
	$layerXML.=' maxanchu=150';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ejecutar_tareas('.$EJECUCION_TAREA.')"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[15];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="programacion('.$EJECUCION_TAREA.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem='.$TbMsg[16];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="informacion_acciones('.$AMBITO_TAREAS.')"';
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.=' textoitem='.$TbMsg[17];
	$layerXML.='></ITEM>';
	
	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="inclusion_acciones('.$AMBITO_TAREAS.')"';
	$layerXML.=' imgitem="../images/iconos/acciones.gif"';
	$layerXML.=' textoitem="'.$TbMsg[20].'"';
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_TAREAS.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[18];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=270;
	$wpages="../propiedades/propiedades_tareas.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[19];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';    

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_accionmenu('.$EJECUCION_TAREA.')"';
	$layerXML.=' imgitem="../images/iconos/menus.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
