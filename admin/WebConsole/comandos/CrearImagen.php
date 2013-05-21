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
<TITLE>Administración web de aulas</TITLE>
<HEAD>
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
	<TABLE  id='tabla_conf' align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
			<TR>
				<TH align=center>&nbsp;&nbsp;</TH>
				<TH align=center>&nbsp;<?php echo $TbMsg["PARTITION"] ?>&nbsp;</TH>
				<TH align=center>&nbsp;<?php echo $TbMsg["PARTITION_TYPE"] ?>&nbsp;</TH>
				<TH align=center>&nbsp;<?php echo $TbMsg["SO_NAME"] ?>&nbsp;</TH>
				<TH align=center>&nbsp;<?php echo $TbMsg["IMAGE_TO_CREATE"] ?>&nbsp;</TD>
				<TH align=center>&nbsp;<?php echo $TbMsg["DESTINATION_REPOSITORY"] ?>&nbsp;</TD>
			</TR> 
		
				<?					
					echo tablaConfiguracionesCrearImagen($cmd,$idambito,$idrepositorio);
				?>
	</TABLE>
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
	UHU - 2013/05/17 - Ahora las imagenes pueden ser en cualquier disco
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idrepositorio,$idperfilsoft,$disk,$particion,$masterip)
{
	$SelectHtml="";
	$cmd->texto="SELECT imagenes.idimagen,imagenes.descripcion,imagenes.nombreca,imagenes.idperfilsoft, repositorios.nombrerepositorio
				FROM  imagenes INNER JOIN repositorios on imagenes.idrepositorio = repositorios.idrepositorio
				WHERE imagenes.idrepositorio=".$idrepositorio ." OR repositorios.ip='" .$masterip ."'";
	//echo $cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$disk."_".$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["nombreca"].'"';
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

function HTMLSELECT_imagenesORIGINAL($cmd,$idrepositorio,$idperfilsoft,$particion,$masterip)
{
	$SelectHtml="";
	$cmd->texto="SELECT imagenes.idimagen,imagenes.descripcion,imagenes.nombreca,imagenes.idperfilsoft 
				FROM  imagenes INNER JOIN repositorios on imagenes.idrepositorio = repositorios.idrepositorio
				WHERE imagenes.idrepositorio=".$idrepositorio ." OR repositorios.ip='" .$masterip ."'";
	//echo $cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["nombreca"].'"';
			if($idperfilsoft==$rs->campos["idperfilsoft"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"].'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}


/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los repositorios
	UHU - 2013/05/17 - Ahora las imagenes pueden ser en cualquier disco
________________________________________________________________________________________________________*/
function HTMLSELECT_repositorios($cmd,$idcentro,$idrepositorio,$disk,$particion,$masterip){
	$SelectHtml="";
	$rs=new Recordset; 
	$cmd->texto='SELECT idrepositorio, nombrerepositorio, ip FROM repositorios WHERE idrepositorio="'.$idrepositorio .'" OR ip="'.$masterip.'"';
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return($SelectHtml); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" id="desplerepositorios_'.$disk."_".$particion.'" style="WIDTH: 250">';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos["ip"].'"';
		if($rs->campos["idrepositorio"]==$idrepositorio) $SelectHtml.=" selected ";
		$SelectHtml.='>';
		$SelectHtml.= $rs->campos["nombrerepositorio"];
		$SelectHtml.='</OPTION>';
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}

?>
