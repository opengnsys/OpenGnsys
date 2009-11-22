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

$msg="";
if (isset($_GET["msg"])) $msg=$_GET["msg"];  // Recoge indice del mensaje

if (isset($TbMsg[$msg]))
	$mensaje=$TbMsg[$msg];
else
	$mensaje=$TbMsg[0]; // Mensaje erronéo
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript">

	</SCRIPT>
</HEAD>
<BODY>
	<BR><BR><BR>
	<TABLE class="mensajebrowser" cellspacing=0 cellpadding=2 align=center border=0>
		<TR>
			<TD align=center class="mensajebrowser">&nbsp;<? echo $mensaje?>&nbsp;</TD>
		</TR>
	</TABLE>
</BODY>
</HTML>
