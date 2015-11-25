<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: CrearImagen.php
// Descripción : 
//		Implementación del comando "CrearImagen.php"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/crearimagen_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/pintaTablaConfiguraciones.php");

//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$resul=tomaPropiedades($cmd,$idambito);
if (!$resul){
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/CrearImagen.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/crearimagen_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<?
	$urlimg='../images/iconos/ordenador.gif';
	$textambito=$TbMsg[15];

	echo '<p align=center><span class=cabeceras>'.$TbMsg[0].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras>
			<U>'.$TbMsg[14].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>	
<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[6] ?></SPAN></P>

<FORM  align=center name="fdatos">
	<? echo tablaConfiguracionesCrearImagen($cmd,$idambito,$idrepositorio); ?>
</FORM>		

<?
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Recupera los datos de un ordenador
		Parametros: 
		- cmd: Una comando ya operativo (con conexiónabierta)  
		- ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function tomaPropiedades($cmd,$ido){
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idrepositorio;
	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard,idrepositorio FROM ordenadores WHERE idordenador='".$ido."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$idrepositorio=$rs->campos["idrepositorio"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los perfiles softwares
//	UHU - 2013/05/17 - Ahora las imagenes pueden ser en cualquier disco
//	Version 0.1: La consulta SQL se limita a IMAGENES_MONOLITICAS.
//		US ETSII - Irina Gomez - 2014-11-11
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idrepositorio,$idperfilsoft,$disk,$particion,$masterip)
{
	global $IMAGENES_MONOLITICAS;
	$SelectHtml="";
	$cmd->texto="SELECT DISTINCT imagenes.idimagen,imagenes.descripcion,imagenes.nombreca,
		imagenes.idperfilsoft, repositorios.nombrerepositorio, repositorios.ip
		FROM  imagenes INNER JOIN repositorios  ON imagenes.idrepositorio = repositorios.idrepositorio 
		INNER JOIN aulas ON aulas.idcentro = repositorios.idcentro 
		INNER JOIN ordenadores  ON  ordenadores.idaula = aulas.idaula
		WHERE imagenes.tipo=".$IMAGENES_MONOLITICAS."
		AND ordenadores.ip='".$masterip."' OR repositorios.ip='" .$masterip ."'
		ORDER BY imagenes.descripcion";

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$disk."_".$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["ip"].'"';
			if($idperfilsoft==$rs->campos["idperfilsoft"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"]. ' -- '. $rs->campos['nombrerepositorio']  . '</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}
?>
