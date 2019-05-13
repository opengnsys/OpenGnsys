<?php
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha útima modificación: Marzo-2006
// Nombre del fichero: menubrowser.php
// Descripción : 
//		Muestra menu en el browser del cliente
// ****************************************************************************
$idioma="esp"; // Por defecto idoma español
include_once("../idiomas/php/".$idioma."/msgbrowser_".$idioma.".php");

$idx="";
$msg="";

if (isset($_GET["msg"])) $msg=$_GET["msg"];  // Recoge indice del mensaje
if (isset($_GET["idx"])) $idx=$_GET["idx"];  // Recoge indice del mensaje

if(!empty($msg))
	$mensaje=UrlDecode($msg);
else
	$mensaje=$TbMsg[$idx];
?>
<HTML>
<HEAD>
	<TITLE>Administración web de aulas</TITLE>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<STYLE TYPE="text/css">

	.deepsea TD, .deepsea TH
	{
	background-color:#026afe;
	color:#FFFFFF;
	font-family: sans-serif;
	font-weight:600; 
	}

	.tdbarra{
		background: url('../images/iconos/barraven.png');
		color:#FFFFFF;
		font-family: sans-serif;
		font-size: 12px;
		font-weight:300;
		border: 1px solid #FFFFFF;
		border-right-color: #000000;
		border-bottom-color: #000000;
    }
	.tdclien{
		background: url('../images/iconos/clienven.png');
		color:#000000;
		font-family: sans-serif;
		font-size: 14px;
		font-weight:300;
		border: 1px solid #FFFFFF;
		border-right-color: #999999;
		border-bottom-color: #999999;
    }
	</STYLE>
</HEAD>
<BODY>
<?php
if(empty($idx) && empty($msg)) {
	// No hay operaciones realizándose
	echo '<BR><BR><BR><BR><BR>';
	echo '<TABLE cellspacing=0 cellpadding=2 align=center border=0>';
	echo '<TR><TD align=center><IMG border=0 src="../images/iconos/logoopengnsys.png"></TD></TR>';
	echo '<TR><TD align=center><SPAN style="COLOR: #999999;FONT-FAMILY: Arial, sans-serif; FONT-SIZE: 12px;">Iniciando...</TD></TR>';
	echo '</TR>';
	echo '</TABLE>';
}
else{
	// Se está realizando una operacion
	echo '<h1>' . $TbMsg[23]   . ' </h1>';
	echo '<h1>' . $TbMsg[24]   . ' </h1>';
	echo '<BR><BR><BR>';
	echo '<TABLE  cellspacing=0 cellpadding=2 align=center border=0>';
	echo '<TR><TD align=center class="tdbarra">OpenGnsys Browser Message</TD><TR>';
	echo '<TR><TD class="tdclien" valign=center >&nbsp;&nbsp;&nbsp;'.$mensaje.'&nbsp;&nbsp;&nbsp;</TD></TR>';
	echo '</TABLE>';
}
?>
</BODY>
</HTML>
