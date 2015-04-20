<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: informacion_repositorios.php
// Descripción : 
//		Muestra los ordenadores que están gestionados por un repositorio
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_repositorio_".$idioma.".php");
//________________________________________________________________________________________________________
$idrepositorio=0; 
$descripcionrepositorio=""; 
if (isset($_GET["idrepositorio"])) $idrepositorio=$_GET["idrepositorio"]; // Recoge parametros
if (isset($_GET["descripcionrepositorio"])) $descripcionrepositorio=$_GET["descripcionrepositorio"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
else
	$arbolXML=CreaArbol($cmd,$idrepositorio); // Crea el arbol XML 

// Creación del árbol
$baseurlimg="../images/tsignos";
$clasedefault="tabla_listados_sin";
$titulotabla=$TbMsg[3];  
$arbol=new ArbolVistaXml($arbolXML,0,$baseurlimg,$clasedefault,1,20,130,1,$titulotabla);
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/repositorio.gif"><BR><BR>
	<IMG src="../images/iconos/repositorio.gif"><SPAN class=presentaciones>&nbsp;&nbsp;
	<U><?echo $TbMsg[2]?></U>:<? echo $descripcionrepositorio?></SPAN></P>
	<?echo $arbol->CreaArbolVistaXml(); // Crea arbol de configuraciones?>
</BODY>
</HTML>
<?php
/**************************************************************************************************************************************************
	Devuelve una cadena con formato XML de toda la información de los repositorios
	Parametros: 
		- cmd:Una comando ya operativo ( con conexión abierta)  
		- idrepositorio: El identificador del perfil repositorios
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idrepositorio){
	$cadenaXML=SubarbolXML_Repositorios($cmd,$idrepositorio);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Repositorios($cmd,$idrepositorio){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idrepositorio,nombrerepositorio,comentarios 
						FROM repositorios
						WHERE idrepositorio=".$idrepositorio ;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	while (!$rs->EOF){
		$cadenaXML.='<REPOSITORIO';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_Repositorio'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/repositorio.gif" ';
		$cadenaXML.=' infonodo="'.$rs->campos["nombrerepositorio"].'"';
		$cadenaXML.='>';
		if($rs->campos["comentarios"]>" "){
			$cadenaXML.='<PROPIEDAD';
			$cadenaXML.=' imagenodo="../images/iconos/propiedad.gif"';
			$cadenaXML.=' infonodo="[b]'.$TbMsg[4].' :[/b] '.$rs->campos["comentarios"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PROPIEDAD>';
		}
		$cadenaXML.=SubarbolXML_grupos_repositorios_ordenadores($cmd,$rs->campos["idrepositorio"]);
		$cadenaXML.=SubarbolXML_ImagenesDisponibles($cmd,$idrepositorio);	
		$cadenaXML.='</REPOSITORIO>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($cadenaXML);
}
//-------------------------------------------------------------------------------------------------------------------------------------------
function SubarbolXML_grupos_repositorios_ordenadores($cmd,$idrepositorio)
{
	global $TbMsg;
	
	$cadenaXML="";
	$gidaula="";
	$rs=new Recordset; 
	$cmd->texto="SELECT aulas.idaula,aulas.nombreaula,ordenadores. idordenador,ordenadores.nombreordenador 
									FROM ordenadores
									 INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula 
									 WHERE ordenadores.idrepositorio=".$idrepositorio." 
									 ORDER BY aulas.idaula,ordenadores.nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<AULASORDENADORES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[6].'"';
		$cadenaXML.='>';
	}	
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
	if ($gidaula!=""){
		$cadenaXML.='</AULA>';
		$cadenaXML.='</AULASORDENADORES>';	
	}	
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ImagenesDisponibles($cmd,$idrepositorio)
{
	global $TbMsg;
	
	$cadenaXML="";

	$cmd->texto="SELECT DISTINCT imagenes.* FROM imagenes
								WHERE imagenes.idrepositorio=".$idrepositorio." 
								AND  imagenes.codpar>0
								ORDER by imagenes.descripcion";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESIMAGENES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[5].'"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
		$cadenaXML.='<IMAGENES';
		$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
		$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].' ('.$TbMsg["IMGTYPE".$rs->campos["tipo"]].')"';
		$cadenaXML.='</IMAGENES>';
		$rs->Siguiente();
	}
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='</DISPONIBLESIMAGENES>';
	}
	$rs->Cerrar();
	return($cadenaXML);
}
?>
