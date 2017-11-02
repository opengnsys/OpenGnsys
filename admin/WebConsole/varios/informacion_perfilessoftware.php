<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: informacion_perfilessoft.php
// Descripción : 
//		Muestra los componentes software que forman parte de un perfil software y los perfiles softwares disponibles
// Version 1.1 - Muetra sistema operativo.
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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confisoft.gif"><BR><BR>
	<IMG src="../images/iconos/perfilsoftware.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:	<? echo $descripcionperfil?></SPAN></P>
	<?echo $arbol->CreaArbolVistaXml(); // Crea arbol de configuraciones?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Devuelve una cadena con formato XML de toda la Información de los perfiles software
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
function SubarbolXML_PerfilesSoftwares($cmd,$idperfilsoft)
{
	global $TbMsg;

	$cadenaXML="";

	$cmd->texto="SELECT perfilessoft.idperfilsoft ,perfilessoft.descripcion as pdescripcion, perfilessoft.comentarios,
								softwares.idsoftware,softwares.descripcion as hdescripcion,tiposoftwares.urlimg, nombreso FROM perfilessoft  
								LEFT OUTER JOIN  perfilessoft_softwares  ON perfilessoft.idperfilsoft=perfilessoft_softwares.idperfilsoft
								LEFT OUTER JOIN  softwares  ON softwares.idsoftware=perfilessoft_softwares.idsoftware
								LEFT OUTER JOIN  tiposoftwares  ON softwares.idtiposoftware=tiposoftwares.idtiposoftware
								LEFT OUTER JOIN nombresos USING (idnombreso)
								WHERE perfilessoft.idperfilsoft=".$idperfilsoft."
								ORDER by tiposoftwares.idtiposoftware,softwares.descripcion";
	$rs=new Recordset; 								
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
				if ( $rs->campos["nombreso"] != "") {
					$cadenaXML.='<PERFILSOFTWARE';
					// Atributos
					$cadenaXML.=' imagenodo="../images/iconos/so.gif"';
					$cadenaXML.=' infonodo="'.$rs->campos["nombreso"].'"';
					$cadenaXML.='>';
				$cadenaXML.='</PERFILSOFTWARE>';
				}
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
	$cadenaXML.=SubarbolXML_Ordenadores($cmd,$idperfilsoft);
	$cadenaXML.=SubarbolXML_ImagenesDisponibles($cmd,$idperfilsoft);
	$cadenaXML.='</PERFILESSOFTWARES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Ordenadores($cmd,$idperfilsoft)
{
	global $TbMsg;

	$cadenaXML="";
	$gidaula=0;
	$cmd->texto="SELECT DISTINCT aulas.idaula,aulas.nombreaula,ordenadores.idordenador,
								ordenadores.nombreordenador,ordenadores_particiones.numpar
								FROM ordenadores
 								INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula
								INNER JOIN ordenadores_particiones ON  ordenadores_particiones.idordenador=ordenadores.idordenador 								
 								WHERE ordenadores_particiones.idperfilsoft=".$idperfilsoft." ORDER BY aulas.idaula,ordenadores.nombreordenador";
	$rs=new Recordset; 
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
			if (!empty($gidaula))
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
	if (!empty($gidaula))
		$cadenaXML.='</AULA>';
	if ($rs->numeroderegistros>0)
			$cadenaXML.='</ORDENADORES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_ImagenesDisponibles($cmd,$idperfilsoft)
{
	global $TbMsg;
	
	$cadenaXML="";
	$gidimagen=0;

	$cmd->texto="SELECT imagenes.* FROM imagenes
								INNER JOIN perfilessoft ON perfilessoft.idperfilsoft=imagenes.idperfilsoft
								WHERE perfilessoft.idperfilsoft=".$idperfilsoft." 
								AND  imagenes.codpar>0
								ORDER by imagenes.descripcion";
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if ($rs->numeroderegistros>0) {
		$cadenaXML.='<DISPONIBLESIMAGENES';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$TbMsg[4].'"';
		$cadenaXML.='>';
	}
	while (!$rs->EOF){
		if ($gidimagen!=$rs->campos["idperfilsoft"]){
			if ($gidimagen){
				$cadenaXML.='</IMAGENES>';
			}
			$gidimagen=$rs->campos["idperfilsoft"];
			$cadenaXML.='<IMAGENES';
			// Atributos
			$cadenaXML.=' imagenodo="../images/iconos/imagenes.gif"';
			$cadenaXML.=' infonodo="'.$rs->campos["descripcion"].'"';
			$cadenaXML.='>';
		}
		$rs->Siguiente();
	}
	if ($gidimagen){
		$cadenaXML.='</IMAGENES>';
		$cadenaXML.='</DISPONIBLESIMAGENES>';
	}
	$rs->Cerrar();
	return($cadenaXML);
}
?>
