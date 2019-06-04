<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Abril-2010
// Nombre del fichero: actualizar.php
// Descripción : 
//		Obliga a los clientes a reiniciar la sesión en el sistema
// *************************************************************************************************************************************************
	include_once("../includes/ctrlacc.php");
	include_once("../clases/SockHidra.php");
	include_once("../clases/AdoPhp.php");
	include_once("../includes/constantes.php");
	include_once("../includes/comunes.php");
	include_once("../includes/CreaComando.php");
	include_once("../includes/RecopilaIpesMacs.php");
	include_once('../includes/restfunctions.php');
	//________________________________________________________________________________________________________
	$ambito=0; 
	$idambito=0; 

	if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
	if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
	//________________________________________________________________________________________________________
	$cmd=CreaComando($cadenaconexion);
	if (!$cmd)
		Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
	//________________________________________________________________________________________________________
	$funcion="Actualizar"; // Nombre de la función que procesa la petición
	$atributos=""; // Parametros adicionales  la petición
	$aplicacion=""; // Ámbito de aplicación (cadena de ipes separadas por ";" y de identificadores de ordenadores por ","
	$acciones=""; // Cadena de identificadores de acciones separadas por ";" para seguimiento 
	//________________________________________________________________________________________________________
	// Ámbito de aplicación de la petición
	//________________________________________________________________________________________________________
	$cadenaid="";
	$cadenaip="";
	$cadenamac="";
	RecopilaIpesMacs($cmd,$ambito,$idambito); // Ámbito de aplicación
	$aplicacion="ido=".$cadenaid.chr(13)."iph=".$cadenaip.chr(13);
	//________________________________________________________________________________________________________
	// Envio al servidor de la petición
	//________________________________________________________________________________________________________

	echo refresh($cadenaip) === 0 ? "0" : "1";
