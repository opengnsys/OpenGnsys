<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: ogAdmWebCon
// Autor: Jos�Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�: Diciembre-2003
// Fecha �tima modificaci�: Febrero-2005
// Nombre del fichero: menucliente.php
// Descripci� :Este fichero implementa el menu del browser de los clientes
// *************************************************************************************************************************************************
$iph=""; // Switch menu cliente
if (isset($_GET["iph"])) $iph=$_GET["iph"]; 

if(!empty($iph)){
	Header("Location:../controlacceso.php?iph=".$iph); // Accede a la p�ina de menus
	exit;
}
?>
<HTML>
	<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	</HEAD>
	<BODY>
	<H1>Error de acceso al menú del cliente.</H1>
</BODY>
</HTML>

