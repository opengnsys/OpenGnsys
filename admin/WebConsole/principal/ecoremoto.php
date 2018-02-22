<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: ecoremoto.php
// Descripción : 
//		Crea una consola remota para escribir comandos de la shell de forma remota
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/ecoremoto_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
$idambito="";
$litambito="";
$nomambito=""; 

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; 
if (isset($_GET["nomambito"])) $nomambito=$_GET["nomambito"]; 
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Eco-<?php echo $nomambito?></TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/ecoremoto.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript">
//______________________________________________________________________________________________________
</SCRIPT> 
	<FORM name="fdatos" action="shellconsola.php">
		<INPUT type=hidden name="idambito" value=<?php echo $idambito?>>
		<INPUT type=hidden name="litambito" value=<?php echo $litambito?>>
		<INPUT type=hidden name="nomambito" value=<?php echo $nomambito?>>
	</FORM>

<?php
switch($litambito){
		case $LITAMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $LITAMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $LITAMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $LITAMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $LITAMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
?>
	<P align=center class=cabeceras><?php echo $TbMsg[7]?><BR>
	<SPAN align=center class=subcabeceras>
		<IMG src="<?php echo $urlimg?>">&nbsp;<?php echo $textambito.": ".$nomambito?></SPAN></P>
		<DIV id="Layer_nota" align=center >
		<BR>
		<SPAN align=center class=notas><I><?php echo $TbMsg[8]?></I></SPAN>

	<table align=center border="0" cellpadding="0" cellspacing="1">
			<tr><td class="presentaciones">Salida</td></tr>
			<tr><td>
				<div id="diveco" class="marco" align=left style="width:700px;height:500px;overflow:scroll"><P><?php echo $TbMsg[11]?></P></div>
			</td></tr>
	</table>
	</DIV>

<SCRIPT language="javascript">
	enviaping(); 
</SCRIPT>
</BODY>
</HTML>
