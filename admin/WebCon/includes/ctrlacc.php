<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Diciembre-2003
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: controlacceso.php
// Descripción :Este fichero implementa el control de acceso a la Aplicación en todas las p�inas
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

if (isset($_SESSION["idcentro"]))	$idcentro = $_SESSION["idcentro"];
if (isset($_SESSION["nombrecentro"]))	$nombrecentro = $_SESSION["nombrecentro"];
if (isset($_SESSION["usuario"]))	$usuario = $_SESSION["usuario"];
if (isset($_SESSION["idtipousuario"]))	$idtipousuario = $_SESSION["idtipousuario"];
if (isset($_SESSION["idioma"]))	$idioma = $_SESSION["idioma"];
if (isset($_SESSION["cadenaconexion"]))	$cadenaconexion = $_SESSION["cadenaconexion"];
if (isset($_SESSION["servidorhidra"]))	$servidorhidra = $_SESSION["servidorhidra"];
if (isset($_SESSION["hidraport"]))	$hidraport = $_SESSION["hidraport"];
if (isset($_SESSION["pagerror"]))	$pagerror = $_SESSION["pagerror"];
if (isset($_SESSION["urlacceso"]))	$urlacceso = $_SESSION["urlacceso"];

// Comprueba el valor de las variables de sesi�
$swacc=empty($cadenaconexion) || empty($servidorhidra) || empty($hidraport) || empty($usuario) || empty($idtipousuario);
//============================================================================================================================
if ($swacc){ // Error en alguna variable de sesión
	die("Error de acceso");
}
//============================================================================================================================
?>
