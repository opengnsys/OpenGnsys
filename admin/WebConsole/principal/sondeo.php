<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
	include_once("../includes/RecopilaIpesMacs.php");
	//________________________________________________________________________________________________________
	$ambito=0; 
	$idambito=0; 
	$sw=0;  // Swich para conmutar entre sondeo a clientes o sólo consulta a la tabla de sockets

	if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
	if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
	if (isset($_POST["sw"])) $sw=$_POST["sw"]; 
	//________________________________________________________________________________________________________
	$cmd=CreaComando($cadenaconexion);
	if (!$cmd)
		Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
	//________________________________________________________________________________________________________
	$funcion="Sondeo"; // Nombre de la función que procesa la petición
	if($sw==2)
		$funcion="respuestaSondeo"; // Nombre de la función que procesa la petición
	$atributos="";
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
	$resul=false;
	$trama="";
	$shidra=new SockHidra($servidorhidra,$hidraport); 
	if ($shidra->conectar()){ // Se ha establecido la conexión con el servidor hidra
		$parametros="nfn=".$funcion.chr(13);
		$parametros.=$aplicacion;
		$parametros.=$atributos;
		$parametros.=$acciones;
		$resul=$shidra->envia_peticion($parametros);
		if($resul)
			$trama=$shidra->recibe_respuesta();
		$shidra->desconectar();
	}
	if($resul){
		$hlonprm=hexdec(substr($trama,$LONCABECERA,$LONHEXPRM));
		$parametros=substr($trama,$LONCABECERA+$LONHEXPRM,$hlonprm);
		$ValorParametros=extrae_parametros($parametros,chr(13),'=');
		if (isset ($ValorParametros["tso"])) {
			$trama_notificacion=$ValorParametros["tso"];
			echo $trama_notificacion; // Devuelve respuesta
		}
	}
?>

