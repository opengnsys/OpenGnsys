<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: acceso.php
// Descripción : Redirecciona a la págia de inicio del idioma que se quiere tener por defecto
// *************************************************************************************************************************************************
if(isset($_SESSION)){ 	// Si existe algua sesión ...
	session_unset(); // Elimina variables
	session_destroy(); // Destruye sesión
}
$herror=0;
if (isset($_GET["herror"])) $herror=$_GET["herror"]; 
if (isset($_POST["herror"])) $herror=$_POST["herror"]; 
Header("Location: acceso_esp.php?herror=".$herror); // Redireccionamiento a la página de inicio en el idioma por defecto
?>