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

// OGAgent sessions log file.
define('REMOTEPC_LOGFILE', '/opt/opengnsys/log/remotepc.log');

// Function to write a line into log file.
function writeRemotepcLog($message = "") {
        file_put_contents(REMOTEPC_LOGFILE, date(DATE_ISO8601).": $message\n", FILE_APPEND);
}


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
	global $ACCION_FINALIZADA;
	global $ACCION_SINRESULTADO;
	global $ACCION_FALLIDA;
	global $userid;
	$response = Array();
	$ogagent = Array();
	$repo = Array();

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Init.");
	// Checking parameters. 
	try {
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT']))) {
			throw new Exception("Bad agent: sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		if (!checkIds($ouid, $imageid)) {
			throw new Exception("Ids. must be positive integers");
		}
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		// Default: no lab. filter.
		if (isset($input->labid)) {
			$labid = $input->labid != "0" ? $input->labid : '%';
		} else {
			$labid = '%';
		}
		$maxtime = isset($input->maxtime) ? $input->maxtime : 24;	// Default: 24 h.
		$opts = Array('options' => Array('min_range' => 1));	// Check for int>0
		if (filter_var($labid, FILTER_VALIDATE_INT, $opts) === false and $labid !== '%') {
			throw new Exception("Lab id. must be positive integer");
		}
		if (filter_var($maxtime, FILTER_VALIDATE_INT, $opts) === false) {
			throw new Exception("Time must be positive integer (in hours)");
		}
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": ERROR: ".$response["message"].".");
		jsonResponse(400, $response);
		$app->stop();
	}

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Parameters: labid=$labid, maxtime=$maxtime");
	// Choose older not-reserved client with image installed and get ogAdmServer data.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador, ordenadores.nombreordenador, ordenadores.ip,
       ordenadores.mac, ordenadores.agentkey, par.numdisk, par.numpar,
       aulas.idaula, aulas.idcentro, repo.ip AS repoip, repo.apikey AS repokey
  FROM ordenadores
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 RIGHT JOIN ordenadores_particiones AS par USING(idordenador)
 RIGHT JOIN imagenes USING(idimagen)
 RIGHT JOIN repositorios AS repo ON repo.idrepositorio = ordenadores.idrepositorio
  LEFT JOIN remotepc ON remotepc.id=ordenadores.idordenador
 WHERE adm.idusuario = '$userid'
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
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idordenador"])) {
		// Read query data.
		$clntid = $rs->campos["idordenador"];
		$clntname = $rs->campos["nombreordenador"];
		$clntip = $rs->campos["ip"];
		$clntmac = $rs->campos["mac"];
		$agentkey = $rs->campos["agentkey"];
		$disk = $rs->campos["numdisk"];
		$part = $rs->campos["numpar"];
		$labid = $rs->campos["idaula"];
		$ouid = $rs->campos["idcentro"];
		$repoip = $rs->campos["repoip"];
		$repokey = $rs->campos["repokey"];
		// Check client's status.
		$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/status";
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": OGAgent status, url=".$ogagent[$clntip]['url'].".");
		$result = multiRequest($ogagent);
		if (empty($result[$clntip]['data'])) {
			// Client is off, send WOL command to ogAdmServer.
			// TODO: if client is busy?????
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Send boot command through ogAdmServer: iph=$clntip,mac=$clntmac.");
			wol(1, [$clntmac], [$clntip]);
			// Send WOL command to client repository.
			$repo[$repoip]['url'] = "https://$repoip/opengnsys/rest/repository/poweron";
			$repo[$repoip]['header'] = Array("Authorization: ".$repokey);
			$repo[$repoip]['post'] = '{"macs": ["'.$clntmac.'"], "ips": ["'.$clntip.'"]}';
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Send Boot command through repo: repo=$repoip, ip=$clntip,mac=$clntmac.");
			$result = multiRequest($repo);
			// ... (check response)
			//if ($result[$repoip]['code'] != 200) {
			// ...
		} else {
			// Client is on, send a rieboot command to its OGAgent.
			$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/reboot";
			$ogagent[$clntip]['header'] = Array("Authorization: ".$agentkey);
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": OGAgent reboot, url=".$ogagent[$clntip]['url'].".");
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
		// Create event to remove reservation on timeout (15 min.).
		$timeout = "15 MINUTE";
		$cmd->texto = <<<EOD
CREATE EVENT e_timeout_$clntid
       ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL $timeout DO
       BEGIN
	    SET @clntid = NULL;
	    UPDATE acciones
	       SET estado = $ACCION_FINALIZADA, resultado = $ACCION_FALLIDA,
		   descrinotificacion = 'Timeout'
	     WHERE descriaccion = 'RemotePC Session' AND estado = $ACCION_INICIADA
	       AND idordenador = (SELECT @clntid := '$clntid');
	    IF @clntid IS NOT NULL THEN
	       UPDATE remotepc
		  SET reserved=NOW() - INTERVAL 1 SECOND, urllogin=NULL, urllogout=NULL
		WHERE id = @clntid;
	       DELETE FROM acciones
		WHERE idordenador = @clntid
		  AND descriaccion = 'RemotePC Session'
		  AND descrinotificacion = 'Timeout';
	    END IF;
       END
