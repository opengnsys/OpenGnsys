<?php
/**
 * @file    ogagent.php
 * @brief   OpenGnsys REST routes for OGAgent communications.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1.0 - First version
 * @date    2016-10-03
 */


// OGAgent sessions log file.
define('LOG_FILE', '/opt/opengnsys/log/ogagent.log');

// Function to write a line into log file.
function writeLog($message = "") {
	file_put_contents(LOG_FILE, date(DATE_ISO8601).": $message\n", FILE_APPEND);
}

/**
 * @brief    OGAgent notifies that its service is started on a client.
 * @note     Route: /ogagent/started, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string mac        MAC (Ethernet) address
 * @param    string ostype     OS type (Linux, Windows, macOS)
 * @param    string osversion  OS version
 * @param    string secret     random secret key to access client's REST API
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/started',
    function() use ($app) {
	global $cmd;
	$secret = "";
	$osType = $osVersion = "none";
	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$mac = htmlspecialchars($input->mac);
		if (isset($input->ostype))  $osType = htmlspecialchars($input->ostype);
		if (isset($input->osversion))  $osVersion = str_replace(",", ";", htmlspecialchars($input->osversion));
		// Check sender agent type and IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// Client secret key for secure communications.
		if (isset($input->secret)) {
		    // Check if secret key is valid (32 alphanumeric characters).
		    if (! ctype_alnum($input->secret) or strlen($input->secret) !== 32) {
			throw new Exception("Bad secret key: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		    }
		    // Store secret key in DB.
		    if (isset($input->secret))  $secret = htmlspecialchars($input->secret);
		    $cmd->texto = <<<EOD
UPDATE ordenadores
   SET agentkey='$secret'
 WHERE ip='$ip' AND mac=UPPER(REPLACE('$mac', ':', ''))
 LIMIT 1;
EOD;
		    if ($cmd->Ejecutar() !== true or mysqli_affected_rows($cmd->Conexion->controlador) !== 1) {
			// DB access error or not updated.
			throw new Exception("Cannot store new secret key: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		    }
		} else {
		    // Insecure agent exception.
		    throw new Exception("Insecure OGAgent started: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		}
		// Default processing: log activity.
		writeLog("OGAgent started: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		writeLog($app->request()->getResourceUri().": ERROR: ".$response["message"]);
		jsonResponse(400, $response);
	}
    }
);

/**
 * @brief    OGAgent notifies that its service is stopped on client.
 * @note     Route: /ogagent/stopped, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string mac        MAC (Ethernet) address
 * @param    string ostype     OS type (Linux, Windows, macOS)
 * @param    string osversion  OS version
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/stopped',
    function() use ($app) {
	$osType = $osVersion = "none";
	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$mac = htmlspecialchars($input->mac);
		if (isset($input->ostype))  $osType = htmlspecialchars($input->ostype);
		if (isset($input->osversion))  $osVersion = str_replace(",", ";", htmlspecialchars($input->osversion));
		// Check sender agent type and IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// May check if client is included in the server database?
		// Default processing: log activity.
		writeLog("OGAgent stopped: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		writeLog($app->request()->getResourceUri().": ERROR: ".$response["message"]);
		jsonResponse(400, $response);
	}
    }
);

/**
 * @brief    OGAgent notifies that an user logs in.
 * @note     Route: /ogagent/loggedin, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string user       username
 * @param    string language   session language
 * @param    string ostype     OS type (Linux, Windows, macOS)
 * @param    string osversion  OS version
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/loggedin',
    function() use ($app) {
	global $cmd;
	$osType = $osVersion = "none";
	$redirto = Array();
	$result = Array();

	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$user = htmlspecialchars($input->user);
		$language = isset($input->language) ? substr($input->language, 0, strpos($input->language, "_")) : "";
		if (isset($input->ostype))  $osType = htmlspecialchars($input->ostype);
		if (isset($input->osversion))  $osVersion = str_replace(",", ";", htmlspecialchars($input->osversion));
		// Check sender IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// Check if client is included in the server database.
		$cmd->CreaParametro("@ip", $ip, 0);
		$cmd->texto = <<<EOD
SELECT ordenadores.idordenador, ordenadores.nombreordenador, remotepc.urllogin,
       remotepc.reserved > NOW() AS reserved
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
 WHERE ordenadores.ip=@ip
 LIMIT 1;
EOD;
		$rs=new Recordset;
		$rs->Comando=&$cmd;
		if ($rs->Abrir()) {
			// Read query data.
			$rs->Primero();
			$id = $rs->campos['idordenador'];
			$redirto[0]['url'] = $rs->campos['urllogin'];
			$reserved = $rs->campos['reserved'];
			$rs->Cerrar();
			if (!is_null($id)) {
				// Log activity, respond to client and continue processing.
				writeLog("User logged in: ip=$ip, user=$user, lang=$language, os=$osType:$osVersion.");
				$response = "";
				jsonResponseNow(200, $response);
			} else {
		    		throw new Exception("Client is not in the database: ip=$ip, user=$user");
			}
			// Redirect notification to UDS server, if needed.
			if ($reserved == 1 and !is_null($redirto[0]['url'])) {
				$redirto[0]['get'] = $app->request()->getBody();
				$result = multiRequest($redirto);
				// ... (check response)
				//if ($result[0]['code'] != 200) {
				// ...
				// Updating user's session language for messages.
				$cmd->texto = <<<EOD
UPDATE remotepc
   SET language = '$language'
 WHERE id = '$id';
EOD;
				$cmd->Ejecutar();
			}
		} else {
			throw new Exception("Database error");
		}
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		writeLog($app->request()->getResourceUri().": ERROR: ".$response["message"]);
		jsonResponse(400, $response);
	}
    }
);

/**
 * @brief    OGAgent notifies that an user logs out.
 * @note     Route: /ogagent/loggedout, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string user       username
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/loggedout',
    function() use ($app) {
	global $cmd;
	$redirto = Array();
	$result = Array();

	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$user = htmlspecialchars($input->user);
		// Check sender agent type and IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// Check if client is included in the server database.
		$cmd->CreaParametro("@ip", $ip, 0);
		$cmd->texto = <<<EOD
SELECT ordenadores.idordenador, ordenadores.nombreordenador, remotepc.urllogout,
       remotepc.reserved > NOW() AS reserved
  FROM remotepc
 RIGHT JOIN ordenadores ON remotepc.id=ordenadores.idordenador
 WHERE ordenadores.ip=@ip
 LIMIT 1;
EOD;
		$rs=new Recordset;
		$rs->Comando=&$cmd;
		if ($rs->Abrir()) {
			// Read query data.
			$rs->Primero();
			$id = $rs->campos['idordenador'];
			$redirto[0]['url'] = $rs->campos['urllogout'];
			$reserved = $rs->campos['reserved'];
			$rs->Cerrar();
			if (!is_null($id)) {
				// Log activity, respond to client and continue processing.
				writeLog("User logged out: ip=$ip, user=$user.");
				$response = "";
				jsonResponseNow(200, $response);
			} else {
		    		throw new Exception("Client is not in the database: ip=$ip, user=$user");
			}
			// Redirect notification to UDS server, if needed.
			if ($reserved == 1 and !is_null($redirto[0]['url'])) {
				$redirto[0]['get'] = $app->request()->getBody();
				$result = multiRequest($redirto);
				// ... (check response)
				//if ($result[0]['code'] != 200) {
				// ...
			}
		} else {
			throw new Exception("Database error");
		}
	} catch (Exception $e) {
		// Communication error.
		$response["message"] = $e->getMessage();
		writeLog($app->request()->getResourceUri().": ERROR: ".$response["message"]);
		jsonResponse(400, $response);
	}
    }
);

// Processing command results (TESTING).
$app->post('/ogagent/command_done',
    // 'validateClient',
    function() use ($app) {
        global $cmd;

        try {
            // Reading parameters.
            $input = json_decode($app->request()->getBody());
            $client = htmlspecialchars($input->client);
            $id = htmlspecialchars($input->trace);
            $status = htmlspecialchars($input->status);
            $output = htmlspecialchars($input->output);
            $error = htmlspecialchars($input->error);
            $client = $_SERVER['REMOTE_ADDR'];
            // Check sender agent type.
            if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $client !== $_SERVER['REMOTE_ADDR']) {
                throw new Exception("Bad OGAgent: client=$client, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
            }
            // TODO: truncating outputs.
            if ($status == 0) {
                writeLog($app->request()->getResourceUri().": Operation OK: client=$client, id=$id, output=$output");
            } else {
                writeLog($app->request()->getResourceUri().": Operation ERROR: client=$client, id=$id, status=$status, error=$error");
            }
            $response = "";
            jsonResponse(200, $response);
        } catch (Exception $e) {
            // Communication error.
            $response["message"] = $e->getMessage();
            writeLog($app->request()->getResourceUri().": ERROR: ".$response["message"]);
            jsonResponse(400, $response);
        }
    }
);

