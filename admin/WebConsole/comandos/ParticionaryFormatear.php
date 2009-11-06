<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: ParticionaryFormatear.php
// Descripción : 
//		Implementación del comando "ParticionaryFormatear"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/particionaryformatear_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$nombreambito="";
$cadenaip="";

$tbconfigur="";

$fp = fopen($fileparam,"r"); 
$parametros= fread ($fp, filesize ($fileparam));
fclose($fp);

$ValorParametros=extrae_parametros($parametros,chr(13),'=');
$identificador=$ValorParametros["identificador"]; 
$nombrefuncion=$ValorParametros["nombrefuncion"]; 
$ejecutor=$ValorParametros["ejecutor"]; 
$tipotrama=$ValorParametros["tipotrama"]; 
$ambito=$ValorParametros["ambito"]; 
$idambito=$ValorParametros["idambito"]; 
$nombreambito=$ValorParametros["nombreambito"]; 
$cadenaip=$ValorParametros["cadenaip"]; 

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/ParticionaryFormatear.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/MenuContextual.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/particionaryformatear_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatosocultos"> 
	<INPUT type=hidden name=identificador value=<? echo $identificador ?>>
	<INPUT type=hidden name=nombrefuncion value=<? echo $nombrefuncion ?>>
	<INPUT type=hidden name=ejecutor value=<? echo $ejecutor ?>>
	<INPUT type=hidden name=tipotrama value=<? echo $tipotrama ?>>
	<INPUT type=hidden name=ambito value=<? echo $ambito ?>>
	<INPUT type=hidden name=idambito value=<? echo $idambito ?>>
	<INPUT type=hidden name=cadenaip value=<? echo $cadenaip ?>>
</FORM>
<?
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
//________________________________________________________________________________________________________
?>	
<BR>
	<P align=center><SPAN align=center class=subcabeceras><? echo "Partitions"?></SPAN></P>
	<FORM  name="fdatos"> 
			<?
				echo tabla_configuraciones($cmd,$idcentro,$idambito,$ambito);
				echo '<TABLE border=0 style="visibility: hidden" id=patron_contenidoparticion>'.Patrontabla_Particion().'</TABLE>';
				echo '<INPUT type=hidden id=tbconfigur value="'.$tbconfigur.'">';
			?>
	</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function tabla_configuraciones($cmd,$idcentro,$idambito,$ambito){
	global $tbconfigur;
	global $TbMsg;
	$idc=0;
	$tablaHtml="";
	$tablaHtml.= '<TABLE align=center  id=tabla_contenidoparticion_'.$idc.'  value=0><TR><TD>';
	$tablaHtml.= '<TABLE id=tb_particiones_'.$idc.' class=tabla_listados_sin  align=center value=0 cellPadding=1 cellSpacing=1 >';
	$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH align=center ><IMG src="../images/iconos/eliminar.gif"></TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[9].'&nbsp</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[11].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[12].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	$ultpa=0;
	$tablaHtml.='</TABLE>';
	$tablaHtml.= '</TD></TR></TABLE>';

  // Boton de insercion
	$tablaHtml.= '<INPUT type=hidden id="ultpa_'.$idc.'" value='.$ultpa.'>';
	$tablaHtml.= '<div align=center>';
	$tablaHtml.= '<br><A href="#"><IMG border=0 style="cursor:hand" name="btanade_"'.$idc.'  src="../images/boton_annadir.gif" onclick="annadir_particion('.$idc.')" WIDTH="73" HEIGHT="22"></A>';
	$tablaHtml.= '</div><br>';
	$tbconfigur.=$idc.";";
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function Patrontabla_Particion(){
	global $TbMsg;
	$p="_upa_";
	$idc="_cfg_";
	$tablaHtml='<TR id=TRparticion_'.$p."_".$idc.'>'.chr(13);
	$tablaHtml.='<TD><input type=checkbox onclick="elimina_particion(this,'.$idc.')" id=eliminarparticion_'.$p."_".$idc.' value=0></TD>'.chr(13);
	$opciones="";
	for($i=1;$i<8;$i++){
		$opciones.=$i."=".$i.chr(13);
	}
	$opciones.="8=8";
	$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"numpar_".$p."_".$idc,"estilodesple","",$p,35,"chgpar").'</TD>'.chr(13);
	$opciones="1=BIGDOS".chr(13);
	$opciones.="2=FAT32".chr(13);
	$opciones.="3=NTFS".chr(13);
	$opciones.="4=EXT2".chr(13);
	$opciones.="5=EXT3".chr(13);
	$opciones.="6=EXT4".chr(13);
	$opciones.="7=LINUX-SWAP".chr(13);
	$opciones.="8=CACHE";
	$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"tipospar_".$p."_".$idc,"estilodesple","EMPTY",0,100,"chgtipopar").'</TD>'.chr(13);
	$tablaHtml.='<TD><span id=tiposo_'.$p."_".$idc.' value=0>&nbsp;<span style="COLOR:red">'.$TbMsg[17].'</span>&nbsp;</span></TD>'.chr(13);
	$tablaHtml.='<TD align=center><INPUT type=text onchange="chgtama('.$idc.')" id="tamano_'.$p."_".$idc.'" style="width=70" value=0 ></TD>'.chr(13);
	$opciones="1=".$TbMsg[14]."".chr(13);
	$opciones.="2=".$TbMsg[15]."".chr(13);
	$opciones.="3=".$TbMsg[16]."";
	$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"acciones_".$p."_".$idc,"estilodesple",$TbMsg[13],0,100,"chgaccion").'</TD>'.chr(13);
	$tablaHtml.='</TR>'.chr(13);
	$tablaHtml.='</TABLE>';
	$tablaHtml.= '</TD></TR></TABLE>';
	return($tablaHtml);
}
?>