EOD;
		$t3 = $cmd->Ejecutar();
		if ($t1 and $t2 and $t3) {
			// Commit transaction on success.
			$cmd->texto = "COMMIT;";
			$cmd->Ejecutar();
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": DB tables and events updated, clntid=$clntid.");
			// Send init session command if client is booted on ogLive.
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Send Init Session command to ogAdmClient, ido=$clntid,iph=$clntip,dsk=$disk,par=$part.");
			session($clntip, "$disk\r$part");
			// Compose JSON response.
			$response['id'] = (int)$clntid;
			$response['name'] = $clntname;
			$response['ip'] = $clntip;
			$response['mac'] = $clntmac;
			$response['lab']['id'] = $labid;
			$response['ou']['id'] = (int)$ouid;
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Response, ".var_export($response,true).".");
			jsonResponse(200, $response);
		} else {
			// Roll-back transaction on DB error.
			$cmd->texto = "ROLLBACK;";
			$cmd->Ejecutar();
			// Error message.
			$response["message"] = "Database error: $t1, $t2, $t3";
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": ERROR: ".$response["message"].".");
			jsonResponse(400, $response);
		}
       	} else {
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": UNASSIGNED");
       	}
	$rs->Cerrar();
	$app->stop();
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

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Init.");
	// Checking parameters. 
	try {
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT']))) {
			throw new Exception("Bad agent: sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		if (!checkIds($ouid, $labid, $clntid)) {
			throw new Exception("Ids. must be positive integers");
		}
		// Reading JSON parameters.
		$input = json_decode($app->request()->getBody());
		$urlLogin = htmlspecialchars($input->urlLogin);
		$urlLogout = htmlspecialchars($input->urlLogout);
		if (filter_var($urlLogin, FILTER_VALIDATE_URL) === false) {
			throw new Exception("Must be a valid URL for login notification");
		}
		if (filter_var($urlLogout, FILTER_VALIDATE_URL) === false) {
			throw new Exception("Must be a valid URL for logout notification");
		}
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": ERROR: ".$response["message"].".");
		jsonResponse(400, $response);
		$app->stop();
	}

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Parameters: urlLogin=$urlLogin, urlLogout=$urlLogout");
	// Select client data for UDS compatibility.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador, remotepc.*
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 WHERE adm.idusuario = '$userid'
   AND idcentro = '$ouid' AND aulas.idaula ='$labid'
   AND ordenadores.idordenador = '$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idordenador"])) {
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
				$response = "";
				jsonResponse(200, $response);
        		} else {
				// Error message.
				$response["message"] = "Database error";
				jsonResponse(400, $response);
			}
        	} else {
			// Error message.
			$response["message"] = "Client is not reserved";
			jsonResponse(400, $response);
        	}
        }
	$rs->Cerrar();
	$app->stop();
    }
);


/*
 * @brief    Store session time (in sec).
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/session, Method: POST
 * @param    int    deadLine   maximum session time, in seconds (0 for unlimited)
 * @warning  Parameters will be stored in a new "remotepc" table.
 */
$app->post('/ous/:ouid/labs/:labid/clients/:clntid/session', 'validateApiKey',
    function($ouid, $labid, $clntid) use ($app) {
	global $cmd;
	global $userid;
	$response = Array();

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Init.");
	// Checking parameters. 
	try {
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT']))) {
			throw new Exception("Bad agent: sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		if (!checkIds($ouid, $labid, $clntid)) {
			throw new Exception("Ids. must be positive integers");
		}
		// Reading JSON parameters.
		$input = json_decode($app->request()->getBody());
		$deadLine = $input->deadLine;
		if (filter_var($deadLine, FILTER_VALIDATE_INT) === false) {
			throw new Exception("Deadline must be integer");
		}
		if ($deadLine < 0) {
			throw new Exception("Resource unavailable");
		}
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": ERROR: ".$response["message"].".");
		jsonResponse(400, $response);
		$app->stop();
	}

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Parameters: deadLine=$deadLine");
	// Get client's data.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador, remotepc.*
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 WHERE adm.idusuario = '$userid'
   AND aulas.idcentro = '$ouid' AND aulas.idaula = '$labid'
   AND ordenadores.idordenador = '$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idordenador"])) {
		// Check if client is reserved.
		if (! is_null($rs->campos["urllogin"])) {
			// Read query data.
			$clntid = $rs->campos["idordenador"];
			# Removing previous commands from OGAgent operations queue.
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Updating database.");
			$cmd->texto = <<<EOD
DELETE FROM ogagent_queue
 WHERE clientid = '$clntid' AND operation IN ('popup-10', 'popup-5', 'poweroff');
EOD;
			$cmd->Ejecutar();
			# Add new commands to OGAgent operations queue.
			$cmd->texto = "INSERT INTO ogagent_queue (clientid, exectime, operation) VALUES";
			if ($deadLine > 600) {
				# Add reminder 10 min. before deadline.
				$cmd->texto .= " ($clntid, NOW() + INTERVAL $deadLine SECOND - INTERVAL 10 MINUTE, 'popup-10'),";
			}
			if ($deadLine > 300) {
				# Add reminder 5 min. before deadline.
				$cmd->texto .= " ($clntid, NOW() + INTERVAL $deadLine SECOND - INTERVAL 5 MINUTE, 'popup-5'),";
			}
			# Add power off command at deadline time.
			$cmd->texto .= " ($clntid, NOW() + INTERVAL $deadLine SECOND, 'poweroff');";
			if ($deadLine == 0 or $cmd->Ejecutar()) {
				// Confirm operation.
				$cmd->texto = "";
				$response = "";
				jsonResponse(200, $response);
			} else {
				// Error message.
				$response["message"] = "Database error";
				jsonResponse(400, $response);
			}
		} else {
			// Error message.
			$response["message"] = "Client is not reserved";
			jsonResponse(400, $response);
		}
	} else {
		// Error message.
		$response["message"] = "Client does not exist";
		jsonResponse(404, $response);
	}
	$rs->Cerrar();
    }
);


