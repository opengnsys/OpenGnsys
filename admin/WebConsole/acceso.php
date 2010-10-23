<?
// ********************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: barramenu.php
// Descripción :Este fichero implementa el menu general de la Aplicación
// ********************************************************************************************************
if(isset($_SESSION)){ 	// Si existe algua sesión ...
	session_unset(); // Elimina variables
	session_destroy(); // Destruye sesión
}

include_once("controlacceso.php");

$herror=0;
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 
Header("Location: acceso_".$idi.".php?herror=".$herror); // Redireccionamiento a la página de inicio en el idioma por defecto
?>
