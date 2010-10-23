<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: informacion_perfileshard.php
// Descripción : 
//		Muestra los componentes hardware que forman parte de un perfil hardware y los perfiles softwares disponibles
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../idiomas/php/".$idioma."/informacion_perfileshard_".$idioma.".php");
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
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../clases/jscripts/ArbolVistaXML.js"></SCRIPT>
</HEAD>
<BODY>
	<P align=center class=cabeceras><?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/confihard.gif"><BR><BR>
	<IMG src="../images/iconos/perfilhardware.gif"><SPAN class=presentaciones>&nbsp;&nbsp;<U><?echo $TbMsg[2]?></U>:	<? echo $descripcionperfil?></SPAN></P>
	<?echo $arbol->CreaArbolVistaXml(); // Crea arbol de configuraciones?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Devuelve una cadena con formato XML de toda la Información de los perfiles hardwares
	Parametros: 
		- cmd:Una comando ya operativo ( con conexiónabierta)  
		- idperfil: El identificador del perfil hardware
________________________________________________________________________________________________________*/
function CreaArbol($cmd,$idperfil){
	$cadenaXML=SubarbolXML_PerfilesHardwares($cmd,$idperfil);
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_PerfilesHardwares($cmd,$idperfilhard){
	global $TbMsg;
	$cadenaXML="";
	$rs=new Recordset; 
	$cmd->texto="SELECT tipohardwares.descripcion as tipohardware,perfileshard.idperfilhard ,
											perfileshard.descripcion as pdescripcion, perfileshard.comentarios,
											hardwares.idhardware,hardwares.descripcion as hdescripcion,tipohardwares.urlimg 
											FROM perfileshard  ";
	$cmd->texto.=" LEFT OUTER JOIN perfileshard_hardwares  ON perfileshard.idperfilhard=perfileshard_hardwares.idperfilhard";
	$cmd->texto.=" LEFT OUTER JOIN hardwares  ON hardwares.idhardware=perfileshard_hardwares.idhardware";
	$cmd->texto.=" LEFT OUTER JOIN tipohardwares  ON hardwares.idtipohardware=tipohardwares.idtipohardware" ;
	$cmd->texto.=" WHERE perfileshard.idperfilhard=".$idperfilhard;
	$cmd->texto.=" ORDER by tipohardwares.idtipohardware,hardwares.descripcion";
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	$rs->Primero(); 
	$cadenaXML.='<PERFILESHARDWARES';
	// Atributos`
	$cadenaXML.=' imagenodo="../images/iconos/perfilhardware.gif"';
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
		if ($rs->campos["idhardware"]){
			if (!$swcompo) {
				$cadenaXML.='<COMPONENTES';
				$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
				$cadenaXML.=' infonodo="'.$TbMsg[6].'"';
				$cadenaXML.='>';
				$swcompo=true;
			}	

			$cadenaXML.='<PERFILHARDWARE';
			// Atributos
			$cadenaXML.=' imagenodo='.$rs->campos["urlimg"];
			$cadenaXML.=' infonodo="('.$rs->campos["tipohardware"].") ".$rs->campos["hdescripcion"].'"';
			$cadenaXML.='>';
			$cadenaXML.='</PERFILHARDWARE>';
		}
		$rs->Siguiente();
	}
	if ($swcompo) {
		$cadenaXML.='</COMPONENTES>';
	}
	$cadenaXML.=SubarbolXML_Ordenadores($cmd,$idperfilhard);
	$cadenaXML.=SubarbolXML_ImagenesDisponibles($cmd,$idperfilhard);
	$cadenaXML.='</PERFILESHARDWARES>';
	$rs->Cerrar();
	return($cadenaXML);
}
//________________________________________________________________________________________________________
function SubarbolXML_Ordenadores($cmd,$idperfilhard)
{
	global $TbMsg;

	$cadenaXML="";
	$gidaula=0;
	$cmd->texto="SELECT DISTINCT aulas.idaula,aulas.nombreaula,ordenadores.idordenador,ordenadores.nombreordenador
								FROM ordenadores
 								INNER JOIN aulas ON  ordenadores.idaula=aulas.idaula
 								WHERE ordenadores.idperfilhard=".$idperfilhard." ORDER BY aulas.idaula,ordenadores.nombreordenador";
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
function SubarbolXML_ImagenesDisponibles($cmd,$idperfilhard)
{
	global $TbMsg;
	
	$cadenaXML="";
	$gidimagen=0;

	$cmd->texto="SELECT DISTINCT imagenes.* FROM imagenes
								INNER JOIN perfilessoft ON perfilessoft.idperfilsoft=imagenes.idperfilsoft
								INNER JOIN ordenadores_particiones ON ordenadores_particiones.idperfilsoft=imagenes.idperfilsoft
								INNER JOIN ordenadores ON ordenadores.idordenador=ordenadores_particiones.idordenador
								INNER JOIN perfileshard ON perfileshard.idperfilhard=ordenadores.idperfilhard
								WHERE perfileshard.idperfilhard=".$idperfilhard." 
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
