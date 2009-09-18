<?
include_once("../includes/ctrlacc.php");

include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/TablaVistaXML.php");
include_once("../clases/MenuContextual.php");

$idperfilhard=0; 
$descripcionperfil=""; 
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"]; // Recoge parametros
if (isset($_GET["descripcionperfil"])) $descripcionperfil=$_GET["descripcionperfil"]; // Recoge parametros

//-------------------------------------------------------------------------------
// Para pruebas
// $idcentro = 1; 
//$cadenaconexion="127.0.0.1;usuhidra;passusuhidra;BDHidra;sqlserver";
//-------------------------------------------------------------------------------
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	$arbolXML=""; // Error de conexión
else
	$arbolXML=CreaArbol($cmd,$idperfilhard); // Crea el arbol XML con todos los datos del perfil hardware
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla="Perfiles y Softwares Combinacional disponibles";  
$arbol=new TablaVistaXml($arbolXML,0,$baseurlimg,$clasedefault,0,20,130,$titulotabla);

?>
<HTML>
<TITLE>" Administración web de aulas"</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
	<SCRIPT language="javascript" src="../clases/jscripts/TablaVistaXml.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/hardwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
</HEAD>
<BODY>
	<p align=center class=cabeceras>Perfiles Hardwares<br>
	<span align=center class=subcabeceras>Perfiles Softwares disponibles</span>&nbsp;<img src="../images/iconos/confisoft.gif"></p>
	<br>
	<DIV align=center>
		<span class=presentaciones><b>Perfil Hardware:&nbsp;</b><? echo $descripcionperfil?></span>
	<?
	echo $arbol->CreaTablaVistaXml(); // Crea arbol de configuraciones
	$flotante=new MenuContextual(); // Instancia clase
	$XMLcontextual=CreacontextualXMLTipos_H(); // Crea menu contextual de tipos 

	?>
	</DIV>
	 <input type=hidden value="<? echo $idcentro?>" id=idcentro>	 
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/*==============================================================
	Devuelve una objeto comando totalmente operativo (con la conexión abierta)
	Parametros: 
		- cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor
		usuario,password,base de datos,etc separados por coma
----------------------------------------------------------------------------------------------------------------*/
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cmd->Conexion=&$cn; 
	return($cmd);
}
/*=======================================================
	Devuelve una cadena con formato XML de toda la información de las configuraciones
	hardwares
	Parametros: 
		- idperfilhard: El identificador del perfil hardware
		- cmd:Una comando ya operativo ( con conexión abierta)  
----------------------------------------------------------------------------------------------------*/
function CreaArbol($cmd,$idperfilhard){
	$cadenaXML=SubarbolXML_PerfilesSoftwaresDisponibles($cmd,$idperfilhard);
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_PerfilesSoftwaresDisponibles($cmd,$idperfilhard){
	$cadenaXML="";
	$gidperfilsoft=null;
	$swcombi=false;
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion as pdescripcion,softcombinacional.idsoftcombinacional,softcombinacional.descripcion  as cdescripcion FROM perfilessoft  ";
	$cmd->texto.=" LEFT OUTER JOIN  perfileshard_perfilessoft  ON perfileshard_perfilessoft.idperfilsoft=perfilessoft.idperfilsoft" ;
	$cmd->texto.=" LEFT OUTER JOIN  phard_psoft_softcombinacional  ON phard_psoft_softcombinacional.idphardidpsoft=perfileshard_perfilessoft .idphardidpsoft" ;
	$cmd->texto.=" LEFT OUTER JOIN  softcombinacional  ON softcombinacional.idsoftcombinacional=phard_psoft_softcombinacional .idsoftcombinacional" ;
	$cmd->texto.=" WHERE perfileshard_perfilessoft.idperfilhard=".$idperfilhard;
	$cmd->texto.=" ORDER by perfilessoft.descripcion,softcombinacional.descripcion";

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 

	while (!$rs->EOF){
		if ($gidperfilsoft!=$rs->campos["idperfilsoft"]){
			if ($gidperfilsoft){
				if ($swcombi){
					$swcombi=false;
					$cadenaXML.='</DISPONIBLESCOMBI>';
				}
				$cadenaXML.='</PERFILESSOFTWARES>';
			}
			$gidperfilsoft=$rs->campos["idperfilsoft"];
			$cadenaXML.='<PERFILESSOFTWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["pdescripcion"].'"';
			$cadenaXML.=' classnodo="linea_abajo"';
			$cadenaXML.='>';
		}
		if ($rs->campos["idsoftcombinacional"]){
			if (!$swcombi){
				$swcombi=true;
				$cadenaXML.='<DISPONIBLESCOMBI';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="Software combinacional disponible"';
				$cadenaXML.='>';
			}	
			$cadenaXML.='<PERFILSOFTWARE';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/softcombi.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["cdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.=SubarbolXML_ComponentesdelCombi($cmd,$rs->campos["idsoftcombinacional"]);
			$cadenaXML.='</PERFILSOFTWARE>';
		}
		$rs->Siguiente();
	}
	if ($gidperfilsoft){
		if ($swcombi){
			$swcombi=false;
			$cadenaXML.='</DISPONIBLESCOMBI>';
		}
		$cadenaXML.='</PERFILESSOFTWARES>';

	}
	$rs->Cerrar();

	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_ComponentesdelCombi($cmd,$idsoftcombinacional){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT softwares.idsoftware,softwares.descripcion, tiposoftwares.urlimg FROM softwares";
	$cmd->texto.=" INNER JOIN tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware";
	$cmd->texto.=" INNER JOIN softcombinacional_softwares  ON softcombinacional_softwares.idsoftware=softwares.idsoftware";
	$cmd->texto.=" WHERE softcombinacional_softwares.idsoftcombinacional=".$idsoftcombinacional."  order by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<COMPONENTESSOFTWARES';
		// Atributos
		$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
		$cadenaXML.='>';
		$cadenaXML.='</COMPONENTESSOFTWARES>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
/*===================================================
	Menus contextuales
---------------------------------------------------------------------------------------------*/
function CreacontextualXMLTipos_H(){
	$layerXML='<MENUCONTEXTUAL';
	$layerXML.=' idctx="flo_Tipos"';
	$layerXML.=' maxanchu=187';
	$layerXML.=' swimg=1';
	$layerXML.=' clase="menu_contextual"';
	$layerXML.='>';

	$layerXML.='<ITEM';
	$layerXML.=' alpulsar="insertar_tipohardware()"';
	$layerXML.=' imgitem="../images/iconos/confihard.gif"';
	$layerXML.=' textoitem="Definir nuevo tipo de hardware"';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>