<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2012
// Fecha última modificación: Noviembre-2012
// Nombre del fichero: CrearImagenBas.php
// Descripción: 
//		Implementación del comando "CrearImagenBas.php"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/crearsoftincremental_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/pintaTablaConfiguraciones.php");

//________________________________________________________________________________________________________
//
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
//
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
<SCRIPT language="javascript" src="./jscripts/CrearSoftIncremental.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/crearsoftincremental_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<!------------------------------------------------------------------------------------------
 Cabecera 
-------------------------------------------------------------------------------------------> 
	<P align=center class=cabeceras><?php echo $TbMsg[0] ?></P>
	<P align=center>
	<SPAN class=subcabeceras><?php echo $TbMsg[1] ?></SPAN>
	<BR>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[2] ?>&nbsp;</TH>
			<?php echo '<TD>'.$nombreordenador.'</TD>';?>
			<TD colspan=2 valign=top align=left rowspan=3><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
		</TR>	
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[3] ?>&nbsp;</TH>
			<?php echo '<TD>'.$ip.'</TD>';?>
		</TR>
		<TR>
			<TH align=center>&nbsp;<?php echo $TbMsg[4] ?>&nbsp;</TH>
			<?php echo '<TD>'.$mac.'</TD>';?>
		</TR>	
	</TABLE>
<!------------------------------------------------------------------------------------------
 Subcabecera 
-------------------------------------------------------------------------------------------> 	
	<P align=center><SPAN class=subcabeceras><?php echo $TbMsg[6] ?></SPAN></p>
	<FORM  align=center name="fdatos"> 
	<TABLE  width=90% align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg["PARTITION"] ?>&nbsp;</TH>			
			<TH align=center>&nbsp;<?php echo $TbMsg["SO_NAME"] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg["INCREMENTAL_IMAGE_REPOSITORY"]?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg["SYNC_METHOD"]?>&nbsp;</TH>
			<TH align=center>&nbsp;<dfn  title='<?php echo $TbMsg["TITLE_W"]?>'> W </dfn> &nbsp;</TH>
			<TH align=center>&nbsp;<dfn  title='<?php echo $TbMsg["TITLE_E"]?>'> E </dfn> &nbsp;</TH>
			<TH align=center>&nbsp;<dfn  title='<?php echo $TbMsg["TITLE_C"]?>'> C </dfn> &nbsp;</TH>
		</TR>
<!------------------------------------------------------------------------------------------
 Detalle 
-------------------------------------------------------------------------------------------> 											
			<?php
				$tbPar=tablaConfiguracionesSincronizacion1($idambito);
			?>
	</TABLE>
	<input type=hidden id="cadPar" value="<?php echo $tbPar ?>">
	<br>
		<?php
				opcionesAdicionales();
		?>	
</FORM>
<?php
//---------------------------------------------------------------------------------------------
// Pie 
//----------------------------------------------------------------------------------------------
	include_once("./includes/formularioacciones.php");
	include_once("./includes/opcionesacciones.php");
?>
</BODY>
</HTML>
<?php
//*********************************************************************************************
//	FUNCIONES
//*********************************************************************************************

/*----------------------------------------------------------------------------------------------
	Recupera los datos de un ordenador
		Parámetros:
		- ido: El identificador del ordenador
----------------------------------------------------------------------------------------------*/
function tomaPropiedades($cmd,$ido)
{
	global $nombreordenador;
	global $ip;
	global $mac;
	global $cmd;	
	
	$rs=new Recordset; 
	$cmd->texto="SELECT  nombreordenador, ip, mac, idperfilhard, idrepositorio 
			FROM ordenadores 
			WHERE idordenador='".$ido."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];global $idcentro;
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}

/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con las opciones generales
----------------------------------------------------------------------------------------------*/
function opcionesAdicionales()
{
	global $TbMsg;	
	
	$tablaHtml='<table width="90%" style="display:none" align=center border=0 cellPadding=0 cellSpacing=0 class="tabla_accesos">';
	$tablaHtml.='<tr><th colspan=8 align=center><b>&nbsp;'.$TbMsg[11].'&nbsp;</b></th></tr>';
	$tablaHtml.='<tr id="trOpc">
					<td align=right>'.$TbMsg[13].'</td>
					<td ><input  type=checkbox name="bpi"></td>'; // Borrar imagen del servidor  o borrar partición previamente
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[14].'</td>
					<td><input type=checkbox name="cpc"></td>'; // Copiar además la imagen a la caché
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[15].'</td>
					<td><input type=checkbox name="bpc"></td>'; // Borrar imagen de la caché previamente antes de copiarla
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[16].'</td>
					<td><input type=checkbox name="nba"></td>'; // No borrar archivos en destino  						
	$tablaHtml.='</tr>';
	$tablaHtml.='	</table>';
	echo $tablaHtml;
}
/*----------------------------------------------------------------------------------------------
	Crea desplegable de imagenes
----------------------------------------------------------------------------------------------*/
function HTMLSELECT_imagenes($idimagen)
{
	global $cmd;
	global $idcentro;	
	global $IMAGENES_INCREMENTALES;
	
	$SelectHtml="";
	$cmd->texto="SELECT imagenes.idimagen as idimageninc,imagenes.descripcion,imagenes.nombreca as nombreincca,
				imagesbas.nombreca,imagesbas.idimagen,imagesbas.ruta,
				repositorios.ip,repositorios.nombrerepositorio
				FROM  imagenes
				INNER JOIN imagenes as imagesbas on imagesbas.idimagen = imagenes.imagenid
				INNER JOIN repositorios on imagesbas.idrepositorio = repositorios.idrepositorio
				WHERE imagenes.tipo=".$IMAGENES_INCREMENTALES." 
				AND imagesbas.idcentro=".$idcentro;
	//echo $cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return("");
	$rs->Primero(); 
	$SelectHtml.= '<SELECT class="estilodesple" style="width:95%">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	while (!$rs->EOF){
		$SelectHtml.='<OPTION 
		value="'.$rs->campos["idimagen"].";".$rs->campos["nombreca"].";".$rs->campos["ip"].";".$rs->campos["idimageninc"].";".$rs->campos["nombreincca"].";".$rs->campos["ruta"].'"';
		if($idimagen==$rs->campos["idimagen"]) $SelectHtml.=" selected ";
			
		$SelectHtml.='>';
		$SelectHtml.= $rs->campos["descripcion"].' - '. $rs->campos['nombrerepositorio'].'</OPTION>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}

