<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Abril-2010
// Nombre del fichero: opcionesacciones.php
// Descripción : 
//		Captura de parámetros comunes para la ejecución de comandos
// *************************************************************************************************************************************************
//________________________________________________________________________________________________________
// Captura parámetros
//________________________________________________________________________________________________________
$idcomando=0;
$ambito=0;
$idambito=0;
$nombreambito="";
$funcion="";
$atributos="";
$gestor="";
$filtro="";

if (isset($_POST["idcomando"])) $idcomando=$_POST["idcomando"]; 
if (isset($_POST["descricomando"])) $descricomando=$_POST["descricomando"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 
if (isset($_POST["funcion"])) $funcion=$_POST["funcion"]; 
if (isset($_POST["atributos"])) $atributos=$_POST["atributos"]; 
if (isset($_POST["gestor"])) $gestor=$_POST["gestor"]; 
if (isset($_POST["filtro"])) $filtro=$_POST["filtro"]; 
?>

