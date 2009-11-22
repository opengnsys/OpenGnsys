<?
// *************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
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
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript">

	</SCRIPT>
	<STYLE TYPE="text/css">

	.deepsea, .deepsea TD, .deepsea TH
	{
	background-color:#026afe;
	color:#FFFFFF;
	font-family: sans-serif;
	font-weight:600; 
	}

	.tdtest{
		background: url('../images/iconos/ventana.png');
		witdh:400;
		heigth: 300px;
	} 

	</STYLE>
</HEAD>
<BODY>
<?
	if(empty($idx) && empty($msg)) {
		echo '<BR><BR><BR><BR><BR>';
		echo '<TABLE cellspacing=0 cellpadding=2 align=center border=0>';
		echo '<TR>';
		echo '<TD align=center><IMG border=0 src="../images/iconos/logoopengnsys.png" width=64></TD>';
		echo '<TD align=center>';
		echo '<SPAN style="COLOR: #999999;FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 12px;">';
		echo '&nbsp;&nbsp;Iniciando ...</TD>';
		echo '</TR>';
		echo '</TABLE>';
	}
	else{
		echo '<BR><BR><BR>';
		echo '<TABLE CLASS="deepsea" cellspacing=0 cellpadding=2 align=center border=0>';
		echo '<TR>';
		echo '<TD height=20 >&nbsp;&nbsp;&nbsp;'.$mensaje.'&nbsp;&nbsp;&nbsp;</TD>';
		echo '</TR>';
		echo '</TABLE>';
	}
?>
</BODY>
</HTML>
