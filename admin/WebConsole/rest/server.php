<?php
/**
 * @file    index.php
 * @brief   OpenGnsys REST API manager.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1
 * @date    2015-04-16
 */


// Auxiliar functions.

/**
 * @brief   Compose JSON response.
 * @param   int status      Status code for HTTP response.
 * @param   array response  Response data.
 * @return  string          JSON response.
 */
function jsonResponse($status, $response) {
	$app = \Slim\Slim::getInstance();
	// HTTP status code.
	$app->status($status);
	// Content-type HTTP header.
	$app->contentType('application/json');
	// JSON response.
	echo json_encode($response);
}

/**
 * @brief    Validate API key included in "Authorization" HTTP header.
 * @return   JSON response on error.
 */
function validateApiKey() {
	global $cmd;
	global $userid;
	$response = array();

	// Read Authorization HTTP header.
	$headers = apache_request_headers();
	if (! empty($headers['Authorization'])) {
		// Assign user id. that match this key to global variable.
		$apikey = htmlspecialchars($headers['Authorization']);
		$cmd->texto = "SELECT idusuario
				 FROM usuarios
				WHERE apikey='$apikey'";
		$rs=new Recordset;
		$rs->Comando=&$cmd;
		if ($rs->Abrir()) {
			$rs->Primero();
			if (!$rs->EOF){
				// Fetch user id.
				$userid = $rs->campos["idusuario"];
			} else {
                		// Credentials error.
                		$response['message'] = 'Login failed. Incorrect credentials';
				jsonResponse(401, $response);
				$app->stop();
			}
			$rs->Cerrar();
		} else {
			// Access error.
			$response['message'] = "An error occurred, please try again";
			jsonResponse(500, $response);
		}
	} else {
		// Error: missing API key.
		$response['message'] = 'Missing API key';
		jsonResponse(400, $response);
		$app->stop();
	}
}

/**
 * @brief    Check if parameter is set and print error messages if empty.
 * @param    string param    Parameter to check.
 * @return   boolean         "false" if parameter is null, otherwise "true".
 */
function checkParameter($param) {
	if (isset($param)) {
		return true;
	} else {
		// Print error message.
		$response['message'] = 'Parameter not found';
		jsonResponse(400, $response);
		return false;
	}
}

/**
 * @brief    Check if user is administrator and print error messages if not.
 * @param    int adminid   Administrator id.
 * @return   boolean       "true" if admin id. is equals to global user id., otherwise "false".
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
 * @#fn      sendCommand($serverip, $serverport, $reqframe, &$values)
 * @brief    Send a command to an OpenGnsys ogAdmServer and get request.
 * @param    string serverip    Server IP address.
 * @param    string serverport  Server port.
 * @param    string reqframe    Request frame (field's separator is "\r").
 * @param    array values       Response values (out parameter).
 * @return   boolean            "true" if success, otherwise "false".
 */
function sendCommand($serverip, $serverport, $reqframe, &$values) {
	global $LONCABECERA;
	global $LONHEXPRM;

	// Connect to server.
	$respvalues = "";
	$connect = new SockHidra($serverip, $serverport);
	if ($connect->conectar()) {
		// Send request frame to server.
		$result = $connect->envia_peticion($reqframe);
		if ($result) {
			// Parse request frame.
			$respframe = $connect->recibe_respuesta();
			$connect->desconectar();
			$paramlen = hexdec(substr($respframe, $LONCABECERA, $LONHEXPRM));
			$params = substr($respframe, $LONCABECERA+$LONHEXPRM, $paramlen);
			// Fetch values and return result.
			$values = extrae_parametros($params, "\r", '=');
			return ($values);
		} else {
			// Return with error.
			return (false);
		}
	} else {
		// Return with error.
		return (false);
	}
}