/**
 * @brief    Store UDS server URLs to resend some events recieved from OGAgent.
 * @brief    Unreserve a client and send a poweroff operation.
 * @note     Route: /ous/:ouid/labs/:labid/clients/:clntid/unreserve, Method: DELETE
 */
$app->delete('/ous/:ouid/labs/:labid/clients/:clntid/unreserve', 'validateApiKey',
    function($ouid, $labid, $clntid) use ($app) {
	global $cmd;
	global $userid;
	global $ACCION_INICIADA;
	$response = Array();
	$ogagent = Array();

	if ($app->settings['debug'])
		writeRemotepcLog($app->request()->getResourceUri(). ": Init.");
	// Checking parameters. 
	try {
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT']))) {
			throw new Exception("Bad agent: sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		if (!checkIds($ouid, $labid, $clntid)) {
			throw new Exception("Ids. must be positive integers");
		}
	} catch (Exception $e) {
		// Error message.
		$response["message"] = $e->getMessage();
		if ($app->settings['debug'])
			writeRemotepcLog($app->request()->getResourceUri(). ": ERROR: ".$response["message"].".");
		jsonResponse(400, $response);
		$app->stop();
	}

	// Select client data for UDS compatibility.
	$cmd->texto = <<<EOD
SELECT adm.idusuario, ordenadores.idordenador, ordenadores.ip, ordenadores.agentkey, remotepc.reserved
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
  JOIN aulas USING(idaula)
 RIGHT JOIN administradores_centros AS adm USING(idcentro)
 RIGHT JOIN usuarios USING(idusuario)
 WHERE adm.idusuario = '$userid'
   AND idcentro = '$ouid' AND aulas.idaula ='$labid'
   AND ordenadores.idordenador = '$clntid';
EOD;
	$rs=new Recordset;
	$rs->Comando=&$cmd;
	if (!$rs->Abrir()) return(false);       // Error opening recordset.
	// Check if user is admin and client exists.
	$rs->Primero();
	if (checkAdmin($rs->campos["idusuario"]) and checkParameter($rs->campos["idordenador"])) {
		// Check if client is reserved.
		if (! is_null($rs->campos["reserved"])) {
			// Read query data.
			$clntip = $rs->campos["ip"];
			$agentkey = $rs->campos["agentkey"];
			// DB Transaction: set reservation time to the past, remove pending
			// boot commands from client's and agent's queues, and drop its event.
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": Updating database.");
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
			$cmd->texto = <<<EOD
DELETE FROM ogagent_queue
 WHERE clientid = '$clntid' AND command IN ('popup-10', 'popup-5', 'poweroff');
EOD;
			$cmd->Ejecutar();
			$cmd->texto = "DROP EVENT IF EXISTS e_timeout_$clntid;";
			$cmd->Ejecutar();
			$cmd->texto = "COMMIT;";
			$cmd->Ejecutar();
			// Send a poweroff command to client's OGAgent.
			$ogagent[$clntip]['url'] = "https://$clntip:8000/opengnsys/poweroff";
			$ogagent[$clntip]['header'] = Array("Authorization: ".$agentkey);
			if ($app->settings['debug'])
				writeRemotepcLog($app->request()->getResourceUri(). ": OGAgent poweroff, url=".$ogagent[$clntip]['url'].".");
			$result = multiRequest($ogagent);
			// ... (check response)
			//if ($result[$clntip]['code'] != 200) {
			// ...
			// Confirm operation.
			$response = "";
			jsonResponse(200, $response);
		} else {
			// Error message.
			$response["message"] = "Client is not reserved";
			jsonResponse(400, $response);
		}
	} else {
		// Error message.
		$response["message"] = "Client does not exist";
		jsonResponse(404, $response);
	}
	$rs->Cerrar();
    }
);


