<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Agosto-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: controlacceso.php
// Descripción :Este fichero implementa el control de acceso a la Aplicación en todas las páginas
// *************************************************************************************************************************************************
session_start(); // Activa variables de sesi�
$idcentro="";
$nombrecentro="";
$usuario="";
$idtipousuario=0;
$idioma="";
$cadenaconexion="";
$servidorhidra="";
$hidraport="";
$pagerror="";
$urlacceso="";

if (isset($_SESSION["widcentro"]))	$idcentro = $_SESSION["widcentro"];
if (isset($_SESSION["wnombrecentro"]))	$nombrecentro = $_SESSION["wnombrecentro"];
if (isset($_SESSION["wusuario"]))	$usuario = $_SESSION["wusuario"];
if (isset($_SESSION["widtipousuario"]))	$idtipousuario = $_SESSION["widtipousuario"];
if (isset($_SESSION["widioma"]))	$idioma = $_SESSION["widioma"];
if (isset($_SESSION["wcadenaconexion"]))	$cadenaconexion = $_SESSION["wcadenaconexion"];
if (isset($_SESSION["wservidorhidra"]))	$servidorhidra = $_SESSION["wservidorhidra"];
if (isset($_SESSION["whidraport"]))	$hidraport = $_SESSION["whidraport"];
if (isset($_SESSION["wpagerror"]))	$pagerror = $_SESSION["wpagerror"];
if (isset($_SESSION["wurlacceso"]))	$urlacceso = $_SESSION["wurlacceso"];
/*
echo "<BR>Cadena=".$_SESSION["wcadenaconexion"];
echo "<BR>servidorhidra=".$_SESSION["wservidorhidra"];
echo "<BR>hidraport=".$_SESSION["whidraport"];
echo "<BR>usuario=".$_SESSION["wusuario"];
echo "<BR>idtipousuario=".$_SESSION["widtipousuario"];
echo "<BR>urlacceso=".$_SESSION["wurlacceso"];
*/
// Comprueba el valor de las variables de sesión
$swacc=empty($cadenaconexion) || empty($servidorhidra) || empty($hidraport) || empty($usuario) || empty($idtipousuario);

//===============================================================================================
if ($swacc){ // Error en alguna variable de sesión
	die("***Error de acceso");
}
//===============================================================================================

