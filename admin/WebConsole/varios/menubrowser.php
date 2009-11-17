<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: menucliente.php
// Descripción :Este fichero implementa el menu del browser de los clientes
// *************************************************************************************************************************************************
$iph=""; // Switch menu cliente
if (isset($_GET["iph"])) $iph=$_GET["iph"]; 

if(!empty($iph)){
	Header("Location:menubroser.php?iph= ".$iph); // Accede a la página de menus
	exit;
}
?>
<HTML>
	<HEAD>
	</HEAD>
	<BODY>
	<P>Error de acceso al menú del cliente.</P>
</BODY>
</HTML>