// Define REST routes.

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

	$response = array();

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

	// Check parameters. 
	if (! empty($user) and ! empty($pass)) {
		// Database query.
		$cmd->texto = "SELECT idusuario, apikey
			 	 FROM usuarios
				WHERE usuario='$user' AND pasguor='$pass'";
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
 * @param    no
 * @return   JSON array with ouid, ouname for every defined OU
 */
$app->get('/ous', 'validateApiKey', function() {
	global $cmd;

	$cmd->texto = "SELECT * FROM centros";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$response['ous'] = array();
	$rs->Primero();
	while (!$rs->EOF) {
		$tmp = array();
		$tmp['ouid'] = $rs->campos["idcentro"];
		$tmp['ouname'] = $rs->campos["nombrecentro"];
		array_push($response['ous'], $tmp);
		$rs->Siguiente();
	}
	$rs->Cerrar(); 
	jsonResponse(200, $response);
} 
);

/**
 * @brief    Get Organizational Unit data
 * @note     Route: /ous/id, Method: GET
 * @param    id      OU id.
 * @return   JSON string with ouid, ouname and description
 */
$app->get('/ous/:ouid', 'validateApiKey',
    function($ouid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$cmd->texto = "SELECT * FROM centros WHERE idcentro='$ouid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	if (checkParameter($rs->campos["nombrecentro"])) {
		$response['id'] = $ouid;
		$response['name'] = $rs->campos["nombrecentro"];
		$response['description'] = $rs->campos["comentarios"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Listar aulas de una OU.
$app->get('/ous/:ouid/labs', 'validateApiKey',
    function($ouid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Listar las salas de la UO si el usuario de la apikey es su admin.
	$cmd->texto = <<<EOD
SELECT aulas.*, adm.idadministradorcentro
  FROM aulas
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 WHERE idcentro='$ouid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error al abrir recordset
	// Comprobar que exista la UO y que el usuario sea su administrador.
	$rs->Primero();
	if (checkParameter($rs->campos["idcentro"]) and checkAdmin($rs->campos["idadministradorcentro"])) {
		$response['ouid'] = $ouid;
		$response['labs'] = array();
		while (!$rs->EOF) {
			$tmp = array();
			$tmp['id'] = $rs->campos["idaula"];
			$tmp['name'] = $rs->campos["nombreaula"];
			$tmp['inremotepc'] = $rs->campos["inremotepc"]==0 ? false: true;
			array_push($response['labs'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Obtener datos de un aula.
// Alternativa: $app->get('/lab/:labid', 'validateApiKey',
//                  function($labid) {
$app->get('/ous/:ouid/labs/:labid', 'validateApiKey',
    function($ouid, $labid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$cmd->texto = <<<EOD
SELECT COUNT(idordenador) AS defclients, aulas.*, adm.idadministradorcentro
  FROM aulas
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
  LEFT JOIN ordenadores USING(idaula)
 WHERE idcentro='$ouid'
   AND idaula='$labid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	if (checkParameter($rs->campos["idaula"]) and checkAdmin($rs->campos["idadministradorcentro"])) {
		$response['id'] = $rs->campos["idaula"];
		$response['name'] = $rs->campos["nombreaula"];
		$response['location'] = $rs->campos["ubicacion"];
		$response['description'] = $rs->campos["comentarios"];
		$response['inremotepc'] = $rs->campos["inremotepc"]==0 ? false: true;
		$response['capacity'] = $rs->campos["puestos"];
		$response['defclients'] = $rs->campos["defclients"];
		$response['projector'] = $rs->campos["cagnon"]==0 ? false: true;
		$response['board'] = $rs->campos["pizarra"]==0 ? false: true;
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
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Listar clientes de un aula.
$app->get('/ous/:ouid/labs/:labid/clients', 'validateApiKey',
    function($ouid, $labid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	// Listar los clientes del aula si el usuario de la apikey es admin de su UO.
	// Consulta temporal,
	$cmd->texto = "SELECT * FROM ordenadores WHERE idaula=$labid;";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Recordset open error.
	$rs->Primero();
	if (checkParameter($rs->campos["idaula"])) {
		$response['ouid'] = $ouid;
		$response['labid'] = $labid;
		$response['clients'] = array();
		while (!$rs->EOF) {
			$tmp = array();
			$tmp['id'] = $rs->campos["idordenador"];
			$tmp['name'] = $rs->campos["nombreordenador"];
			array_push($response['clients'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Obtener datos de un cliente.
$app->get('/ous/:ouid/labs/:labid/clients/:clntid', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	$cmd->texto = "SELECT * FROM ordenadores WHERE idordenador='$clntid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
//	if ($labid != $rs->campos["idaula"]) ...
	if (checkParameter($rs->campos["idordenador"])) {
		$response['id'] = $rs->campos["idordenador"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['serialno'] = $rs->campos["numserie"];
		$response['netiface'] = $rs->campos["netiface"];
		$response['netdriver'] = $rs->campos["netdriver"];
		$response['mac'] = $rs->campos["mac"];
		$response['ip'] = $rs->campos["ip"];
		$response['netmask'] = $rs->campos["mascara"];
		$response['routerip'] = $rs->campos["router"];
		$response['repoid'] = $rs->campos["idrepositorio"];
		//$response['hardprofid'] = $rs->campos["idperfilhard"];
		//$response['menuid'] = $rs->campos["idmenu"];
		//$response['validation'] = $rs->campos["arranque"]==0 ? false: true;
		//$response['boottype'] = $rs->campos["arranque"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Obtener la configuración de hardware de un cliente.
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/hardware', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	$cmd->texto = <<<EOD
SELECT ordenadores.idordenador, ordenadores.nombreordenador,
       tipohardwares.nemonico, hardwares.descripcion
  FROM perfileshard
 RIGHT JOIN ordenadores USING(idperfilhard)
  JOIN perfileshard_hardwares USING(idperfilhard)
  JOIN hardwares ON perfileshard_hardwares.idhardware=hardwares.idhardware
  JOIN tipohardwares ON tipohardwares.idtipohardware=hardwares.idtipohardware
 WHERE ordenadores.idordenador='$clntid'
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
//	if ($ouid != $rs->campos["idcentro"]) ...
//	if ($labid != $rs->campos["idaula"]) ...
	if (checkParameter($rs->campos["idordenador"])) {
		$response['id'] = $rs->campos["idordenador"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['hardware'] = array();
		while (!$rs->EOF) {
			$tmp = array();
			$tmp['type'] = $rs->campos["nemonico"];
			$tmp['description'] = $rs->campos["descripcion"];
			array_push($response['hardware'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Obtener datos de configuración de discos del cliente.
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/diskcfg', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);
	$cmd->texto = <<<EOD
SELECT ordenadores.idordenador AS clientid, ordenadores.nombreordenador,
       ordenadores_particiones.*, tipospar.tipopar,
       sistemasficheros.nemonico, nombresos.nombreso, imagenes.nombreca
  FROM ordenadores_particiones
 RIGHT JOIN ordenadores USING(idordenador)
  LEFT JOIN tipospar USING(codpar)
  LEFT JOIN sistemasficheros USING(idsistemafichero)
  LEFT JOIN nombresos USING(idnombreso)
  LEFT JOIN imagenes USING(idimagen)
 WHERE ordenadores.idordenador='$clntid'
 ORDER BY numdisk ASC, numpar ASC;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
//	if ($labid != $rs->campos["idaula"]) ...
	if (checkParameter($rs->campos["clientid"])) {
		$response['id'] = $rs->campos["clientid"];
		$response['name'] = $rs->campos["nombreordenador"];
		$response['diskcfg'] = array();
		while (!$rs->EOF) {
			if ($rs->campos["numdisk"] == 0) {
				$rs->Siguiente();
				continue;
			}
			$tmp = array();
			if ($rs->campos["numpar"] == 0) {
				$tmp['disk'] = $rs->campos["numdisk"];
				switch ($rs->campos["codpar"]) {
					case 1:  $tmp['parttable'] = "MSDOS"; break;
					case 2:  $tmp['parttable'] = "GPT"; break;
					case 3:  $tmp['parttable'] = "LVM"; break;
					case 4:  $tmp['parttable'] = "ZPOOL"; break;
					default: $tmp['parttable'] = $rs->campos["codpar"];
				}
				$tmp['size'] = $rs->campos["tamano"];
			} else {
				$tmp['partition'] = $rs->campos["numpar"];
				$tmp['parttype'] = $rs->campos["tipopar"];
				$tmp['filesystem'] = $rs->campos["nemonico"];
				$tmp['size'] = $rs->campos["tamano"];
				$tmp['usage'] = $rs->campos["uso"];
				if ($rs->campos["nombreso"] != null) {
					$tmp['os'] = $rs->campos["nombreso"];
					$tmp['imageid'] = $rs->campos["idimagen"];
					$tmp['deploydate'] = $rs->campos["fechadespliegue"];
					// Comprobar si la imagen está actualizada.
					$tmp['updated'] = ($rs->campos["difimagen"]>0 ? "false" : "true");
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

// Obtener estado de ejecución del cliente.
$app->get('/ous/:ouid/labs/:labid/clients/:clntid/status', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $cmd;
	global $LONCABECERA;
	global $LONHEXPRM;

	// Parameters.
	$ouid = htmlspecialchars($ouid);
	$labid = htmlspecialchars($labid);
	$clntid = htmlspecialchars($clntid);

	// Database query.
	$cmd->texto = <<<EOD
SELECT serv.ipserveradm, serv.portserveradm, clnt.idordenador, clnt.ip
  FROM entornos AS serv, ordenadores AS clnt
 WHERE clnt.idordenador='$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	if (checkParameter($rs->campos["idordenador"])) {
		//
		// Probar primero el estado de OGAgent y luego de ogAdmClient
		//

		$serverip = $rs->campos["ipserveradm"];
		$serverport = $rs->campos["portserveradm"];
		$clientid = $rs->campos["idordenador"];
		$clientip = $rs->campos["ip"];

		// Connect to reset client's status.
		$reqframe = "nfn=Sondeo\r".
			    "ido=$clientid\r".
			    "iph=$clientip\r";
		$result = sendCommand($serverip, $serverport, $reqframe, $values);

		// Connect to fetch client's status.
		// Asuming client is off by default.
		$values["tso"]="OFF";
		// Iterate to check client's status.
		// Exit if status!=OFF or end iterations (status=OFF).
		$maxIter = 30;
		for ($i=1; $i<$maxIter and preg_match('/OFF/', $values["tso"]); $i++) {
			// Connect to check status.
			$reqframe = "nfn=respuestaSondeo\r".
				    "ido=$clientid\r".
				    "iph=$clientip\r";
			$result = sendCommand($serverip, $serverport, $reqframe, $values);
			// Wait until next checking (0.1 ms).
			usleep(100000);
		}

		// Parse status response.
		if ($result) {
			// Check status type.
			if (checkParameter($values["tso"])) {
				// Compose JSON response.
				$response['id'] = $clientid;
				$response['ip'] = $clientip;
				$stat = array();
				preg_match('/\/[A-Z]*;/', $values["tso"], $stat);
				// Check if data exists.
				if (empty($stat[0])) {
					$response['status'] = "nodata";
				} else {
					// Status mapping.
					$status = array('OFF'=>"off",
							'INI'=>"initializing",
							'OPG'=>"ogclient",
							'BSY'=>"busy",
							'LNX'=>"linux",
							'WIN'=>"windows");
					$response['status'] = $status[substr($stat[0], 1, 3)];
					if (empty($response['status'])) {
						$response['status'] = "unknown";
					}
				}
				jsonResponse(200, $response);
			}
		} else {
			// Access error.
			$response['message'] = "Cannot access to OpenGnsys server";
			jsonResponse(500, $response);
		}
	}
	$rs->Cerrar(); 
    }
);


// Listar repositorios.
$app->get('/ous/:ouid/repos', 'validateApiKey',
    function($ouid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Listar las salas de la UO si el usuario de la apikey es su admin.
	// Consulta temporal,
	$cmd->texto = "SELECT * FROM repositorios WHERE idcentro='$ouid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	// Comprobar que exista la UO.
	if (checkParameter($rs->campos["idcentro"])) {
		$response['ouid'] = $ouid;
		$response['repos'] = array();
		while (!$rs->EOF) {
			$tmp = array();
			$tmp['id'] = $rs->campos["idrepositorio"];
			$tmp['name'] = $rs->campos["nombrerepositorio"];
			array_push($response['repos'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Obtener datos de un repositorio.
$app->get('/ous/:ouid/repos/:repoid', 'validateApiKey',
    function($ouid, $repoid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$repoid = htmlspecialchars($repoid);
	$cmd->texto = "SELECT * FROM repositorios WHERE idrepositorio='$repoid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	// Comprobar que exista el repositorio.
	if (checkParameter($rs->campos["idrepositorio"])) {
		$response['id'] = $rs->campos["idrepositorio"];
		$response['name'] = $rs->campos["nombrerepositorio"];
		$response['description'] = $rs->campos["comentarios"];
		$response['ipaddress'] = $rs->campos["ip"];
		//$response['port'] = $rs->campos["puertorepo"];
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Listar imágenes.
$app->get('/ous/:ouid/images', 'validateApiKey',
    function($ouid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	// Listar las salas de la UO si el usuario de la apikey es su admin.
	// Consulta temporal,
	$cmd->texto = "SELECT * FROM imagenes WHERE idcentro='$ouid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	// Comprobar que exista la UO.
	$rs->Primero();
	if (checkParameter($rs->campos["idcentro"])) {
		$response['ouid'] = $ouid;
		$response['images'] = array();
		while (!$rs->EOF) {
			$tmp = array();
			$tmp['id'] = $rs->campos["idimagen"];
			$tmp['name'] = $rs->campos["nombreca"];
			$tmp['inremotepc'] = $rs->campos["inremotepc"]==0 ? false: true;
			array_push($response['images'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
    }
);

// Obtener datos de una imagen.
$app->get('/ous/:ouid/images/:imgid', 'validateApiKey',
    function($ouid, $imgid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$imgid = htmlspecialchars($imgid);
	$cmd->texto = "SELECT * FROM imagenes WHERE idimagen='$imgid';";
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero();
	// Comprobar que exista el repositorio.
	if (checkParameter($rs->campos["idimagen"])) {
		$response['id'] = $rs->campos["idimagen"];
		$response['name'] = $rs->campos["nombreca"];
		$response['description'] = $rs->campos["descripcion"];
		$response['comments'] = $rs->campos["comentarios"];
		$response['inremotepc'] = $rs->campos["inremotepc"]==0 ? false: true;
		$response['repoid'] = $rs->campos["idrepositorio"];
		switch ($rs->campos["tipo"]) {
			case 1:  $response['type'] = "monolithic"; break;
			case 2:  $response['type'] = "base"; break;
			case 3:  $response['type'] = "incremental";
				 $response['baseimg'] = $rs->campos["imagenid"];
				 $response['path'] = $rs->campos["ruta"];
				 break;
			default: $response['type'] = $rs->campos["tipo"];
		}
		if ($rs->campos["idordenador"] != 0) {
			$response['clientid'] = $rs->campos["idordenador"];
			$response['disk'] = $rs->campos["numdisk"];
			$response['partition'] = $rs->campos["numpar"];
			$response['creationdate'] = $rs->campos["fechacreacion"];
			$response['release'] = $rs->campos["revision"];
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Lista de softeare instalado en una imagen.
$app->get('/ous/:ouid/images/:imgid/software', 'validateApiKey',
    function($ouid, $imgid) {
	global $cmd;

	$ouid = htmlspecialchars($ouid);
	$imgid = htmlspecialchars($imgid);
	$cmd->texto = <<<EOD
SELECT imagenes.idimagen, imagenes.nombreca, nombresos.nombreso, softwares.descripcion
  FROM perfilessoft
 RIGHT JOIN imagenes USING(idperfilsoft)
  LEFT JOIN nombresos USING(idnombreso)
  LEFT JOIN perfilessoft_softwares USING(idperfilsoft)
  LEFT JOIN softwares USING(idsoftware)
 WHERE imagenes.idimagen='$imgid'
 ORDER BY softwares.descripcion ASC;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error al abrir recordset
	$rs->Primero();
	// Comprobar que exista el repositorio.
	if (checkParameter($rs->campos["idimagen"])) {
		$response['id'] = $rs->campos["idimagen"];
		$response['name'] = $rs->campos["nombreca"];
		$response['os'] = $rs->campos["nombreso"];
		//$response['ostype'] = Tipo de SO (agrupar en array con nombre SO).
		$response['software'] = array();
		while (!$rs->EOF) {
			if ($rs->campos["descripcion"] == null) {
				$rs->Siguiente();
				continue;
			}
			$tmp = array();
			$tmp['application'] = $rs->campos["descripcion"];
			array_push($response['software'], $tmp);
			$rs->Siguiente();
		}
		jsonResponse(200, $response);
	}
	$rs->Cerrar(); 
    }
);

// Arrancar un ordenador con una imagen instalada, elegido al azar.
$app->get('/ous/:id1/images/:id2/boot', 'validateApiKey',
    function($ouid, $imageid) {
	global $cmd;
	global $AMBITO_ORDENADORES;
	global $EJECUCION_COMANDO;
	global $ACCION_INICIADA;
	global $ACCION_SINRESULTADO;

	// Pparameters.
	$ouid = htmlspecialchars($ouid);
	$imegeid = htmlspecialchars($imageid);
	// Boot 1 client.
	$nclients = 1;

	// Query: server data and all clients' boot data availabe for Remote PC with this image installed (random order).
	$cmd->texto = <<<EOD
SELECT s.ipserveradm, s.portserveradm,
       c.idordenador, c.ip, c.mac, p.numdisk, p.numpar
  FROM entornos AS s, ordenadores AS c
  JOIN aulas USING(idaula)
  JOIN centros USING(idcentro)
  JOIN ordenadores_particiones AS p USING(idordenador)
  JOIN imagenes USING(idimagen)
 WHERE centros.idcentro='$ouid'
   AND aulas.inremotepc=1
   AND imagenes.idimagen='$imageid'
   AND imagenes.inremotepc=1
 ORDER BY RAND();
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);	// Error oppening recordset.
	$rs->Primero();
	if (checkParameter($rs->campos["ipserveradm"])) {

		$response['imageid'] = $imageid;
		$response['sendto'] = array();

		// AVISO: Procesar datos del servidor (solo 1er registro).

		$serverip = $rs->campos["ipserveradm"];
		$serverport = $rs->campos["portserveradm"];

		// AVISO: Procesar datos de los clientes.

		$clientid = array();
		$clientip = array();
		$clientmac = array();
		$clientdisk = array();
		$clientpart = array();
		while (!$rs->EOF) {
			array_push($clientid, $rs->campos["idordenador"]);
			array_push($clientip, $rs->campos["ip"]);
			array_push($clientmac, $rs->campos["mac"]);
			array_push($clientdisk, $rs->campos["numdisk"]);
			array_push($clientpart, $rs->campos["numpar"]);
			$rs->Siguiente();
		}
		$rs->Cerrar(); 

		// AVISO: consultar el estado de todos los clientes y
		//        quitar aquellos que no tengan "OFF", "OPG" o ""
		//        (estudiar si incluir los "BSY")

		// Reset clients' status.
		$reqframe = "nfn=Sondeo\r".
			    "ido=".implode(',', $clientid)."\r".
			    "iph=".implode(';', $clientip)."\r";
		sendCommand($serverip, $serverport, $reqframe, $values);
		 // Wait to get response.
		sleep(3);
		// Connect to check status.
		$reqframe = "nfn=respuestaSondeo\r".
			    "ido=".implode(',', $clientid)."\r".
			    "iph=".implode(';', $clientip)."\r";
		sendCommand($serverip, $serverport, $reqframe, $values);
		// Check status type.
		if (isset($values["tso"])) {
			preg_match_all('/[A-Z]{3}/', $values["tso"], $stat);
		}
		if (isset($stat[0])) {
			for ($i=0; $i<sizeof($stat[0]); $i++) {
				if (! in_array($stat[0][$i], array("OFF", "OPG", ""))) {
					unset($clientid[$i]);
					unset($clientip[$i]);
					unset($clientmac[$i]);
					unset($clientdisk[$i]);
					unset($clientpart[$i]);
				}
			}
		}

		// AVISO: el siguiente código inicia un único cliente.
		//        Para iniciar varios: 
		//	  - id. clientes separados por carácter ','.
		//	  - IP clientes separadas por carácter ';'
		//	  - MAC clientes separadas por carácter ';'

		// Executing boot command.
		$reqframe = "nfn=Arrancar\r".
			    "ido=".implode(',', $clientid)."\r".
			    "iph=".implode(';', $clientip)."\r".
			    "mac=".implode(';', $clientmac)."\r".
			    "mar=1\r";
echo "req=".str_replace("\r"," ",$reqframe).".\n";
		sendCommand($serverip, $serverport, $reqframe, $values);
		if ($values["res"]) {
print_r($values);
			$tmp = array();
			for ($i=0, $boot=0; $i<sizeof($clientid) and $boot!=1; $i++) {
				$reqframe = "nfn=IniciarSesion\r".
					    "ido=".$clientid[$i]."\r".
					    "iph=".$clientip[$i]."\r".
					    "dsk=".$clientdisk[$i]."\r".
					    "par=".$clientpart[$i]."\r";
echo "i=$i: req=".str_replace("\r"," ",$reqframe).".\n";
				sendCommand($serverip, $serverport, $reqframe, $values);
				if ($values["res"]) {

					// AVISO: incluir comando Iniciar sesión en cola de acciones.
					$timestamp=time();
					$cmd->texto = <<<EOD
INSERT INTO acciones
	SET tipoaccion=$EJECUCION_COMANDO,
	    idtipoaccion=9,
	    idcomando=9,
	    parametros='nfn=IniciarSesion\rdsk=$clientdisk[$i]\rpar=$clientpart[$i]',
	    descriaccion='RemotePC Session',
	    idordenador=$clientid[$i],
	    ip='$clientip[$i]',
	    sesion=$timestamp,
	    fechahorareg=NOW(),
	    estado=$ACCION_INICIADA,
	    resultado=$ACCION_SINRESULTADO,
	    ambito=$AMBITO_ORDENADORES,
	    idambito=$clientid[$i],
	    restrambito='$clientip[$i]',
	    idcentro=$ouid;
EOD;
					$result = $cmd->Ejecutar();
					if ($result) {
						$tmp['id'] = $clientid[$i];
						$tmp['ip'] = $clientip[$i];
						$tmp['mac'] = $clientmac[$i];
						array_push($response['sendto'], $tmp);
						$boot = 1;
					}
				}
			}
		}
		jsonResponse(200, $response);
	}
    }
);
// Alternativa como método GET.
//$app->get('/ous/:id1/images/:id2/boot/:number', 'validateApiKey',
//    function($ouid, $imageid, $number) {
//
//   }
//);

?>

