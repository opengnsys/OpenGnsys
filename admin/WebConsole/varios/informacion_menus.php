<?
// ******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: A�o 2003-2004
// Fecha �ltima modificaci�n: Febrero-2005
// Nombre del fichero: informacion_menus.php
// Descripciónn : 
//		Muestra los items que forman parte de un menu y sus valores
// *****************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/informacion_menus_".$idioma.".php");
//________________________________________________________________________________________________________
$idmenu=0; 
$descripcionmenu=""; 
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"]; // Recoge parametros
if (isset($_GET["descripcionmenu"])) $descripcionmenu=$_GET["descripcionmenu"]; // Recoge parametros

$contitempub=0; // Contador de itemsp�blicos para dimensinar ventana
$contitempri=0; // Contador de itemsp�blicos para dimensinar ventana

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idmenu); // Crea el arbol XML 

// Creaciónn del �rbol
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[3];  
$arbol=new ArbolVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci�n web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/informacion_menus.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/informacion_menus_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY OnContextMenu="return false">
	<FORM name=fdatos>
		<input type=hidden value="<? echo $idmenu?>" id=idmenu>	 
		<input type=hidden value="<? echo $contitempub?>" id=contitempub>	 
		<input type=hidden value="<? echo $contitempri?>" id=contitempri>	 
	</FORM>
	<p align=center class=cabeceras><?echo $TbMsg[0]?><br>
	<span align=center class=subcabeceras><?echo $TbMsg[1]?></span>&nbsp;<img src="../images/iconos/menus.gif"><br><br>
	<img src="../images/iconos/menu.gif"><span class=presentaciones>&nbsp;&nbsp;<u><?echo $TbMsg[2]?></u>:	<? echo $descripcionmenu?></span></p>
	<?
	echo $arbol->CreaArbolVistaXML(); // Crea arbol de configuraciones

	$flotante=new MenuContextual(); // Crea objeto MenuContextual
	$XMLcontextual=ContextualXMLItems(); // Crea contextual de los items 
	echo $flotante->CreaMenuContextual($XMLcontextual); 
	$XMLcontextual=ContextualXMLItem(); // Crea contextual de un item
	echo $flotante->CreaMenuContextual($XMLcontextual); 
	?>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function CreaArbol($cmd,$idmenu){
	$cadenaXML=SubarbolXML_menuswares($cmd,$idmenu);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_menuswares($cmd,$idmenu){
	global  $TbMsg;
	global  $ITEM_PUBLICO;
	global  $ITEM_PRIVADO;
	global  $idcentro;
	global  $EJECUCION_PROCEDIMIENTO;
	global  $EJECUCION_TAREA;
	global  $EJECUCION_TRABAJO;
	global  $contitempub;
	global  $contitempri;

	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT   menus.*, acciones_menus.*,iconos.urlicono as urlimg FROM  menus LEFT OUTER JOIN acciones_menus ON acciones_menus.idmenu = menus.idmenu";
	$cmd->texto.=" LEFT OUTER JOIN iconos ON acciones_menus.idurlimg =iconos.idicono";
	$cmd->texto.=" WHERE menus.idcentro=".$idcentro." AND menus.idmenu=".$idmenu;
	$cmd->texto.=" ORDER BY acciones_menus.tipoitem,acciones_menus.orden";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$tbmodalidad[1]=$TbMsg[18];
	$tbmodalidad[2]=$TbMsg[19];
	$cadenaXML.='<MENUS';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/menu.gif"';
	$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
	$cadenaXML.=' nodoid=menu-'.$rs->campos["idmenu"];
	$cadenaXML.='>';

	$cadenaXML.='<PROPIEDADES';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo='.$TbMsg[5].'';
	$cadenaXML.=' nodoid=propiedades';
	$cadenaXML.='>';

	$contprop=0;
	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[7].':[/b] '.$rs->campos["titulo"].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='<PROPIEDADESPUB';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[8].'"';
	$cadenaXML.=' nodoid=propiedadespub';
	$cadenaXML.='>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[9].' :[/b] '.$rs->campos["coorx"].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[10].' :[/b] '.$rs->campos["coory"].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[11].' :[/b] '.$tbmodalidad[$rs->campos["modalidad"]].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='</PROPIEDADESPUB>';

	$cadenaXML.='<PROPIEDADESPRI';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[12].'"';
	$cadenaXML.=' nodoid=propiedadespri';
	$cadenaXML.='>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[9].' :[/b] '.$rs->campos["scoorx"].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
	$cadenaXML.=' infonodo="[b]'.$TbMsg[10].' :[/b] '.$rs->campos["coory"].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='<PROPMENU';
	$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';

	$cadenaXML.=' infonodo="[b]'.$TbMsg[11].' :[/b] '.$tbmodalidad[$rs->campos["smodalidad"]].'"';
	$cadenaXML.=' nodoid=propiedad-'.$contprop++;
	$cadenaXML.='>';
	$cadenaXML.='</PROPMENU>';

	$cadenaXML.='</PROPIEDADESPRI>';
	$cadenaXML.='</PROPIEDADES>';

	$swpub=false;
	$swpriv=false;
	
	while (!$rs->EOF){
		if ($rs->campos["tipoitem"]==$ITEM_PUBLICO){
			$contitempub++;
			if (!$swpub) {
				$cadenaXML.='<ITEMSPUBLICOS';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[13].'"';
				$cadenaXML.=' nodoid="itemspublicos-'.$ITEM_PUBLICO.'"';
				$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_items'" .')"';
				$cadenaXML.='>';
				$swpub=true;
			}	
		}
		if ($rs->campos["tipoitem"]==$ITEM_PRIVADO){
			$contitempri++;
			if ($swpub) {
				$cadenaXML.='</ITEMSPUBLICOS>';
				$swpub=false;
			}
			if (!$swpriv) {
				$cadenaXML.='<ITEMSPRIVADOS';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[14].'"';
				$cadenaXML.=' nodoid="itemsprivados-'.$ITEM_PRIVADO.'"';
				$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_items'" .')"';
				$cadenaXML.='>';
				$swpriv=true;
			}	
		}
		switch($rs->campos["tipoaccion"]){
				case $EJECUCION_PROCEDIMIENTO :
					$cmd->texto='SELECT  procedimientos.descripcion  FROM  procedimientos  WHERE procedimientos.idprocedimiento='.$rs->campos["idtipoaccion"];
					$urlimg="procedimiento.gif";
					break;
				case $EJECUCION_TAREA :
					$cmd->texto='SELECT  tareas.idtarea, tareas.descripcion FROM tareas WHERE tareas.idtarea='.$rs->campos["idtipoaccion"];
					$urlimg="tareas.gif";
					break;
				case $EJECUCION_TRABAJO :
					$cmd->texto='SELECT  trabajos.idtrabajo, trabajos.descripcion   FROM  trabajos  WHERE trabajos.idtrabajo='.$rs->campos["idtipoaccion"];
					$urlimg="trabajos.gif";
					break;
		}
		if(!empty($rs->campos["idtipoaccion"]))
				$cadenaXML.= SubarbolXML_itemsmenus($cmd,$urlimg,$rs->campos);
		$rs->Siguiente();
	}
	if ($swpub) 
				$cadenaXML.='</ITEMSPUBLICOS>';
	if ($swpriv) 
				$cadenaXML.='</ITEMSPRIVADOS>';
	$cadenaXML.='</MENUS>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_itemsmenus($cmd,$urlimg,$campos){
	global  $TbMsg;
	global  $ITEM_PUBLICO;
	global $ITEM_PRIVADO;
	global $idcentro;

	$cadenaXML="";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
				$cadenaXML.='<ITEM';
				$cadenaXML.=' imagenodo="../images/iconos/'.$urlimg.'"';
				$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
				$cadenaXML.=' nodoid=item-'.$campos["idaccionmenu"];
				$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_item'" .')"';
				$cadenaXML.='>';

				$contprop=0;

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[20].':[/b] '.$campos["idaccionmenu"].'"';
				$cadenaXML.=' nodoid=propiedad-'.$contprop++;
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[15].':[/b] '.$campos["orden"].'"';
				$cadenaXML.=' nodoid=propiedad-'.$contprop++;
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[16].':[/b] '.$campos["descripitem"].'"';
				$cadenaXML.=' nodoid=propiedad-'.$contprop++;
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				if(!empty($campos["urlimg"])) {
					$cadenaXML.='<PROPMENU';
					$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
					$cadenaXML.=' infonodo="[b]'.$TbMsg[17].': [/b]'.$campos["urlimg"].'"';
					$cadenaXML.=' nodoid=propiedad-'.$contprop++;
					$cadenaXML.='>';
					$cadenaXML.='</PROPMENU>';
				}
		$cadenaXML.='</ITEM>';
		$rs->Siguiente();
	}
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function ContextualXMLItems(){
	global $TbMsg;
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_items"';
	$layerXML.=' maxanchu=160';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_items()"';
	$layerXML.=' textoitem="'.$TbMsg[4].'"';
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
//---------------------------------------------------------------------------------------------------------------------------------------------
function ContextualXMLItem(){
	global  $TbMsg;
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_item"';
	$layerXML.=' maxanchu=110';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="modificar_item()"';
	$layerXML.=' textoitem="'.$TbMsg[5].'"';
	$layerXML.=' imgitem="../images/iconos/propiedades.gif"';
	$layerXML.='></ITEM>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="eliminar_item()"';
	$layerXML.=' imgitem="../images/iconos/eliminar.gif"';
	$layerXML.=' textoitem="'.$TbMsg[6].' "';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>