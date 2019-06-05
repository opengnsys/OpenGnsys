<?php
// Fichero con funciones para trabajar con el webservice
include_once("../../includes/restfunctions.php");

/**
	En este punto disponemos de tres variables indicando las ips, las macs y las ids de los 
	ordenadores, este script obtiene cual es el repositorio
 	$cadenaid
	$cadenaip
	$cadenamac

 */

//Multicast or Unicast
preg_match_all('!\d{1}!', $atributos, $matches);

// Capturamos todas las ids
$macs = explode(";",$cadenamac);
$ips = explode(';',$cadenaip);

wol($matches[0][0], $macs, $ips);

// Recorremos las ids y vemos cual es la ip del repositorio
$repos = array();
$reposAndMacs = array();
foreach($macs as $mac){
	$cmd->texto="SELECT repo.ip, repo.apikey FROM ordenadores o,repositorios repo WHERE o.mac=\"".$mac."\" AND o.idrepositorio=repo.idrepositorio";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
    if (!$rs->Abrir()) 
    	return; // Error al abrir recordset
    while (!$rs->EOF){
		$repo = $rs->campos["ip"];
		if(!existREPO($repo, $repos)){
			$repos[count($repos)]=$repo;
		}
		// Una vez creado el repo se asigna la mac del pc a su lista
		if(empty($reposAndMacs[$repo])){
			$reposAndMacs[$repo] = array();
			$reposAndMacs[$repo]["apikey"] = $rs->campos["apikey"];
		}
		// Modificar la mac añadiendo ":" cada dos caracteres
		$tmp = substr_replace($mac, ":", 2, 0);
		$tmp = substr_replace($tmp, ":", 5, 0);
		$tmp = substr_replace($tmp, ":", 8, 0);
		$tmp = substr_replace($tmp, ":", 11, 0);
		$tmp = substr_replace($tmp, ":", 14, 0);

		$reposAndMacs[$repo][count($reposAndMacs[$repo])] = $tmp;
	    $rs->Siguiente();
	}
	$rs->Cerrar();
}

// En este punto tenemos un array con todos los repos y cada uno de ellos con una lista de todas las macs que deben arrancar
// Recorremos cada uno de ellos
foreach($reposAndMacs as $repo => $macs){
	// En el array de $macs tenemos la clave "apikey"
	if($macs["apikey"] !== "") {
		$apiKeyRepo = $macs["apikey"];
		unset($macs["apikey"]);
		// Componer datos de conexión para el repositorio.
		$urls[$repo]['url'] = "https://$repo/opengnsys/rest/repository/poweron";
		$urls[$repo]['header'] = array('Authorization: '. $apiKeyRepo);
		$urls[$repo]['post'] = '{"macs": ["' . implode('","', $macs) . '"], "ips": ["' . str_replace(';', '","', $cadenaip) .
			'"], "mar": "' . $matches[0][0] . '"}';
	}
	else {
		$avisoRepo = true;
	}
}
// Enviar petición múltiple a los repositorios afectados.
if (isset($urls)) {
	$result = multiRequest($urls);
	// Comprobar respuesta.
	foreach ($result as $repo => $res) {
		if ($res['code'] != 200) {
			$avisoRepo = true;
		}
	}
}


function existREPO($repo, $repos){
	$found=false;
	$index = 0;
	while(!$found && $index < count($repos)){
		$r = $repos[$index];
		$index++;
		if($r == $repo)
			$found=true;
	}
	return $found;
}


