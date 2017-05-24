<?php
/**
 * @file    remotepc.php
 * @brief   OpenGnsys Server REST API consumed by UDS Server for Remote PC implementation.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1.0 - First version
 * @date    2017-02-01
 */


// REST routes.

/**
 * @brief    Reserve a client with an installed image and the older reservation time, then send a boot/reboot operation depending on its status.
 * @warning  If "lab" parameter is specified, then choose a client from this lab.
 * @note     Route: /ous/:ouid/images/:imageid/reserve, Method: POST
 * @param    integer ouid      OU identificator
 * @param    integer imageid   image identificator
 * @note     Input JSON message: {"labid":int_labid,"maxtime":int_hours}
 */
$app->post('/ous/:ouid/images/:imageid/reserve(/)', 'validateApiKey',
    function($ouid, $imageid) use ($app) {
	global $cmd;
	global $AMBITO_ORDENADORES;
	global $EJECUCION_COMANDO;
	global $ACCION_INICIADA;
	global $ACCION_SINRESULTADO;
	global $userid;
	$response = Array();
	$ogagent = Array();

	// Checking parameters. 
	try {
		if (!check_ids($ouid, $imageid)) {
			throw new Exception("Ids. must be positive integers");
		}
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$labid = isset($input->labid) ? $input->labid : '%';	// Default: no lab. filter
		$maxtime = isset($input->maxtime) ? $input->maxtime : 24;	// Default: 24 h.
		if (!filter_var($labid, FILTER_VALIDATE_INT, $opts) and $labid !== '%') {
			throw new Exception("Lab id. must be positive integer");
		}
		if (!filter_var($maxtime, FILTER_VALIDATE_INT, $opts)) {
			throw new Exception("Time must be positive integer (in hours)");
		}
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		jsonResponse(400, $response);
		$app->stop();
	}
	// Choose older not-reserved client with image installed and get ogAdmServer data.
	$cmd->texto = <<<EOD
SELECT adm.idadministradorcentro, entornos.ipserveradm, entornos.portserveradm,
       ordenadores.idordenador, ordenadores.nombreordenador, ordenadores.ip,
       ordenadores.mac, ordenadores.agentkey, ordenadores_particiones.numdisk,
       ordenadores_particiones.numpar, aulas.idaula, aulas.idcentro,
       remotepc.reserved
  FROM entornos, ordenadores
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 RIGHT JOIN ordenadores_particiones USING(idordenador)
 RIGHT JOIN imagenes USING(idimagen)
  LEFT JOIN remotepc ON remotepc.id=ordenadores.idordenador
 WHERE adm.idadministradorcentro = '$userid'
   AND aulas.idcentro = '$ouid' AND aulas.idaula LIKE '$labid' AND aulas.inremotepc = 1
   AND imagenes.idimagen = '$imageid' AND imagenes.inremotepc = 1
   AND (remotepc.reserved < NOW() OR ISNULL(reserved))
 ORDER BY remotepc.reserved ASC LIMIT 1;
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idadministradorcentro"]) and checkParameter($rs->campos["idordenador"])) {
		// Check if client is not reserved.
		if (is_null($rs->campos["reserved"])) {
			// Read query data.
			$serverip = $rs->campos["ipserveradm"];
			$serverport = $rs->campos["portserveradm"];
			$clntid = $rs->campos["idordenador"];
			$clntname = $rs->campos["name"];
			$clntip = $rs->campos["ip"];
			$clntmac = $rs->campos["mac"];
			$agentkey = $rs->campos["agentkey"];
			$disk = $rs->campos["numdisk"];
			$part = $rs->campos["numpar"];
			$labid = $rs->campos["idaula"];
			$ouid = $rs->campos["idcentro"];
			// Check client's status.
			$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/status";
			$result = multiRequest($ogagent);
			if (empty($result[$clntip]['data'])) {
				// Client is off, send a boot command to ogAdmServer.
				// TODO: if client is busy?????
				$reqframe = "nfn=Arrancar\r".
					    "ido=$clntid\r".
					    "iph=$clntip\r".
					    "mac=$clntmac\r".
					    "mar=1\r";
				sendCommand($serverip, $serverport, $reqframe, $values);
			} else {
				// Client is on, send a reboot command to its OGAgent.
				$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/reboot";
				$ogagent[$clntip]['header'] = Array("Authorization: ".$agentkey);
				$result = multiRequest($ogagent);
				// ... (check response)
				//if ($result[$clntip]['code'] != 200) {
				// ...
			}
			// DB Transaction: mark choosed client as reserved and
			// create an init session command into client's actions queue.
			$cmd->texto = "START TRANSACTION;";
			$cmd->Ejecutar();
			$timestamp = time();
			$cmd->texto = <<<EOD
INSERT INTO remotepc
   SET id='$clntid', reserved=NOW() + INTERVAL $maxtime HOUR, urllogin=NULL, urllogout=NULL
    ON DUPLICATE KEY UPDATE
       id=VALUES(id), reserved=VALUES(reserved),
       urllogin=VALUES(urllogin), urllogout=VALUES(urllogout);
EOD;
			$t1 = $cmd->Ejecutar();
			$cmd->texto = <<<EOD
INSERT INTO acciones
   SET tipoaccion=$EJECUCION_COMANDO,
       idtipoaccion=9,
       idcomando=9,
       parametros='nfn=IniciarSesion\rdsk=$disk\rpar=$part',
       descriaccion='RemotePC Session',
       idordenador=$clntid,
       ip='$clntip',
       sesion=$timestamp,
       fechahorareg=NOW(),
       estado=$ACCION_INICIADA,
       resultado=$ACCION_SINRESULTADO,
       ambito=$AMBITO_ORDENADORES,
       idambito=$clntid,
       restrambito='$clntip',
       idcentro=$ouid;
EOD;
			$t2 = $cmd->Ejecutar();
			if ($t1 and $t2) {
				// Commit transaction on success.
				$cmd->texto = "COMMIT;";
				$cmd->Ejecutar();
				// Send init session command if client is booted on ogLive.
				$reqframe = "nfn=IniciarSesion\r".
				   	    "ido=$clntid\r".
					    "iph=$clntip\r".
					    "dsk=$disk\r".
					    "par=$part\r";
				sendCommand($serverip, $serverport, $reqframe, $values);
				// Compose JSON response.
				$response['id'] = $clntid;
				$response['name'] = $clntname;
				$response['ip'] = $clntip;
				$response['mac'] = $clntmac;
				$response['lab']['id'] = $labid;
				$response['ou']['id'] = $ouid;
				jsonResponse(200, $response);
			} else{
				// Roll-back transaction on DB error.
				$cmd->texto = "ROLLBACK;";
				$cmd->Ejecutar();
				// Error message.
				$response["message"] = "Database error";
				jsonResponse(400, $response);
				$app->stop();
			}
       		} else {
			// Error message.
			$response["message"] = "Client is already reserved";
			jsonResponse(400, $response);
			$app->stop();
		}
       	}
	$rs->Cerrar();
    }
);


