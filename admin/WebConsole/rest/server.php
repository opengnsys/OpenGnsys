<?php
/**
 * @file    index.php
 * @brief   OpenGnsys Server REST API manager.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1.0 - First version
 * @date    2016-09-19
 */


// Auxiliar functions.

/**
 * @brief    Check if user is administrator and print error messages if not.
 * @param    int adminid  Administrator id.
 * @return   boolean      "true" if admin id. is equals to global user id., otherwise "false".
 */
function checkAdmin($adminid) {
	global $userid;

	if ($adminid == $userid) {
		return true;
	} else {
		// Print error message.
		$response['message'] = 'Cannot access this resource';
		jsonResponse(401, $response);
		return false;
	}
}

/**
 * @fn    addClassroomGroup(&$classroomGroups, $rs)
 * @brief Funcion privada usada para añadir grupos de aulas recursivamente
 * @param classroomGroups Grupos de aulas que pueden contener más grupos
 * @param rs resultset de la consulta a la base de datos.
 */
function addClassroomGroup(&$classroomGroups, $rs){

	array_walk($classroomGroups, function(&$group,$key){
		global $rs;
		if (isset($group['id']) && $group['id'] === $rs->campos["group_group_id"]) {
			array_push($group["classroomGroups"],array("id" => $rs->campos["group_id"], 
				"name" => $rs->campos["nombregrupoordenador"], 
				"comments" => $rs->campos["comentarios"],
				"classroomGroups" => array()));
		}
		else if(count($group["classroomGroups"]) > 0){
			addClassroomGroup($group["classroomGroups"], $rs);
		}
		/**/
	});
}

/**
 * @fn    getStatus(ouid, labid, [clntid])
 * @brief    Returns client execution status or status of all lab's clients.
 * @param    int ouid    OU id.
 * @param    int labid   Lab. id.
 * @param    int clntid  Client id. (optional)
 * @return   string      JSON object or array of objects including status data.
 */
