<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: informacion_servidorrembo.php
// Descripción : 
//		Muestra los ordenadores que están gestionados por un servidore rembo
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_servidorrembo_".$idioma.".php");
//________________________________________________________________________________________________________
$idservidorrembo=0; 
$descripcionservidor=""; 
if (isset($_GET["idservidorrembo"])) $idservidorrembo=$_GET["idservidorrembo"]; // Recoge parametros
if (isset($_GET["descripcionservidor"])) $descripcionservidor=$_GET["descripcionservidor"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idservidorrembo); // Crea el arbol XML 

// Creación del árbol
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[3];  
$arbol=new ArbolVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXml.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/servidoresrembo.gif"><BR><BR>
	<IMG src="../images/iconos/servidorrembo.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:<? echo $descripcionservidor?></SPAN></P>
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
	Devuelve una cadena con formato XML de toda la información de los servidores rembo
	Parametros: 
		- cmd:Una comando ya operativo ( con conexión abierta)  
		- idservidorrembo: El identificador del perfil servidor rembo
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idservidorrembo){
	$cadenaXML=SubarbolXML_Servidoresrembo($cmd,$idservidorrembo);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Servidoresrembo($cmd,$idservidorrembo){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idservidorrembo,nombreservidorrembo,comentarios FROM servidoresrembo WHERE idservidorrembo=".$idservidorrembo ;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<SERVIDORREMBO';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_ServidorRembo'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/servidorrembo.gif" ';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreservidorrembo"].'"';
		$cadenaXML.='>';
		if($rs->campos["comentarios"]>" "){
			$cadenaXML.='<PROPIEDAD';
			$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
			$cadenaXML.=' infonodo="[b]'.$TbMsg[4].' :[/b] '.$rs->campos["comentarios"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PROPIEDAD>';
		}
		$cadenaXML.=SubarbolXML_grupos_servidoresrembo_ordenadores($cmd,$rs->campos["idservidorrembo"]);
		$cadenaXML.='</SERVIDORREMBO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_grupos_servidoresrembo_ordenadores($cmd,$idservidorrembo){
	$cadenaXML="";
	$gidaula="";
	$rs=new Recordset; 
	$cmd->texto="SELECT aulas.idaula,aulas.nombreaula,ordenadores. idordenador,ordenadores.nombreordenador FROM ordenadores INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula WHERE ordenadores.idservidorrembo=".$idservidorrembo." order by aulas.idaula,ordenadores.nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		if ($gidaula!=$rs->campos["idaula"]){
			if ($gidaula!="")
				$cadenaXML.='</AULA>';
			$cadenaXML.='<AULA ';
			// Atributos		
			$cadenaXML.=' imagenodo="../images/iconos/aula.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["nombreaula"].'"';
			$cadenaXML.='>';
			$gidaula=$rs->campos["idaula"];
		}
		// Visualiza los ordenadores de cada aula ( temporalmente desabilitado por rendimiento )
		$cadenaXML.='<ORDENADOR';
		// Atributos			
		$cadenaXML.=' imagenodo="../images/iconos/ordenador.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["nombreordenador"].'"';
		$cadenaXML.='></ORDENADOR>';
		$rs->Siguiente();
	}
	if ($gidaula!="")
		$cadenaXML.='</AULA>';
	$rs->Cerrar();
	return($cadenaXML);
}
?>