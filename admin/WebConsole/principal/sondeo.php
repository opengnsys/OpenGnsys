<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: sondeo.php
// Descripción : 
//		Consulta el estado de los ordenadores
// *************************************************************************************************************************************************
	include_once("../includes/ctrlacc.php");
	include_once("../clases/SockHidra.php");
	include_once("../clases/AdoPhp.php");
	include_once("../includes/constantes.php");
	include_once("../includes/comunes.php");
	include_once("../includes/CreaComando.php");
	//________________________________________________________________________________________________________
	$cadenaip=0; 
	$sw=0;  // Swich para conmutar entre sondeo a clientes o sólo consulta a la tabla de sockets

	if (isset($_POST["cadenaip"])) $cadenaip=$_POST["cadenaip"]; 
	if (isset($_POST["sw"])) $sw=$_POST["sw"]; 
	//________________________________________________________________________________________________________
	$cmd=CreaComando($cadenaconexion);
	if (!$cmd)
		Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
	//________________________________________________________________________________________________________
	switch($sw){
		case 1:
			$funcion="Sondeo"; // Nombre de la función que procesa la petición
			$atributos="sws=S";
			break;
		case 2:
			$funcion="Sondeo"; // Nombre de la función que procesa la petición
			$atributos="sws=T";
	}
	$aplicacion=""; // Ámbito de aplicación (cadena de ipes separadas por ";" y de identificadores de ordenadores por ","
	$acciones=""; // Cadena de identificadores de acciones separadas por ";" para seguimiento 
	//________________________________________________________________________________________________________
	// Ámbito de aplicación de la petición
	//________________________________________________________________________________________________________
	$aplicacion="iph=".$cadenaip.chr(13);
	//________________________________________________________________________________________________________
	// Envio al servidor de la petición
	//________________________________________________________________________________________________________
	$resul=false;
	$trama="";

	$parametros="1"; // Ejecutor
	$parametros.="nfn=".$funcion.chr(13);
	$parametros.=$atributos.chr(13);
	$parametros.=$acciones.chr(13);
	$parametros.=$aplicacion.chr(13);
	
	//die($parametros);

	$shidra=new SockHidra($servidorhidra,$hidraport); 
	if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
		$resul=$shidra->envia_comando($parametros);
		$trama=$shidra->recibe_respuesta();
		$parametros=substr($trama,$LONCABECERA,strlen($trama)-$LONCABECERA);
		$ValorParametros=extrae_parametros($parametros,chr(13),'=');
		$trama_notificacion=$ValorParametros["tso"];
		$shidra->desconectar();
		echo $trama_notificacion; // Devuelve respuesta
	}
?>
