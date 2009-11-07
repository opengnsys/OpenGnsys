<?
include_once("../includes/ctrlacc.php");

include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/TablaVistaXML.php");
include_once("../clases/MenuContextual.php");

$idsoftcombinacional=0; 
$descripcioncombi=""; 
if (isset($_GET["idsoftcombinacional"])) $idsoftcombinacional=$_GET["idsoftcombinacional"]; // Recoge parametros
if (isset($_GET["descripcioncombi"])) $descripcioncombi=$_GET["descripcioncombi"]; // Recoge parametros

//-------------------------------------------------------------------------------
// Para pruebas
// $idcentro = 1; 
//$cadenaconexion="127.0.0.1;usuhidra;passusuhidra;BDHidra;sqlserver";
//-------------------------------------------------------------------------------
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	$arbolXML=""; // Error de conexión
else
	$arbolXML=CreaArbol($cmd,$idsoftcombinacional); // Crea el arbol XML con todos los datos del software combinacional
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla="Perfiles Softwares combinables y Hardwares soportados";  
$arbol=new TablaVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,$titulotabla);

?>
<HTML>
<TITLE>" Administración web de aulas"</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/TablaVistaXml.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/softwares.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
</HEAD>
<BODY>
	<p align=center class=cabeceras>Softwares Combinacionales<br>
	<span align=center class=subcabeceras>Perfiles Softwares combinables</span>&nbsp;<img src="../images/iconos/confisoft.gif"></p>
	<br>
	<DIV align=center>
		<span class=presentaciones><b>Software Combinacional:&nbsp;</b><? echo $descripcioncombi?></span>
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
	softwares
	Parametros: 
		- idsoftcombinacional: El identificador del software combinacional
		- cmd:Una comando ya operativo ( con conexión abierta)  
----------------------------------------------------------------------------------------------------*/
function CreaArbol($cmd,$idsoftcombinacional){
	$cadenaXML=SubarbolXML_PerfilesSoftwaresParaCombi($cmd,$idsoftcombinacional);
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_PerfilesSoftwaresParaCombi($cmd,$idsoftcombinacional){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion as pdescripcion  FROM perfilessoft  ";
	$cmd->texto.=" LEFT OUTER JOIN  perfileshard_perfilessoft  ON perfileshard_perfilessoft.idperfilsoft=perfilessoft.idperfilsoft" ;
	$cmd->texto.=" LEFT OUTER JOIN  phard_psoft_softcombinacional  ON phard_psoft_softcombinacional.idphardidpsoft=perfileshard_perfilessoft .idphardidpsoft" ;
	$cmd->texto.=" WHERE phard_psoft_softcombinacional.idsoftcombinacional=".$idsoftcombinacional;
	$cmd->texto.=" GROUP BY  perfilessoft.idperfilsoft ,perfilessoft.descripcion";
	$cmd->texto.=" ORDER by perfilessoft.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
			$cadenaXML.='<PERFILESSOFTWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["pdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.=SubarbolXML_PerfilesHardwaresParaCombi($cmd,$rs->campos["idperfilsoft"],$idsoftcombinacional);
			$cadenaXML.='</PERFILESSOFTWARES>';
			$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
	
}
//-------------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_PerfilesHardwaresParaCombi($cmd,$idperfilsoft,$idsoftcombinacional){
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfileshard.idperfilhard ,perfileshard.descripcion as pdescripcion FROM perfileshard  ";
	$cmd->texto.=" LEFT OUTER JOIN  perfileshard_perfilessoft  ON perfileshard_perfilessoft.idperfilhard=perfileshard.idperfilhard" ;
	$cmd->texto.=" LEFT OUTER JOIN  phard_psoft_softcombinacional  ON phard_psoft_softcombinacional.idphardidpsoft=perfileshard_perfilessoft .idphardidpsoft" ;
	$cmd->texto.=" WHERE phard_psoft_softcombinacional.idsoftcombinacional=".$idsoftcombinacional." AND perfileshard_perfilessoft.idperfilsoft=".$idperfilsoft ;
	$cmd->texto.=" ORDER by perfileshard.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESPERFILES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="Perfiles Hardwares soportados"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
			$cadenaXML.='<PERFILESHARDWARES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/perfilhardware.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["pdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PERFILESHARDWARES>';
			$rs->Siguiente();
	}
		if ($rs->numeroderegistros>0) 
			$cadenaXML.='</DISPONIBLESPERFILES>';
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
	$layerXML.=' alpulsar="insertar_tiposoftware()"';
	$layerXML.=' imgitem="../images/iconos/confisoft.gif"';
	$layerXML.=' textoitem="Definir nuevo tipo de software"';
	$layerXML.='></ITEM>';

	$layerXML.='</MENUCONTEXTUAL>';
	return($layerXML);
}
?>