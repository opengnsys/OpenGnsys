<?php
// *************************************************************************************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�n: A�o 2012
// Fecha �ltima modificaci�n: Noviembre-2012
// Nombre del fichero: CrearImagenBas.php
// Descripci�n : 
//		Implementaci�n del comando "CrearImagenBas.php"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/crearimagenbasica_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/pintaTablaConfiguraciones.php");
//________________________________________________________________________________________________________
//
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
//________________________________________________________________________________________________________
//
$resul=tomaPropiedades($cmd,$idambito);
if (!$resul){
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci�n de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci�n web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/CrearImagenBasica.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/crearimagenbasica_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<!------------------------------------------------------------------------------------------
 Cabecera 
------------------------------------------------------------------------------------------->
	<P align=center class=cabeceras><? echo $TbMsg[0] ?><P>
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[1] ?></SPAN>
	</BR>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[2] ?>&nbsp;</TD>
			<? echo '<TD>'.$nombreordenador.'</TD>';?>
			<TD colspan=2 valign=top align=left rowspan=3><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
		</TR>	
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[3] ?>&nbsp;</TD>
			<? echo '<TD>'.$ip.'</TD>';?>
		</TR>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[4] ?>&nbsp;</TD>
			<? echo '<TD>'.$mac.'</TD>';?>
		</TR>	
	</TABLE>
	</P>
<!------------------------------------------------------------------------------------------
 Subcabecera 
-------------------------------------------------------------------------------------------> 	
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[6] ?></SPAN></p>
	<FORM  align=center name="fdatos"> 
		<TABLE  width=90% align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg["PARTITION"] ?>&nbsp;</TH>			
			<TH align=center>&nbsp;<?php echo $TbMsg["SO_NAME"] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<?php echo $TbMsg["IMAGE_REPOSITORY"]?>&nbsp;</TH>
			<TH align=center>&nbsp;W&nbsp;</TH>
			<TH align=center>&nbsp;E&nbsp;</TH>
			<TH align=center>&nbsp;C&nbsp;</TH>
		</TR>
<!------------------------------------------------------------------------------------------
 Detalle 
-------------------------------------------------------------------------------------------> 					
		<?
				$tbPar=tablaConfiguracionesSincronizacion1($idambito);
		?>
		</TABLE>
	<input type=hidden id="cadPar" value="<? echo $tbPar ?>">
	<br>
		<?
				opcionesAdicionales();
		?>
</FORM>
<?
//---------------------------------------------------------------------------------------------
// Pie 
//----------------------------------------------------------------------------------------------
	include_once("./includes/formularioacciones.php");
	include_once("./includes/opcionesacciones.php");
?>
</BODY>
</HTML>
<?
//*********************************************************************************************
//	FUNCIONES
//*********************************************************************************************

/*----------------------------------------------------------------------------------------------
	Recupera los datos de un ordenador
		Parametros: 
		- ido: El identificador del ordenador
----------------------------------------------------------------------------------------------*/
function tomaPropiedades($cmd,$ido)
{
	global $nombreordenador;
	global $ip;
	global $mac;
	global $cmd;	
	
	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard,idrepositorio 
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
	global $funcion;
	
	$tablaHtml='<table width="90%" ';
	
	//if($funcion!="CrearImagenBasica")
		$tablaHtml.='style="display:none"';
			
	$tablaHtml.=' center border=0 cellPadding=0 cellSpacing=0 class="tabla_accesos">';
	$tablaHtml.='<tr><th colspan=8 align=center><b>&nbsp;'.$TbMsg[12].'&nbsp;</b></th></tr>';
	$tablaHtml.='<tr id="trOpc">
					<td align=right>'.$TbMsg[13].'</td>
					<td><input  type=checkbox name="bpi"></td>'; // Borrar imagen previamente del servidor 			
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[14].'</td>
					<td><input type=checkbox name="cpc"></td>'; // Copiar adem�s la imagen a la cach�
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[15].'</td>
					<td><input type=checkbox name="bpc"></td>'; // Borrar imagen de la cach� previamente antes de copiarla 						
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
	global $IMAGENES_BASICAS;
	global $idcentro;	
	
	$SelectHtml="";
	$cmd->texto="SELECT imagenes.idimagen,imagenes.descripcion,imagenes.nombreca,imagenes.ruta,
				repositorios.ip,repositorios.nombrerepositorio
				FROM  imagenes
				INNER JOIN repositorios on imagenes.idrepositorio = repositorios.idrepositorio
				WHERE tipo=".$IMAGENES_BASICAS." 
				AND imagenes.idcentro=".$idcentro;
				
	//echo $cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return("");
	$rs->Primero(); 
	$SelectHtml.= '<SELECT class="estilodesple" style="width:95%">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"].";".$rs->campos["nombreca"].";".$rs->campos["ip"].";".$rs->campos["ruta"].'"';
		if($idimagen==$rs->campos["idimagen"]) $SelectHtml.=" selected ";
			
		$SelectHtml.='>';
		$SelectHtml.= $rs->campos["descripcion"].' - '. $rs->campos['nombrerepositorio'].'</OPTION>';
		$rs->Siguiente();
	}
	$rs->Cerrar();
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}
?>

