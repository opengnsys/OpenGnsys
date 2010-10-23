<?
// ******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: informacion_menus.php
// Descripción : 
//		Muestra los items que forman parte de un menu y sus valores
// *****************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
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
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
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
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/informacion_menus.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/informacion_menus_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
	<FORM name=fdatos>
		<input type=hidden value="<? echo $idmenu?>" id=idmenu>	 
		<input type=hidden value="<? echo $contitempub?>" id=contitempub>	 
		<input type=hidden value="<? echo $contitempri?>" id=contitempri>	 
	</FORM>
	<p align=center class=cabeceras><?echo $TbMsg[0]?><br>
	<span align=center class=subcabeceras><?echo $TbMsg[1]?></span>&nbsp;<img src="../images/iconos/menus.gif"><br>
	<img src="../images/iconos/menu.gif"><span class=presentaciones>&nbsp;&nbsp;<u><?echo $TbMsg[2]?></u>:	<? echo $descripcionmenu?></span></p>
	<?
	echo $arbol->CreaArbolVistaXML(); // Crea arbol de configuraciones
	?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function CreaArbol($cmd,$idmenu){
	$cadenaXML=SubarbolXML_Menus($cmd,$idmenu);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Menus($cmd,$idmenu)
{
	global $TbMsg;

	$cadenaXML="";
	$cmd->texto="SELECT * FROM menus WHERE idmenu=".$idmenu;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<MENU';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/menu.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.='>';
		$cadenaXML.=SubarbolXML_Ordenadores($cmd,$idmenu);
		$cadenaXML.=SubarbolXML_Items($cmd,$idmenu);
		$cadenaXML.='</MENU>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Ordenadores($cmd,$idmenu)
{
	global $TbMsg;

	$cadenaXML="";
	$gidaula=null;
	$cmd->texto="SELECT aulas.idaula,aulas.nombreaula,ordenadores. idordenador,ordenadores.nombreordenador
								FROM ordenadores
 								INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula
 								WHERE ordenadores.idmenu=".$idmenu." ORDER BY aulas.idaula,ordenadores.nombreordenador";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0){
		$cadenaXML.='<ORDENADORES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[21].'"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
		if ($gidaula!=$rs->campos["idaula"]){
			if ($gidaula)
				$cadenaXML.='</AULA>';
			$cadenaXML.='<AULA ';
			// Atributos		

			$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["nombreaula"].'"';
			$cadenaXML.='>';
			$gidaula=$rs->campos["idaula"];
		}
		$cadenaXML.='<ORDENADOR';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/ordenador.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreordenador"].'"' ;
		$cadenaXML.='></ORDENADOR>';
		$rs->Siguiente();
	}
	if ($gidaula)
		$cadenaXML.='</AULA>';
	if ($rs->numeroderegistros>0)
			$cadenaXML.='</ORDENADORES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Items($cmd,$idmenu){
	global  $TbMsg;
	global  $ITEM_PUBLICO;
	global  $ITEM_PRIVADO;
	global  $idcentro;
	global  $EJECUCION_PROCEDIMIENTO;
	global  $EJECUCION_TAREA;
	global  $contitempub;
	global  $contitempri;

	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT acciones_menus.*,iconos.urlicono as urlimg
								FROM  acciones_menus 
								LEFT OUTER JOIN iconos ON acciones_menus.idurlimg =iconos.idicono
 								WHERE acciones_menus.idmenu=".$idmenu."
  							ORDER BY acciones_menus.tipoitem,acciones_menus.orden";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$tbmodalidad[1]=$TbMsg[18];
	$tbmodalidad[2]=$TbMsg[19];

	$swpub=false;
	$swpriv=false;
	
	$cadenaXML.='<ITEMS';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[22].'"';
	$cadenaXML.='>';	
	
	while (!$rs->EOF){
		if ($rs->campos["tipoitem"]==$ITEM_PUBLICO){
			$contitempub++;
			if (!$swpub) {
				$cadenaXML.='<ITEMSPUBLICOS';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[13].'"';
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
				$cadenaXML.='>';
				$swpriv=true;
			}	
		}
		switch($rs->campos["tipoaccion"]){
				case $EJECUCION_PROCEDIMIENTO :
					$cmd->texto='SELECT  procedimientos.descripcion  FROM  procedimientos
						  WHERE procedimientos.idprocedimiento='.$rs->campos["idtipoaccion"];
					$urlimg="procedimiento.gif";
					break;
				case $EJECUCION_TAREA :
					$cmd->texto='SELECT  tareas.idtarea, tareas.descripcion FROM tareas
							 WHERE tareas.idtarea='.$rs->campos["idtipoaccion"];
					$urlimg="tareas.gif";
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
	$cadenaXML.='</ITEMS>';				
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
				$cadenaXML.=' infonodo="'.$campos["descripitem"].'"';
				$cadenaXML.='>';

				$contprop=0;

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[20].':[/b] '.$campos["idaccionmenu"].'"';
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[15].':[/b] '.$campos["orden"].'"';
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				$cadenaXML.='<PROPMENU';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[16].':[/b] '.$rs->campos["descripcion"].'"';
				$cadenaXML.='>';
				$cadenaXML.='</PROPMENU>';

				if(!empty($campos["urlimg"])) {
					$cadenaXML.='<PROPMENU';
					$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
					$cadenaXML.=' infonodo="[b]'.$TbMsg[17].': [/b]'.$campos["urlimg"].'"';
					$cadenaXML.='>';
					$cadenaXML.='</PROPMENU>';
				}
		$cadenaXML.='</ITEM>';
		$rs->Siguiente();
	}
	return($cadenaXML);
}

?>