/**
 * @brief    Store UDS server URLs to resend some events recieved from OGAgent.
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/events, Method: POST
 * @param    string urlLogin   URL to redirect login notification.
 * @param    string urlLogout  URL to redirect logout notification.
 * @warning  Events parameters will be stored in a new "remotepc" table.
 */
$app->post('/ous/:ouid/labs/:labid/clients/:clntid/events', 'validateApiKey',
    function($ouid, $labid, $clntid) use ($app) {
	global $cmd;
	global $userid;
	$response = Array();

	// Checking parameters. 
	try {
		if (!check_ids($ouid, $labid, $clntid)) {
			throw new Exception("Ids. must be positive integers");
		}
		// Reading JSON parameters.
		$input = json_decode($app->request()->getBody());
		$urlLogin = htmlspecialchars($input->urlLogin);
		$urlLogout = htmlspecialchars($input->urlLogout);
		if (!filter_var($urlLogin, FILTER_VALIDATE_URL)) {
			throw new Exception("Must be a valid URL for login notification");
		}
		if (!filter_var($urlLogout, FILTER_VALIDATE_URL)) {
			throw new Exception("Must be a valid URL for logout notification");
		}
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		jsonResponse(400, $response);
		$app->stop();
	}

	// Select client data for UDS compatibility.
	$cmd->texto = <<<EOD
SELECT adm.idadministradorcentro, ordenadores.idordenador, remotepc.*
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 WHERE adm.idadministradorcentro = '$userid'
   AND idcentro = '$ouid' AND aulas.idaula ='$labid'
   AND ordenadores.idordenador = '$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idadministradorcentro"]) and checkParameter($rs->campos["idordenador"])) {
		// Check if client is reserved.
		if (! is_null($rs->campos["reserved"])) {
			// Updating DB if client is reserved.
			$cmd->CreaParametro("@urllogin", $urlLogin, 0);
			$cmd->CreaParametro("@urllogout", $urlLogout, 0);
			$cmd->texto = <<<EOD
UPDATE remotepc
   SET urllogin=@urllogin, urllogout=@urllogout
 WHERE id='$clntid';
EOD;
			if ($cmd->Ejecutar()) {
				// Confirm operation.
				jsonResponse(200, "");
        		} else {
				// Error message.
				$response["message"] = "Database error";
				jsonResponse(400, $response);
				$app->stop();
			}
        	} else {
			// Error message.
			$response["message"] = "Client is not reserved";
			jsonResponse(400, $response);
			$app->stop();
        	}
        }
	$rs->Cerrar();
    }
);


