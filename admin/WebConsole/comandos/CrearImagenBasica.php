<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2012
// Fecha Última modificación: Noviembre-2012
// Nombre del fichero: CrearImagenBas.php
// Descripción : 
//		Implementación del comando "CrearImagenBas.php"
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
<TITLE>Administración web de aulas</TITLE>
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
			<TH align=center>&nbsp;<? echo $TbMsg[8] ?>&nbsp;</TH>			
			<TH align=center>&nbsp;<? echo $TbMsg[9] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[10]?>&nbsp;</TH>
		</TR>
<!------------------------------------------------------------------------------------------
 Detalle 
-------------------------------------------------------------------------------------------> 					
		<?
				$tbPar=tablaConfiguraciones($idambito);
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
	Dibuja una tabla con los datos de particiones y parametros a elegir
	
		Parametros: 
		- idordenador: El identificador del ordenador
----------------------------------------------------------------------------------------------*/
function tablaConfiguraciones($idordenador)
{
	global $idcentro;
	global $TbMsg;	
	global $cmd;
	
	$tablaHtml="";
	$cmd->texto="SELECT DISTINCT	ordenadores_particiones.numpar, ordenadores_particiones.idnombreso, nombresos.nombreso,
					ordenadores_particiones.idimagen, ordenadores_particiones.codpar,
					tipospar.clonable, perfilessoft.idperfilsoft,
					nombresos.idnombreso, nombresos.nombreso
					FROM ordenadores_particiones 
					INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar		
					LEFT OUTER JOIN nombresos ON nombresos.idnombreso=ordenadores_particiones.idnombreso
					LEFT OUTER JOIN perfilessoft ON perfilessoft.idperfilsoft=ordenadores_particiones.idperfilsoft										
					WHERE ordenadores_particiones.idordenador=$idordenador 
					ORDER BY ordenadores_particiones.numpar";
	//echo 	$cmd->texto;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($tablaHtml); // Error al abrir recordset
	$rs->Primero(); 
	$tbPAR="";
	while (!$rs->EOF){
		//$swcc=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]) && !empty($rs->campos["idperfilsoft"]); 
		$sw=$rs->campos["clonable"] && !empty($rs->campos["idnombreso"]); 
		if($sw){// Una partición es clonable si es cierta esta variable	
			$tbPAR.=$rs->campos["numpar"].";"; // Cadena con las particiones a procesar	
			$tablaHtml.='<TR id="trPar-'.$rs->campos["numpar"].'"';
			$tablaHtml.='<td align=center><input type=radio name="particion" value="'.$rs->campos["codpar"].'"></td>';
			$tablaHtml.='<td align="center">&nbsp;'.$rs->campos["numpar"].'&nbsp;</td>'; // Número de partición
			$tablaHtml.='<td align=center>&nbsp;'.$rs->campos["nombreso"].'&nbsp;</td>'; // Nombre sistema operativo
			$tablaHtml.='<td align=center>'.HTMLSELECT_imagenes($rs->campos["idimagen"]).'</td>';	
			$tablaHtml.='</tr>';			
		}
		$rs->Siguiente();
	}
	$rs->Cerrar();
	echo $tablaHtml;
	return($tbPAR);
}
/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con las opciones generales
----------------------------------------------------------------------------------------------*/
function opcionesAdicionales()
{
	global $TbMsg;	

	$tablaHtml.='<table width="90%" align=center border=0 cellPadding=0 cellSpacing=0 class="tabla_accesos">';
	$tablaHtml.='<tr><th colspan=8 align=center><b>&nbsp;'.$TbMsg[12].'&nbsp;</b></th></tr>';
	$tablaHtml.='<tr id="trOpc">
					<td align=right>'.$TbMsg[13].'</td>
					<td><input  type=checkbox name="bpi"></td>'; // Borrar imagen previamente del servidor 			
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

