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
	switch($sw){
		case 1: // Envío del código de scrip
			$funcion="nfn=ConsolaRemota".chr(13);		
			$atributos="scp=".rawurlencode($comando).chr(13);
			break;
		case 2: // Recupera el archivo de eco
			$funcion="nfn=EcoConsola".chr(13); // Nombre de la función que procesa la petición
			$atributos=chr(13);
	}
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
		$parametros=$funcion.$aplicacion.$atributos.$acciones;
		$resul=$shidra->envia_peticion($parametros);
		if($resul)
			$trama=$shidra->recibe_respuesta();
		$shidra->desconectar();
	}
	if($resul){
		$hlonprm=hexdec(substr($trama,$LONCABECERA,$LONHEXPRM));
		$parametros=substr($trama,$LONCABECERA+$LONHEXPRM,$hlonprm);
		$ValorParametros=extrae_parametros($parametros,chr(13),'=');
		switch($sw){
			case 1: // Envío del código de scrip
				$trama_notificacion=$ValorParametros["res"];
				echo $trama_notificacion; // Devuelve respuesta
				break;
			case 2: // Recupera el archivo de eco
				$trama_notificacion=$ValorParametros["res"];
				echo $trama_notificacion; // Devuelve respuesta
		}
		
	}