$app->post('/ous/:ouid/labs/:labid/clients/:clntid/session', 'validateApiKey',
    function($ouid, $imageid) use ($app) {
    }
);


$app->delete('/ous/:ouid/labs/:labid/clients/:clntid/unreserve', 'validateApiKey',
    function($ouid, $labid, $clntid) {
	global $cmd;
	global $userid;
	global $ACCION_INICIADA;
	$response = Array();
	$ogagent = Array();

	// Checking parameters. 
	try {
		if (!check_ids($ouid, $labid, $clntid)) {
			throw new Exception("Ids. must be positive integers");
		}
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		jsonResponse(400, $response);
		$app->stop();
	}

	// Select client data for UDS compatibility.
	$cmd->texto = <<<EOD
SELECT adm.idadministradorcentro, ordenadores.idordenador, ordenadores.ip, ordenadores.agentkey, remotepc.reserved
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 WHERE adm.idadministradorcentro = '$userid'
   AND idcentro = '$ouid' AND aulas.idaula ='$labid'
   AND ordenadores.idordenador = '$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idadministradorcentro"]) and checkParameter($rs->campos["idordenador"])) {
		// Check if client is reserved.
		if (! is_null($rs->campos["reserved"])) {
			// Read query data.
			$clntip = $rs->campos["ip"];
			$agentkey = $rs->campos["agentkey"];
			// DB Transaction: set reservation time to the past and
			// remove pending boot commands from client's actions queue.
			$cmd->texto = "START TRANSACTION;";
			$cmd->Ejecutar();
			$cmd->texto = <<<EOD
UPDATE remotepc
   SET reserved=NOW() - INTERVAL 1 SECOND, urllogin=NULL, urllogout=NULL
 WHERE id='$clntid';
EOD;
			$cmd->Ejecutar();
			$cmd->texto = <<<EOD
DELETE FROM acciones
 WHERE idordenador = '$clntid'
   AND descriaccion = 'RemotePC Session';
EOD;
			$cmd->Ejecutar();
			$cmd->texto = "COMMIT;";
			$cmd->Ejecutar();
			// Send a poweroff command to client's OGAgent.
			$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/poweroff";
			$ogagent[$clntip]['header'] = Array("Authorization: ".$agentkey);
			$result = multiRequest($ogagent);
			// ... (check response)
			//if ($result[$clntip]['code'] != 200) {
			// ...
			// Confirm operation.
			jsonResponse(200, "");
       		} else {
			// Error message.
			$response["message"] = "Client is not reserved";
			jsonResponse(400, $response);
		}
       	}
	$rs->Cerrar();
    }
);

?>
