<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: EjecutarScripts.php
// Descripción : 
//		Implementación del comando "EjecutarScripts"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/ejecutarscripts_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$nombreambito="";
$cadenaip="";

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
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/EjecutarScripts.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/ejecutarscripts_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM action="./gestores/gestor_EjecutarScripts.php" method="post" enctype="multipart/form-data" name="fdatos">
	<INPUT type=hidden name=identificador value=<? echo $identificador ?>>
	<INPUT type=hidden name=nombrefuncion value=<? echo $nombrefuncion ?>>
	<INPUT type=hidden name=ejecutor value=<? echo $ejecutor ?>>
	<INPUT type=hidden name=tipotrama value=<? echo $tipotrama ?>>
	<INPUT type=hidden name=ambito value=<? echo $ambito ?>>
	<INPUT type=hidden name=idambito value=<? echo $idambito ?>>
	<INPUT type=hidden name=cadenaip value=<? echo $cadenaip ?>>
	<INPUT type=hidden name=pseudocodigo value=0>
	<INPUT type=hidden name=sw_ejya value="">
	<INPUT type=hidden name=sw_seguimiento value="">
	<INPUT type=hidden name=sw_mkprocedimiento value="">
	<INPUT type=hidden name=nwidprocedimiento value="">
	<INPUT type=hidden name=nwdescriprocedimiento value="">
	<INPUT type=hidden name=sw_mktarea value="">
	<INPUT type=hidden name=nwidtarea value="">
	<INPUT type=hidden name=nwdescritarea value="">

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
?>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<table align=center  class=tabla_datos border="0" cellpadding="0" cellspacing="1">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[7]?>&nbsp;</th>
			<td ><input class="cajatexto" name="titulo" type="text" style="width:352"></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[8]?>&nbsp;</th>
			<td ><textarea class="cajatexto" name="descripcion" cols="70" rows="3"></textarea></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[9]?>&nbsp;</th>
			<td><textarea class="cajatexto" name="codigo" cols="70" rows="18"></textarea></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
        <tr> 
            <th>&nbsp;<? echo $TbMsg[10]?>&nbsp;</th>
			<td ><input  class="cajatexto" name="userfile" type="file"  size="45"></td></tr>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
</TABLE>
 </FORM>
 <?
 //________________________________________________________________________________________________________
include_once("../includes/opcionesacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotones.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
