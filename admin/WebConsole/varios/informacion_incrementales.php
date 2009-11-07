<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla

// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: informacion_incrementales.php
// Descripción : 
//		Muestra los componentes software  de un software incremental y los perfiles softwares y hardwares  disponibles
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_incrementales_".$idioma.".php");
//________________________________________________________________________________________________________
$idsoftincremental=0; 
$descripcionincremental=""; 
if (isset($_GET["idsoftincremental"])) $idsoftincremental=$_GET["idsoftincremental"]; // Recoge parametros
if (isset($_GET["descripcionincremental"])) $descripcionincremental=$_GET["descripcionincremental"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idsoftincremental); // Crea el arbol XML 

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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXml.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"><BR><BR>
	<IMG src="../images/iconos/incremental.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:	<? echo $descripcionincremental?></SPAN></P>
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
	Devuelve una cadena con formato XML de toda la información de los perfiles software
	softwares
	Parametros: 
		- cmd:Una comando ya operativo ( con conexión abierta)  
		- idsoftincremental: El identificador del perfil software
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idsoftincremental){
	$cadenaXML=SubarbolXML_SoftwaresIncrementales($cmd,$idsoftincremental);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_SoftwaresIncrementales($cmd,$idsoftincremental){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softincrementales.idsoftincremental ,softincrementales.descripcion as idescripcion, softwares.idsoftware,softwares.descripcion as sdescripcion,tiposoftwares.urlimg FROM softincrementales  ";
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
	$cadenaXML.=SubarbolXML_PerfilesDisponibles($cmd,$idsoftincremental);
	$cadenaXML.='</SOFTWARESINCREMENTALES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesDisponibles($cmd,$idsoftincremental){
	global $TbMsg;
	$cadenaXML="";
	$gidperfilsoft=null;
	$rs=new Recordset; 
	$cmd->texto="SELECT  perfilessoft.descripcion AS sdescripcion, perfileshard.descripcion AS hdescripcion, perfileshard.idperfilhard, perfilessoft.idperfilsoft FROM  softincrementales INNER JOIN  phard_psoft_softincremental ON softincrementales.idsoftincremental = phard_psoft_softincremental.idsoftincremental INNER JOIN  perfileshard_perfilessoft ON phard_psoft_softincremental.idphardidpsoft = perfileshard_perfilessoft.idphardidpsoft INNER JOIN  perfileshard ON perfileshard_perfilessoft.idperfilhard = perfileshard.idperfilhard INNER JOIN  perfilessoft ON perfileshard_perfilessoft.idperfilsoft = perfilessoft.idperfilsoft   WHERE softincrementales.idsoftincremental=".$idsoftincremental." GROUP BY softincrementales.descripcion, perfilessoft.descripcion, perfileshard.descripcion, perfileshard.idperfilhard, perfilessoft.idperfilsoft ORDER BY perfilessoft.idperfilsoft, perfileshard.idperfilhard";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESPERFILES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[5].'"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
		if ($gidperfilsoft!=$rs->campos["idperfilsoft"]){
			if ($gidperfilsoft){
				$cadenaXML.='</PERFILESSOFTWARES>';
			}
			$gidperfilsoft=$rs->campos["idperfilsoft"];
			$cadenaXML.='<PERFILESSOFTWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["sdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.=SubarbolXML_ComponentesSoftware($cmd,$rs->campos["idperfilsoft"]);
		}

			$cadenaXML.='<PERFILESHARDWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilhardware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["hdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.=SubarbolXML_ComponentesHardware($cmd,$rs->campos["idperfilhard"]);
			$cadenaXML.='</PERFILESHARDWARES>';

		$rs->Siguiente();
	}
	if ($gidperfilsoft){
		$cadenaXML.='</PERFILESSOFTWARES>';
		$cadenaXML.='</DISPONIBLESPERFILES>';
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ComponentesSoftware($cmd,$idperfilsoft){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softwares.descripcion,tiposoftwares.urlimg FROM perfilessoft_softwares  ";
	$cmd->texto.=" LEFT OUTER JOIN  softwares  ON softwares.idsoftware=perfilessoft_softwares.idsoftware";
	$cmd->texto.=" LEFT OUTER JOIN  tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware" ;
	$cmd->texto.=" WHERE perfilessoft_softwares.idperfilsoft=".$idperfilsoft;
	$cmd->texto.=" ORDER by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaXML.='<COMPONENTES';
	$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
	$cadenaXML.=' infonodo="Software components"';
	$cadenaXML.='>';
	while (!$rs->EOF){
			$cadenaXML.='<COMPONENTESOFT';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</COMPONENTESOFT>';
			$rs->Siguiente();
	}	
	$cadenaXML.='</COMPONENTES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesHardwaresSoportados($cmd,$idperfilsoft){
	$cadenaXML="";
	$gidperfilhard=null;
	$rs=new Recordset; 
	$cmd->texto="SELECT perfileshard.idperfilhard ,perfileshard.descripcion FROM perfileshard  ";
	$cmd->texto.=" LEFT OUTER JOIN perfileshard_perfilessoft  ON perfileshard_perfilessoft.idperfilhard=perfileshard.idperfilhard" ;
	$cmd->texto.=" WHERE perfileshard_perfilessoft.idperfilsoft=".$idperfilsoft;
	$cmd->texto.=" ORDER by perfileshard.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESPERFILES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="Availables hardware profiles"';
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
			$cadenaXML.=SubarbolXML_ComponentesHardware($cmd,$rs->campos["idperfilhard"]);
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
	$cadenaXML.=' infonodo="Hardware components"';
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
?>