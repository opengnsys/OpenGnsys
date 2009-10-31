<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: informacion_imagenes.php
// Descripción : 
//		Muestra los perfiles que forman parte de una imagen  y los ordenadores que tienen instalada dicha imagen
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_imagenes_".$idioma.".php");
//________________________________________________________________________________________________________
$idimagen=0; 
$descripcionimagen=""; 
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"]; // Recoge parametros
if (isset($_GET["descripcionimagen"])) $descripcionimagen=$_GET["descripcionimagen"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idimagen); // Crea el arbol XML 

// Creación del árbol
$baseurlimg="../images/tsignos"; // Url de las im�enes de signo
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[3];  
$arbol=new ArbolVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/imagenes.gif"><BR><BR>
	<IMG src="../images/iconos/imagen.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:	<? echo $descripcionimagen?></SPAN></P>
	<?echo $arbol->CreaArbolVistaXml(); // Crea arbol de configuraciones
//________________________________________________________________________________________________________
	include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Devuelve una cadena con formato XML de toda la informaci� de las imagenes
	Parametros: 
		- cmd:Una comando ya operativo ( con conexiónabierta)  
		- idimagen: El identificador del perfil hardware
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idimagen){
	$cadenaXML=SubarbolXML_Imagenes($cmd,$idimagen);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Imagenes($cmd,$idimagen){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idimagen,descripcion,comentarios, idperfilsoft  FROM imagenes WHERE idimagen=".$idimagen;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.='>';
		if($rs->campos["comentarios"]>" "){
			$cadenaXML.='<PROPIEDAD';
			$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
			$cadenaXML.=' infonodo="[b]'.$TbMsg[9].' :[/b] '.$rs->campos["comentarios"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PROPIEDAD>';
		}
		$cadenaXML.=SubarbolXML_PerfilImagen($cmd,$rs->campos["idperfilsoft"]);
		$cadenaXML.=SubarbolXML_IncrementalImagen($cmd,$rs->campos["idimagen"]);
		$cadenaXML.=SubarbolXML_Ordenadores($cmd,$rs->campos["idimagen"],$rs->campos["idperfilsoft"]);
		$cadenaXML.='</IMAGEN>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_PerfilImagen($cmd,$idperfilsoft){
	global $TbMsg;
	$cadenaXML="";
	$gidperfilsoft=null;
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion as pdescripcion ,perfilessoft.comentarios, softwares.idsoftware,softwares.descripcion as hdescripcion,tiposoftwares.urlimg FROM perfilessoft  ";
	$cmd->texto.=" LEFT OUTER JOIN  perfilessoft_softwares  ON perfilessoft.idperfilsoft=perfilessoft_softwares.idperfilsoft";
	$cmd->texto.=" LEFT OUTER JOIN  softwares  ON softwares.idsoftware=perfilessoft_softwares.idsoftware";
	$cmd->texto.=" LEFT OUTER JOIN  tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware" ;
	$cmd->texto.=" WHERE perfilessoft.idperfilsoft=".$idperfilsoft;
	$cmd->texto.=" ORDER by perfilessoft.descripcion,tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->EOF) return($cadenaXML); 

	$cadenaXML.='<CARPETAPERFILES';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[6].'"';
	$cadenaXML.='>';

	while (!$rs->EOF){
		if ($gidperfilsoft!=$rs->campos["idperfilsoft"]){
			if ($gidperfilsoft){
				$cadenaXML.='</COMPONENTES>';
				$cadenaXML.='</PERFILESSOFTWARES>';
			}
			$gidperfilsoft=$rs->campos["idperfilsoft"];
			$cadenaXML.='<PERFILESSOFTWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["pdescripcion"].'"';
			$cadenaXML.='>';

			if($rs->campos["comentarios"]>" "){
				$cadenaXML.='<PROPIEDAD';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[9].' :[/b] '.$rs->campos["comentarios"].'"';
				$cadenaXML.='>';
				$cadenaXML.='</PROPIEDAD>';
			}

			$cadenaXML.='<COMPONENTES';
			$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
			$cadenaXML.=' infonodo="'.$TbMsg[4].'"';
			$cadenaXML.='>';
		}
		if ($rs->campos["idsoftware"]){
			$cadenaXML.='<COMPONENTE';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="'.$rs->campos["hdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</COMPONENTE>';
		}
		$rs->Siguiente();
	}
	$cadenaXML.='</COMPONENTES>';
	$cadenaXML.='</PERFILESSOFTWARES>';
	$cadenaXML.='</CARPETAPERFILES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_IncrementalImagen($cmd,$idimagen){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT   imagenes_softincremental.idsoftincremental FROM imagenes INNER JOIN imagenes_softincremental ON imagenes.idimagen =imagenes_softincremental.idimagen WHERE  (imagenes.idimagen = ".$idimagen.")";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if($rs->EOF) return($cadenaXML);

	$cadenaXML.='<CARPETAINCREMENTALES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[5].'"';
	$cadenaXML.='>';
	while (!$rs->EOF){
		$cadenaXML.=SubarbolXML_SoftwaresIncrementales($cmd,$rs->campos["idsoftincremental"]);
		$rs->Siguiente();
	}
	$cadenaXML.='</CARPETAINCREMENTALES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_SoftwaresIncrementales($cmd,$idsoftincremental){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softincrementales.idsoftincremental ,softincrementales.descripcion as idescripcion,softincrementales.comentarios , softwares.idsoftware,softwares.descripcion as sdescripcion,tiposoftwares.urlimg FROM softincrementales  ";
	$cmd->texto.=" LEFT OUTER JOIN  softincremental_softwares  ON softincrementales.idsoftincremental=softincremental_softwares.idsoftincremental";
	$cmd->texto.=" LEFT OUTER JOIN  softwares  ON softwares.idsoftware=softincremental_softwares.idsoftware";
	$cmd->texto.=" LEFT OUTER JOIN  tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware" ;
	$cmd->texto.=" WHERE softincrementales.idsoftincremental=".$idsoftincremental;
	$cmd->texto.=" ORDER by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaXML.='<SOFTWARESINCREMENTALES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/incremental.gif"';
	$cadenaXML.=' infonodo="'.$rs->campos["idescripcion"].'"';
	$cadenaXML.='>';

	if($rs->campos["comentarios"]>" "){
		$cadenaXML.='<PROPIEDAD';
		$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
		$cadenaXML.=' infonodo="[b]'.$TbMsg[9].' :[/b] '.$rs->campos["comentarios"].'"';
		$cadenaXML.='>';
		$cadenaXML.='</PROPIEDAD>';
	}

	$swcompo=false;
	while (!$rs->EOF){
		if ($rs->campos["idsoftware"]){
			if (!$swcompo) {
				$cadenaXML.='<COMPONENTES';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[4].'"';
				$cadenaXML.='>';
				$swcompo=true;
			}	
			$cadenaXML.='<SOFTWAREINCREMENTAL';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="'.$rs->campos["sdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</SOFTWAREINCREMENTAL>';
		}
		$rs->Siguiente();
	}
	if ($swcompo) {
		$cadenaXML.='</COMPONENTES>';
	}
	$cadenaXML.='</SOFTWARESINCREMENTALES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_Ordenadores($cmd,$idimagen,$idperfilsoft){
	global $TbMsg;
	$cadenaXML="";
	$gidaula=null;
	$rs=new Recordset; 
	$cmd->texto="SELECT aulas.idaula,aulas.nombreaula,ordenadores. idordenador,ordenadores.nombreordenador,ordenador_imagen.particion,ordenadores. idperfilhard FROM ordenadores";
	$cmd->texto.=" INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula";
	$cmd->texto.=" INNER JOIN ordenador_imagen ON  ordenador_imagen.idordenador=ordenadores.idordenador";
	$cmd->texto.=" WHERE ordenador_imagen.idimagen=".$idimagen." order by aulas.idaula";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0){
		$cadenaXML.='<ORDENADORES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[7].'"';
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
		$cadenaXML.=' infonodo="'.$rs->campos["nombreordenador"].' &nbsp;&nbsp;-&nbsp;&nbsp ('.$TbMsg[8].':'.$rs->campos["particion"]. ', PS'.$idperfilsoft.'_PH'.$rs->campos["idperfilhard"].'.psf)"' ;
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
?>