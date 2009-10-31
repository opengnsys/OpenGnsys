<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: informacion_perfilessoft.php
// Descripción : 
//		Muestra los componentes software que forman parte de un perfil software y los perfiles softwares disponibles
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_perfilessoft_".$idioma.".php");
//________________________________________________________________________________________________________
$idperfil=0; 
$descripcionperfil=""; 
if (isset($_GET["idperfil"])) $idperfil=$_GET["idperfil"]; // Recoge parametros
if (isset($_GET["descripcionperfil"])) $descripcionperfil=$_GET["descripcionperfil"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idperfil); // Crea el arbol XML 

// Creación del árbol
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[3];  
$arbol=new ArbolVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
//________________________________________________________________________________________________________
?>
<HTML>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"><BR><BR>
	<IMG src="../images/iconos/perfilsoftware.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:	<? echo $descripcionperfil?></SPAN></P>
	<?echo $arbol->CreaArbolVistaXml(); // Crea arbol de configuraciones?>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Devuelve una cadena con formato XML de toda la informaci� de los perfiles software
	softwares
	Parametros: 
		- cmd:Una comando ya operativo ( con conexiónabierta)  
		- idperfil: El identificador del perfil software
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idperfil){
	$cadenaXML=SubarbolXML_PerfilesSoftwares($cmd,$idperfil);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesSoftwares($cmd,$idperfilsoft){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion as pdescripcion, perfilessoft.comentarios,softwares.idsoftware,softwares.descripcion as hdescripcion,tiposoftwares.urlimg FROM perfilessoft  ";
	$cmd->texto.=" LEFT OUTER JOIN  perfilessoft_softwares  ON perfilessoft.idperfilsoft=perfilessoft_softwares.idperfilsoft";
	$cmd->texto.=" LEFT OUTER JOIN  softwares  ON softwares.idsoftware=perfilessoft_softwares.idsoftware";
	$cmd->texto.=" LEFT OUTER JOIN  tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware" ;
	$cmd->texto.=" WHERE perfilessoft.idperfilsoft=".$idperfilsoft;
	$cmd->texto.=" ORDER by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaXML.='<PERFILESSOFTWARES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
	$cadenaXML.=' infonodo="'.$rs->campos["pdescripcion"].'"';
	$cadenaXML.='>';
	if($rs->campos["comentarios"]>" "){
		$cadenaXML.='<PROPIEDAD';
		$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
		$cadenaXML.=' infonodo="[b]'.$TbMsg[8].' :[/b] '.$rs->campos["comentarios"].'"';
		$cadenaXML.='>';
		$cadenaXML.='</PROPIEDAD>';
	}
	$swcompo=false;
	while (!$rs->EOF){
		if ($rs->campos["idsoftware"]){
			if (!$swcompo) {
				$cadenaXML.='<COMPONENTES';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[6].'"';
				$cadenaXML.='>';
				$swcompo=true;
			}	
			$cadenaXML.='<PERFILSOFTWARE';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="'.$rs->campos["hdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PERFILSOFTWARE>';
		}
		$rs->Siguiente();
	}
	if ($swcompo) {
		$cadenaXML.='</COMPONENTES>';
	}
	$cadenaXML.=SubarbolXML_PerfilesHardwaresSoportados($cmd,$idperfilsoft);
	$cadenaXML.='</PERFILESSOFTWARES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesHardwaresSoportados($cmd,$idperfilsoft){
	global $TbMsg;
	$cadenaXML="";
	$gidperfilhard=null;
	$rs=new Recordset; 
	$cmd->texto="SELECT perfileshard.idperfilhard ,perfileshard.descripcion,perfileshard.comentarios FROM perfileshard  ";
	$cmd->texto.=" LEFT OUTER JOIN perfileshard_perfilessoft  ON perfileshard_perfilessoft.idperfilhard=perfileshard.idperfilhard" ;
	$cmd->texto.=" WHERE perfileshard_perfilessoft.idperfilsoft=".$idperfilsoft;
	$cmd->texto.=" ORDER by perfileshard.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESPERFILES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[4].'"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
		if ($gidperfilhard!=$rs->campos["idperfilhard"]){
			if ($gidperfilhard){
				$cadenaXML.='</PERFILESHARDWARES>';
			}
			$gidperfilhard=$rs->campos["idperfilhard"];
			$cadenaXML.='<PERFILESHARDWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilhardware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.='>';
			if($rs->campos["comentarios"]>" "){
				$cadenaXML.='<PROPIEDAD';
				$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
				$cadenaXML.=' infonodo="[b]'.$TbMsg[8].' :[/b] '.$rs->campos["comentarios"].'"';
				$cadenaXML.='>';
				$cadenaXML.='</PROPIEDAD>';
			}
			$cadenaXML.=SubarbolXML_ComponentesHardware($cmd,$rs->campos["idperfilhard"]);
			$cadenaXML.=SubarbolXML_IncrementalesDisponibles($cmd,$idperfilsoft,$rs->campos["idperfilhard"]);
		}
		$rs->Siguiente();
	}
	if ($gidperfilhard){
		$cadenaXML.='</PERFILESHARDWARES>';
		$cadenaXML.='</DISPONIBLESPERFILES>';
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ComponentesHardware($cmd,$idperfilhard){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT hardwares.descripcion,tipohardwares.urlimg FROM perfileshard_hardwares  ";
	$cmd->texto.="INNER JOIN hardwares  ON hardwares.idhardware=perfileshard_hardwares.idhardware";
	$cmd->texto.=" INNER JOIN tipohardwares  ON hardwares.idtipohardware=tipohardwares.idtipohardware" ;
	$cmd->texto.=" WHERE perfileshard_hardwares.idperfilhard=".$idperfilhard;
	$cmd->texto.=" ORDER by tipohardwares.idtipohardware,hardwares.descripcion";
	$rs->Comando=&$cmd; 

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaXML.='<COMPONENTES';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[5].'"';
	$cadenaXML.='>';
	while (!$rs->EOF){
			$cadenaXML.='<COMPONENTEHARD';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</COMPONENTEHARD>';
			$rs->Siguiente();
	}	
	$cadenaXML.='</COMPONENTES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_IncrementalesDisponibles($cmd,$idperfilsoft,$idperfilhard){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT   softincrementales.descripcion,  softincrementales.idsoftincremental,  perfileshard_perfilessoft.idperfilhard,      perfileshard_perfilessoft.idperfilsoft FROM   perfileshard_perfilessoft INNER JOIN     phard_psoft_softincremental ON  perfileshard_perfilessoft.idphardidpsoft =  phard_psoft_softincremental.idphardidpsoft INNER JOIN      softincrementales ON  phard_psoft_softincremental.idsoftincremental =  softincrementales.idsoftincremental WHERE  ( perfileshard_perfilessoft.idperfilhard = ".$idperfilhard.") AND ( perfileshard_perfilessoft.idperfilsoft = ".$idperfilsoft.")";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if($rs->EOF) return($cadenaXML);
	$cadenaXML.='<CARPETAINCREMENTALES';
	// Atributos
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="'.$TbMsg[7].'"';
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
	$cmd->texto="SELECT softincrementales.idsoftincremental ,softincrementales.descripcion as idescripcion,softincrementales.comentarios, softwares.idsoftware,softwares.descripcion as sdescripcion,tiposoftwares.urlimg FROM softincrementales  ";
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
		$cadenaXML.=' infonodo="[b]'.$TbMsg[8].' :[/b] '.$rs->campos["comentarios"].'"';
		$cadenaXML.='>';
		$cadenaXML.='</PROPIEDAD>';
	}
	$swcompo=false;
	while (!$rs->EOF){
		if ($rs->campos["idsoftware"]){
			if (!$swcompo) {
				$cadenaXML.='<COMPONENTES';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[6].'"';
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
?>