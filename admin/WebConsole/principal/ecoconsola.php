<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: sondeo.php
// Descripción : 
//		Consulta el eco de los clientes a traqvés de la consola remota
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
	$sw=0;  // Swich para conmutar entre sondeo a clientes o sólo consulta a la tabla de sockets

	if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
	if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
	if (isset($_POST["comando"])) $comando=$_POST["comando"]; 
	if (isset($_POST["sw"])) $sw=$_POST["sw"]; 
	//________________________________________________________________________________________________________
	$cmd=CreaComando($cadenaconexion);
	if (!$cmd)
		Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
	//________________________________________________________________________________________________________
	// Ámbito de aplicación de la petición
	//________________________________________________________________________________________________________
	$cadenaid="";
	$cadenaip="";
	$cadenamac="";

	RecopilaIpesMacs($cmd,$ambito,$idambito); // Ámbito de aplicación
	//________________________________________________________________________________________________________
	// Envio al servidor de la petición
	//________________________________________________________________________________________________________

	$trama_notificacion = shell($sw, $cadenaip, $comando);

	echo $trama_notificacion;