function getStatus($ouid, $labid, $clntid=0) {
	global $userid;
	global $cmd;
	global $LONCABECERA;
	global $LONHEXPRM;
	$app = \Slim\Slim::getInstance();
	$response = [];
	$id = [];
	$stat = [];
	$ip = "";
	$urls = [];
	// Status mapping.
	$status = ['OFF'=>"off",
		   'INI'=>"initializing",
		   'OPG'=>"oglive",
		   'BSY'=>"busy",
		   'LNX'=>"linux",
		   'OSX'=>"macos",
		   'WIN'=>"windows",
		   'UNK'=>"unknown"];
	// Parameters.
	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$single = is_numeric(explode("/", $app->request->getResourceUri())[6]);

	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, aulas.idaula, ordenadores.idordenador, ordenadores.ip
  FROM ordenadores
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND aulas.idaula='$labid'
EOD;
	// Request for a single client.
	if ($single) {
		$clntid = htmlspecialchars($clntid);
		$cmd->texto .= <<<EOD
   AND ordenadores.idordenador='$clntid';
EOD;
	}
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and asset exists.
	if (checkAdmin($rs->campos["idusuario"]) and (($single and checkParameter($rs->campos["idordenador"])) or (! $single and checkParameter($rs->campos["idaula"])))) {
		while (!$rs->EOF) {
			$id[$rs->campos["ip"]] = $rs->campos["idordenador"];
			$stat[$rs->campos["ip"]] = $status['OFF'];
			$rs->Siguiente();
		}
		// Get client status.
		$clientid = implode(",", $id);
		$clientip = implode(";", array_keys($id));
		$result = clients(2, $clientip);
		// Check status type.
		if (checkParameter($result)) {
			foreach (explode(";", $result) as $data) {
				if (!empty($data)) {
					list($clip, $clst) = explode("/", $data);
					if ($clst != "OFF") {
						// Update current status.
						$stat[$clip] = $status[$clst];
					}
				}
			}
		}
		// Prepare request to new OGAgent for OSes.
		foreach ($stat as $ip => $st) {
			if ($st == "off") {
				$urls[$ip] = "https://$ip:8000/opengnsys/status";
			}
		}
		// Send request to OGAgents.
		if (isset($urls)) {
			$result = multiRequest($urls);
		}
		// Parse responses.
		reset($urls);
		foreach ($result as $res) {
			if (!empty($res['data'])) {
				// Get status and session data.
				$ip = key($urls);
				$data = json_decode($res['data']);
				if (@isset($status[$data->status])) {
					$stat[$ip] = $status[$data->status];
					$logged[$ip] = $data->loggedin;
				} else {
					$stat[$ip] = $status['UNK'];
				}
			}
			unset($urls[$ip]);
		}
		// Compose JSON response.
		if ($single) {
			// Single response.
			$response['id'] = (int)reset($id);
			$response['ip'] = key($id);
			$response['status'] = $stat[key($id)];
			empty($logged[$ip]) || $response['loggedin'] = $logged[$ip];
		} else {
			// Multiple responses.
			foreach ($stat as $ip => $st) {
				$tmp = Array();
				$tmp['id'] = (int)$id[$ip];
				$tmp['ip'] = $ip;
				$tmp['status'] = $stat[$ip];
				empty($logged[$ip]) || $tmp['loggedin'] = $logged[$ip];
				array_push($response, $tmp);
			}
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
}


// REST routes.

/**
 * @brief    user login.
 * @note     Route: /login, Method: POST
 * @param    string username   User name.
 * @param    string password   User password.
 * @return   string            JSON response with user id. and API key.
 * @note     User's API key is stored in a new field of "usuarios" table.
 */
$app->post('/login',
    function() use ($app) {
	global $cmd;
	global $userid;

	$response = Array();
	// Reading JSON parameters.
	try {
		$input = json_decode($app->request()->getBody());
		$user = htmlspecialchars($input->username);
		$pass = htmlspecialchars($input->password);
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		jsonResponse(400, $response);
		$app->stop();
	}

	// Checking parameters. 
	if (! empty($user) and ! empty($pass)) {
		// Database query.
		$cmd->texto = "SELECT idusuario, apikey
			 	 FROM usuarios
				WHERE usuario='$user' AND pasguor=SHA2('$pass',224)";
		$rs=new Recordset;
		$rs->Comando=&$cmd;
		if ($rs->Abrir()) {
			$rs->Primero();
			if (!$rs->EOF){
				// JSON response.
				$userid=$rs->campos["idusuario"];
				$apikey=$rs->campos["apikey"];
				$response['userid'] = $userid;
				$response['apikey'] = $apikey;
				jsonResponse(200, $response);
			} else {
                		// Credentials error.
                		$response['message'] = 'Login failed. Incorrect credentials';
				jsonResponse(401, $response);
				$app->stop();
			}
			$rs->Cerrar();
		} else {
			// Access error.
			$response['message'] = "An error occurred. Please try again";
			jsonResponse(500, $response);
			$app->stop();
		}
	} else {
		# Error: missing some input parameter.
		$response['message'] = 'Missing username or password';
		jsonResponse(400, $response);
		$app->stop();
	}
    }
);

/**
 * @brief    List all defined Organizational Units
 * @note     Route: /ous, Method: GET
 * @return   string  JSON array with id. and name for every defined OU
 */
$app->get('/ous(/)', function() {
	global $cmd;

	$cmd->texto = "SELECT * FROM centros";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$response = Array();
	$rs->Primero();
	while (!$rs->EOF) {
		$tmp = Array();
		$tmp['id'] = (int)$rs->campos["idcentro"];
		$tmp['name'] = $rs->campos["nombrecentro"];
		array_push($response, $tmp);
		$rs->Siguiente();
	}
	$rs->Cerrar(); 
	jsonResponse(200, $response);
   } 
);

/**
 * @brief    Get Organizational Unit data
 * @note     Route: /ous/:ouid, Method: GET
 * @param    int ouid  OU id.
 * @return   string    JSON string with OU's parameters
 */
$app->get('/ous/:ouid(/)', 'validateApiKey',
    function($ouid) {
	global $cmd;
	global $userid;

	$ouid = htmlspecialchars($ouid);
	// Show OU information if user is OU's admin.
	$cmd->texto = <<<EOD
SELECT *
  FROM centros
 RIGHT JOIN administradores_centros USING(idcentro)
 WHERE administradores_centros.idusuario = '$userid'
   AND centros.idcentro = '$ouid'
 LIMIT 1;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	if (checkAdmin($rs->campos["idusuario"]) and
	    checkParameter($rs->campos["idcentro"])) {
		$response['id'] = (int)$ouid;
		$response['name'] = $rs->campos["nombrecentro"];
		$response['description'] = $rs->campos["comentarios"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    List group of labs in an Organizational Unit
 * @note     Route: /ous/:ouid/groups, Method: GET
 * @param    int ouid   OU id.
 * @return   string      JSON array of OU groups
 */
$app->get('/ous/:ouid/groups(/)', 'validateApiKey', function($ouid) {
	global $cmd;
	global $userid;

	$ouid = htmlspecialchars($ouid);
	// List group of labs if user is OU's admin.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, grupos.*
  FROM grupos
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND idcentro='$ouid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin.
	if (checkAdmin($rs->campos["idusuario"])) {
		$response = Array();
		// Read data.
		if (! is_null($rs->campos["idcentro"])) {
			while (!$rs->EOF) {
				$tmp = Array();
				$tmp['id'] = (int)$rs->campos["idgrupo"];
				$tmp['name'] = $rs->campos["nombregrupo"];
				$tmp['type'] = $rs->campos["tipo"];
				$tmp['comments'] = $rs->campos["comentarios"];
				if($rs->campos["grupoid"] != 0){
					$tmp['parent']['id'] = (int)$rs->campos["grupoid"];
				}
				array_push($response, $tmp);
				$rs->Siguiente();
			}
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar();
    }
);

/**
 * @brief    List all labs defined in an OU
 * @note     Route: /ous/:ouid/labs, Method: GET
 * @param    int ouid  OU id.
 * @return   string    JSON array of all UO's labs data
 */
$app->get('/ous/:ouid/labs(/)', 'validateApiKey',
    function($ouid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, aulas.*, grp.idgrupo AS group_id,
       grp.nombregrupoordenador, grp.grupoid AS group_group_id, grp.comentarios
  FROM aulas
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN gruposordenadores AS grp USING(idaula)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
 ORDER BY aulas.idaula, grp.idgrupo
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error opening recordset.
	// Check if user is an UO admin.
	$rs->Primero();
	if (checkAdmin($rs->campos["idusuario"])) {
		$response = Array();
		if (! is_null($rs->campos["idcentro"])) {
			while (!$rs->EOF) {
				// En los resultados las aulas vienen repetidas tantas veces como grupos tengan, solo dejamos uno
				$classroomIndex = -1;
				$found=false;
				$index = 0;
				while(!$found && $index < count($response)){
					if(isset($response[$index]["id"]) && $response[$index]["id"] == $rs->campos["idaula"]){
						$classroomIndex = $index;
						$found = true;
					}
					$index++;
				}
				if(!$found){
					$tmp = Array();
					$tmp['id'] = (int)$rs->campos["idaula"];
					$tmp['name'] = $rs->campos["nombreaula"];
					$tmp['inremotepc'] = ($rs->campos["inremotepc"] == 1);
					$tmp['group']['id'] = (int)$rs->campos["grupoid"];
					$tmp['ou']['id'] = (int)$ouid;
					array_push($response, $tmp);
				}
				else{
					// Le añadimos el grupo en cuestion siempre que no sea un subgrupo
					if($rs->campos["group_group_id"] == 0){
						array_push($response[$classroomIndex]['classroomGroups'],
							array("id" => (int)$rs->campos["group_id"],
							"name" => $rs->campos["nombregrupoordenador"],
							"comments" => $rs->campos["comentarios"],
							"classroomGroups" => array()));
					}
					else {
						// Buscamos el grupo donde añadir el grupo
						addClassroomGroup($response[$classroomIndex]['classroomGroups'], $rs);
					}
				}
				$rs->Siguiente();
			}
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get lab data
 * @note     Route: /ous/:ouid/labs/:labid, Method: GET
 * @param    int ouid   OU id.
 * @param    int labid  lab id.
 * @return   string     JSON string with lab parameters
 */
$app->get('/ous/:ouid/labs/:labid(/)', 'validateApiKey',
    function($ouid, $labid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, COUNT(idordenador) AS defclients, aulas.*
  FROM aulas
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN ordenadores USING(idaula)
 WHERE adm.idusuario = '$userid'
   AND idcentro='$ouid'
   AND idaula='$labid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and lab exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idaula"])) {
		$response['id'] = (int)$rs->campos["idaula"];
		$response['name'] = $rs->campos["nombreaula"];
		$response['location'] = $rs->campos["ubicacion"];
		$response['description'] = $rs->campos["comentarios"];
		$response['inremotepc'] = ($rs->campos["inremotepc"] == 1);
		$response['capacity'] = (int)$rs->campos["puestos"];
		if ($rs->campos["idordprofesor"]) {
			$response['profclient']['id'] = (int)$rs->campos["idordprofesor"];
		}
		$response['defclients'] = (int)$rs->campos["defclients"];
		$response['projector'] = ($rs->campos["cagnon"] == 1);
		$response['board'] = ($rs->campos["pizarra"] == 1);
		$response['routerip'] = $rs->campos["router"];
		$response['netmask'] = $rs->campos["netmask"];
		$response['ntp'] = $rs->campos["ntp"];
		$response['dns'] = $rs->campos["dns"];
		$response['proxyurl'] = $rs->campos["proxy"];
		switch ($rs->campos["modomul"]) {
			case 1:  $response['mcastmode'] = "half-duplex"; break;
			case 2:  $response['mcastmode'] = "full-duplex"; break;
			default: $response['mcastmode'] = $rs->campos["modomul"];
		}
		$response['mcastip'] = $rs->campos["ipmul"];
		$response['mcastport'] = $rs->campos["pormul"];
		$response['mcastspeed'] = $rs->campos["velmul"];
		$response['p2pmode'] = $rs->campos["modp2p"];
		$response['p2ptime'] = $rs->campos["timep2p"];
		$response['picture'] = $rs->campos["urlfoto"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);


/**
 * @brief    List all clients defined in a lab
 * @note     Route: /ous/:ouid/labs/:labid/clients, Method: GET
 * @param    int ouid   OU id.
 * @param    int labid  lab id.
 * @return   string     JSON data with lab id. and array of lab parameters
 */
$app->get('/ous/:ouid/labs/:labid/clients(/)', 'validateApiKey',
    function($ouid, $labid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.*, aulas.idaula AS labid
  FROM ordenadores
 RIGHT JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND aulas.idaula='$labid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and lab exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["labid"])) {
		$response = Array();
		while (!$rs->EOF) {
			if (!is_null($rs->campos["idordenador"])) {
				$tmp = Array();
				$tmp['id'] = (int)$rs->campos["idordenador"];
				$tmp['name'] = $rs->campos["nombreordenador"];
				$tmp['ip'] = $rs->campos["ip"];
				$tmp['mac'] = $rs->campos["mac"];
				$tmp['ou']['id'] = (int)$ouid;
				$tmp['lab']['id'] = (int)$labid;
				array_push($response, $tmp);
			}
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get execution status of all clients defined in a lab
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/status, Method: GET
 * @param    int ouid   OU id.
 * @param    int labid  lab id.
 * @return   string     JSON string with array of all client status defined in a lab
 */
$app->get('/ous/:ouid/labs/:labid/clients/status(/)', 'validateApiKey', 'getStatus');

/**
 * @brief    Get client data
 * @note     Route: /ous/id1/labs/id2clients/id3, Method: GET
 * @param    int ouid    OU id.
 * @param    int labid   lab id.
 * @param    int clntid  client id.
 * @return   string      JSON string with hardware parameters
 */
$app->get('/ous/:ouid/labs/:labid/clients/:clntid(/)', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.*,
       IF(ordenadores.idordenador=aulas.idordprofesor, 1, 0) AS profclient
  FROM ordenadores
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND idcentro='$ouid'
   AND idaula='$labid'
   AND idordenador='$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin, lab exists and client exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idaula"]) and checkParameter($rs->campos["idordenador"])) {
		// Read data.
		$response['id'] = (int)$rs->campos["idordenador"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['serialno'] = $rs->campos["numserie"];
		$response['netiface'] = $rs->campos["netiface"];
		$response['netdriver'] = $rs->campos["netdriver"];
		$response['mac'] = $rs->campos["mac"];
		$response['ip'] = $rs->campos["ip"];
		$response['netmask'] = $rs->campos["mascara"];
		$response['routerip'] = $rs->campos["router"];
		$response['repo']['id'] = (int)$rs->campos["idrepositorio"];
		$response['profclient'] = ($rs->campos["profclient"] == 1);
		//$response['hardprofile']['id'] = $rs->campos["idperfilhard"];
		//$response['menu']['id'] = $rs->campos["idmenu"];
		$response['validation'] = ($rs->campos["validacion"] == 1);
		$response['boottype'] = $rs->campos["arranque"];
		$response['picture'] = $rs->campos["fotoord"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get client's harware configuration data
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/hardware, Method: GET
 * @param    int ouid    OU id.
 * @param    int labid   lab id.
 * @param    int clntid  client id.
 * @return   string      JSON string with cleint parameters
 */
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/hardware(/)', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador, ordenadores.nombreordenador,
       tipohardwares.nemonico, hardwares.descripcion
  FROM ordenadores
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN perfileshard_hardwares USING(idperfilhard)
  LEFT JOIN hardwares ON perfileshard_hardwares.idhardware=hardwares.idhardware
  LEFT JOIN tipohardwares ON tipohardwares.idtipohardware=hardwares.idtipohardware
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND aulas.idaula='$labid'
   AND ordenadores.idordenador='$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and client exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idordenador"])) {
		// Read data.
		$response['id'] = (int)$rs->campos["idordenador"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['hardware'] = Array();
		while (!$rs->EOF) {
			if (!is_null($rs->campos["nemonico"])) {
				$tmp = Array();
				$tmp['type'] = $rs->campos["nemonico"];
				$tmp['description'] = $rs->campos["descripcion"];
				array_push($response['hardware'], $tmp);
			}
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get client's disk configuration data
 * @note     Route: /ous/:ouid1/labs/:labid/clients/:clntid/diskcfg, Method: GET
 * @param    int ouid    OU id.
 * @param    int labid   lab id.
 * @param    int clntid  client id.
 * @return   string      JSON string with disk parameters
 */
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/diskcfg(/)', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador AS clientid,
       ordenadores.nombreordenador, ordenadores_particiones.*, tipospar.tipopar,
       sistemasficheros.nemonico, nombresos.nombreso, imagenes.nombreca,
       (imagenes.revision - ordenadores_particiones.revision) AS difimagen
  FROM ordenadores_particiones
 RIGHT JOIN ordenadores USING(idordenador)
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN tipospar USING(codpar)
  LEFT JOIN sistemasficheros USING(idsistemafichero)
  LEFT JOIN nombresos USING(idnombreso)
  LEFT JOIN imagenes USING(idimagen)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND aulas.idaula='$labid'
   AND ordenadores.idordenador='$clntid'
 ORDER BY numdisk ASC, numpar ASC;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and client exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["clientid"])) {
		// Read data.
		$response['id'] = (int)$rs->campos["clientid"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['diskcfg'] = Array();
		while (!$rs->EOF) {
			// Skip header.
			if ($rs->campos["numdisk"] == 0) {
				$rs->Siguiente();
				continue;
			}
			$tmp = Array();
			// Common data.
			$tmp['disk'] = (int)$rs->campos["numdisk"];
			$tmp['size'] = (int)$rs->campos["tamano"];
			if ($rs->campos["numpar"] == 0) {
				// Disk data.
				switch ($rs->campos["codpar"]) {
					case 1:  $tmp['parttable'] = "MSDOS"; break;
					case 2:  $tmp['parttable'] = "GPT"; break;
					case 3:  $tmp['parttable'] = "LVM"; break;
					case 4:  $tmp['parttable'] = "ZPOOL"; break;
					default: $tmp['parttable'] = $rs->campos["codpar"];
				}
			} else {
				// Partition data.
				$tmp['partition'] = (int)$rs->campos["numpar"];
				$tmp['parttype'] = $rs->campos["tipopar"];
				$tmp['filesystem'] = $rs->campos["nemonico"];
				$tmp['usage'] = (int)$rs->campos["uso"];
				if ($rs->campos["nombreso"] != null) {
					$tmp['os'] = $rs->campos["nombreso"];
					if ($rs->campos["idimagen"] > 0) {
						// Restored image data.
						$tmp['image']['id'] = (int)$rs->campos["idimagen"];
						$tmp['image']['deploydate'] = $rs->campos["fechadespliegue"];
						// Check if image is updated.
						$tmp['image']['updated'] = ($rs->campos["difimagen"] == 0);
					}
				}
				//$tmp['cachedata'] = $rs->campos["cache"];
			}
			array_push($response['diskcfg'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get client's execution status
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/status, Method: GET
 * @param    int ouid    OU id.
 * @param    int labid   lab id.
 * @param    int clntid  client id.
 * @return   string      JSON string with client status
 */
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/status(/)', 'validateApiKey', 'getStatus');

/**
 * @brief    List all image repositories defined in an OU
 * @note     Route: /ous/id/repos, Method: GET
 * @param    int ouid  OU id.
 * @return   string    JSON array of all UO's repo data
 */
$app->get('/ous/:ouid/repos(/)', 'validateApiKey',
    function($ouid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, adm.idcentro AS ouid, repositorios.*
  FROM repositorios
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["ouid"])) {
		$response = Array();
		while (!$rs->EOF) {
			if (! is_null($rs->campos["idcentro"])) {
				$tmp = Array();
				$tmp['id'] = (int)$rs->campos["idrepositorio"];
				$tmp['name'] = $rs->campos["nombrerepositorio"];
				$tmp['ou']['id'] = (int)$ouid;
				array_push($response, $tmp);
			}
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    Get image repository data
 * @note     Route: /ous/:ouid/repos/:repoid, Method: GET
 * @param    int ouid    OU id.
 * @param    int repoid  repository id.
 * @return   string      JSON string with repo parameters
 */
$app->get('/ous/:ouid/repos/:repoid(/)', 'validateApiKey',
    function($ouid, $repoid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$repoid = htmlspecialchars($repoid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, repositorios.*
  FROM repositorios
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND idrepositorio='$repoid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and repo exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idrepositorio"])) {
		// Read data.
		$response['id'] = (int)$rs->campos["idrepositorio"];
		$response['name'] = $rs->campos["nombrerepositorio"];
		$response['description'] = $rs->campos["comentarios"];
		$response['ip'] = $rs->campos["ip"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

/**
 * @brief    List all images defined in an OU
 * @note     Route: /ous/:ouid/images, Method: GET
 * @param    int ouid  OU id.
 * @return   string    JSON array of all UO's image data
 */
$app->get('/ous/:ouid/images(/)', 'validateApiKey',
    function($ouid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, adm.idcentro AS ouid, imagenes.*
  FROM imagenes
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["ouid"])) {
		$response = Array();
		while (!$rs->EOF) {
			if (! is_null($rs->campos["idcentro"])) {
				$tmp = Array();
				$tmp['id'] = (int)$rs->campos["idimagen"];
				$tmp['name'] = $rs->campos["nombreca"];
				$tmp['inremotepc'] = ($rs->campos["inremotepc"] == 1);
				$tmp['ou']['id'] = (int)$ouid;
				array_push($response, $tmp);
			}
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
    }
);

/**
 * @brief    Get image data
 * @note     Route: /ous/:ouid/images/:imgid, Method: GET
 * @param    int ouid   OU id.
 * @param    int imgid  image id.
 * @return   string     JSON string with image parameters
 */
$app->get('/ous/:ouid/images/:imgid(/)', 'validateApiKey',
    function($ouid, $imgid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$imgid = htmlspecialchars($imgid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, imagenes.*, nombreso AS os
  FROM imagenes
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN perfilessoft USING(idperfilsoft)
  LEFT JOIN nombresos USING(idnombreso)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND idimagen='$imgid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and repo exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idimagen"])) {
		// Read data.
		$response['id'] = (int)$rs->campos["idimagen"];
		$response['name'] = $rs->campos["nombreca"];
		$response['description'] = $rs->campos["descripcion"];
		$response['comments'] = $rs->campos["comentarios"];
		$response['inremotepc'] = ($rs->campos["inremotepc"] == 1);
		$response['repo']['id'] = (int)$rs->campos["idrepositorio"];
		switch ($rs->campos["tipo"]) {
			// Image type.
			case 1:  $response['type'] = "monolithic"; break;
			case 2:  $response['type'] = "base"; break;
			case 3:  $response['type'] = "incremental";
				 $response['baseimg'] = $rs->campos["imagenid"];
				 $response['path'] = $rs->campos["ruta"];
				 break;
			default: $response['type'] = $rs->campos["tipo"];
		}
		if ($rs->campos["idordenador"] != 0) {
			// Source client data.
			$response['client']['id'] = (int)$rs->campos["idordenador"];
			$response['client']['disk'] = (int)$rs->campos["numdisk"];
			$response['client']['partition'] = (int)$rs->campos["numpar"];
			$response['creationdate'] = $rs->campos["fechacreacion"];
			$response['release'] = (int)$rs->campos["revision"];
			$response['os'] = $rs->campos["os"];
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Lista de softeare instalado en una imagen.
/**
 * @brief    List software installed in an image
 * @note     Route: /ous/:ouid/images/:imgid/software, Method: GET
 * @param    int ouid   OU id.
 * @param    int imgid  image id.
 * @return   string     JSON array with installed software
 */$app->get('/ous/:ouid/images/:imgid/software(/)', 'validateApiKey',
    function($ouid, $imgid) {
	global $userid;
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$imgid = htmlspecialchars($imgid);
	// Database query.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, imagenes.idimagen, imagenes.nombreca,
       nombresos.nombreso, softwares.descripcion
  FROM imagenes
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
  LEFT JOIN perfilessoft USING(idperfilsoft)
  LEFT JOIN nombresos USING(idnombreso)
  LEFT JOIN perfilessoft_softwares USING(idperfilsoft)
  LEFT JOIN softwares USING(idsoftware)
 WHERE adm.idusuario = '$userid'
   AND adm.idcentro='$ouid'
   AND imagenes.idimagen='$imgid'
 ORDER BY softwares.descripcion ASC;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	// Check if user is an UO admin and repo exists.
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idimagen"])) {
		$response['id'] = (int)$rs->campos["idimagen"];
		$response['name'] = $rs->campos["nombreca"];
		if (is_null($rs->campos["nombreso"])) {
			// Null object.
			$response['software'] = Array();
			jsonResponse(200, $response, JSON_FORCE_OBJECT);
		} else {
			// Read data.
			$response['software']['os'] = $rs->campos["nombreso"];
			//$response['software']['type'] = ...;  // OS type
			$response['software']['applications'] = Array();
			while (!$rs->EOF) {
				// Ignoring empty fields.
				if (!is_null($rs->campos["descripcion"])) {
					array_push($response['software']['applications'], $rs->campos["descripcion"]);
				}
				$rs->Siguiente();
			}
			jsonResponse(200, $response);
		}
	}
	$rs->Cerrar(); 
    }
);

