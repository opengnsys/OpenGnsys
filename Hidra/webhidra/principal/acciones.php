<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Febrero-2005
// Nombre del fichero: acciones.php
// Descripci� :
//		Administra procedimientos,tareas y trabajos de un determinado Centro
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
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexi� con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idcentro); // Crea el arbol XML con todos los datos de las acciones registradas en el Centro
// Creaci� del �bol
$baseurlimg="../images/signos"; // Url de las im�enes de signo
$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del �bol
$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault,1,0,5);
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
	<META HTTP-EQUIV="Content-Type"  CONTENT="text/html;charset=ISO-8859-1"> 
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/acciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/acciones_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
<?
//________________________________________________________________________________________________________
echo $arbol->CreaArbolVistaXML();	 // Crea �bol (HTML) a partir del XML
$flotante=new MenuContextual();			 // Crea objeto MenuContextual

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

// Crea contextual de los trabajos
$XMLcontextual=CreacontextualXMLTrabajos(); 
echo $flotante->CreaMenuContextual($XMLcontextual);  
$XMLcontextual=ContextualXMLGruposTrabajos();  // Grupo de Trabajos
echo $flotante->CreaMenuContextual($XMLcontextual); 
$XMLcontextual=CreacontextualXMLTrabajo(); // Trabajos
echo $flotante->CreaMenuContextual($XMLcontextual); 
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
//	Devuelve una cadena con formato XML con toda la informaci� de las acciones registradas en un Centro concreto
//	Parametros: 
//		- cmd:Una comando ya operativo ( con conexi� abierta)  
//		- idcentro: El identificador del centro
//________________________________________________________________________________________________________
function CreaArbol($cmd,$idcentro){
	global $TbMsg;
	global $LITAMBITO_PROCEDIMIENTOS;
	global $LITAMBITO_TAREAS;
	global $LITAMBITO_TRABAJOS;

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

	$cadenaXML.='<TRABAJOS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[32].'"';
	$cadenaXML.=' nodoid=Raiz'.$LITAMBITO_TRABAJOS;
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Raiz".$LITAMBITO_TRABAJOS."'".')"';
	$cadenaXML.='>';
	$cadenaXML.=SubarbolXML_grupos_trabajos($cmd,$idcentro,0);
	$cadenaXML.='</TRABAJOS>';
	$cadenaXML.='</ACCIONES>';
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Comandos($cmd){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idcomando,descripcion,urlimg  FROM comandos order by descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<COMANDO';
		// Atributos
		//if (!empty($rs->campos["urlimg"]))
		//	$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
		//else
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
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSPROCEDIMIENTOS." ORDER BY nombregrupo";
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
	$cmd->texto="SELECT idprocedimiento,descripcion  FROM procedimientos WHERE idcentro=".$idcentro." AND grupoid=".$grupoid." ORDER BY descripcion";
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
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSTAREAS."  ORDER BY nombregrupo";
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
function SubarbolXML_Tareas($cmd,$idcentro,$grupoid){
	global $LITAMBITO_TAREAS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT  tareas.idtarea,tareas.descripcion FROM tareas WHERE tareas.idcentro=".$idcentro." AND grupoid=".$grupoid;
	$cmd->texto.=" ORDER by tareas.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<TAREA';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_TAREAS.'-'.$rs->campos["idtarea"];
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TAREAS."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</TAREA>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_grupos_trabajos($cmd,$idcentro,$grupoid){
	global $AMBITO_GRUPOSTRABAJOS;
	global $LITAMBITO_GRUPOSTRABAJOS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idgrupo,nombregrupo,grupoid FROM grupos WHERE grupoid=".$grupoid." AND idcentro=".$idcentro." AND tipo=".$AMBITO_GRUPOSTRABAJOS."  ORDER BY nombregrupo";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<GRUPOSTRABAJOS';
		// Atributos
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,'. " 'flo_".$LITAMBITO_GRUPOSTRABAJOS."'" .');"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombregrupo"].'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSTRABAJOS.'-'.$rs->campos["idgrupo"];
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_grupos_trabajos($cmd,$idcentro,$rs->campos["idgrupo"]);
		$cadenaXML.='</GRUPOSTRABAJOS>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$cadenaXML.=SubarbolXML_Trabajos($cmd,$idcentro,$grupoid);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Trabajos($cmd,$idcentro,$grupoid){
	global $LITAMBITO_TRABAJOS;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT trabajos.idtrabajo,trabajos.descripcion FROM trabajos WHERE trabajos.idcentro=".$idcentro." AND trabajos.grupoid=".$grupoid;;
	$cmd->texto.=" ORDER by trabajos.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<TRABAJO';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/trabajos.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.=' nodoid='.$LITAMBITO_TRABAJOS.'-'.$rs->campos["idtrabajo"];
			$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TRABAJOS."'" .')"';
			$cadenaXML.='>';
			$cadenaXML.='</TRABAJO>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
//
//	Mens Contextuales
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
	$layerXML.=' alpulsar="gestionar_procedimientocomando()"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_comandosprocedimientos()"';
	$layerXML.=' textoitem='.$TbMsg[6];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
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
	$layerXML.=' alpulsar="ejecutar_tareas()"';
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
	$layerXML.=' alpulsar="gestionar_tareacomando()"';
	$layerXML.=' imgitem="../images/iconos/comandos.gif"';
	$layerXML.=' textoitem='.$TbMsg[5];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_comandostareas()"';
	$layerXML.=' textoitem='.$TbMsg[17];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
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
//________________________________________________________________________________________________________
function CreacontextualXMLTrabajos(){
	global $AMBITO_TRABAJOS;
	global $AMBITO_GRUPOSTRABAJOS;
	global $LITAMBITO_GRUPOSTRABAJOS;
	global $LITAMBITO_TRABAJOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Raiz'.$LITAMBITO_TRABAJOS.'"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSTRABAJOS.',' ."'".$LITAMBITO_GRUPOSTRABAJOS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[20];
	$layerXML.='></ITEM>';

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=250;
	$wpages="../propiedades/propiedades_trabajos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/trabajos.gif"';
	$layerXML.=' textoitem='.$TbMsg[21];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_trabajos.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_TRABAJOS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[22];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function ContextualXMLGruposTrabajos(){
	global $AMBITO_TRABAJOS;
	global $AMBITO_GRUPOSTRABAJOS;
	global $LITAMBITO_GRUPOSTRABAJOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_GRUPOSTRABAJOS.'"';
	$layerXML.=' maxanchu=170';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_grupos('.$AMBITO_GRUPOSTRABAJOS.',' ."'".$LITAMBITO_GRUPOSTRABAJOS."'". ')"';
	$layerXML.=' imgitem="../images/iconos/carpeta.gif"';
	$layerXML.=' textoitem='.$TbMsg[20];
	$layerXML.='></ITEM>';
	
	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=250;
	$wpages="../propiedades/propiedades_trabajos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/trabajos.gif"';
	$layerXML.=' textoitem='.$TbMsg[21];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';

	$wParam="../gestores/gestor_trabajos.php";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="colocar('."'".$wParam."'".','.$AMBITO_TRABAJOS.')"';
	$layerXML.=' imgitem="../images/iconos/colocar.gif"';
	$layerXML.=' textoitem='.$TbMsg[22];
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
	$layerXML.=' textoitem='.$TbMsg[23];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//________________________________________________________________________________________________________
function CreacontextualXMLTrabajo(){
	global $EJECUCION_TRABAJO;
	global $AMBITO_TRABAJOS;
	global $LITAMBITO_TRABAJOS;
	global $TbMsg;

	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_'.$LITAMBITO_TRABAJOS.'"';
	$layerXML.=' maxanchu=140';
	$layerXML.=' swimg=1';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ejecutar_trabajos()"';
	$layerXML.=' imgitem="../images/iconos/trabajos.gif"';
	$layerXML.=' textoitem='.$TbMsg[24];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="programacion('.$EJECUCION_TRABAJO.')"';
	$layerXML.=' imgitem="../images/iconos/reloj.gif"';
	$layerXML.=' textoitem='.$TbMsg[16];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';  

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_trabajotarea()"';
	$layerXML.=' imgitem="../images/iconos/tareas.gif"';
	$layerXML.=' textoitem='.$TbMsg[25];
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="ver_tareastrabajos()"';
	$layerXML.=' textoitem='.$TbMsg[26];
	$layerXML.=' imgitem="../images/iconos/informacion.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';   

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="mover('.$AMBITO_TRABAJOS.')"';
	$layerXML.=' imgitem="../images/iconos/mover.gif"';
	$layerXML.=' textoitem='.$TbMsg[27];
	$layerXML.='></ITEM>';

	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';    

	$wLeft=170;
	$wTop=150;
	$wWidth=480;
	$wHeight=250;
	$wpages="../propiedades/propiedades_trabajos.php";
	$wParam=$wLeft .",".$wTop.",".$wWidth.",".$wHeight.",'". $wpages."'";

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar('.$wParam.')"';
	$layerXML.=' textoitem='.$TbMsg[8];
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar('.$wParam.')"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem='.$TbMsg[28];
	$layerXML.='></ITEM>';
	
	$layerXML.='<SEPARADOR>';
	$layerXML.='</SEPARADOR>';    

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_accionmenu('.$EJECUCION_TRABAJO.')"';
	$layerXML.=' imgitem="../images/iconos/menus.gif"';
	$layerXML.=' textoitem='.$TbMsg[10];
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>
