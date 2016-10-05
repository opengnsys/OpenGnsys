<?php

// OpenGnsys REST routes for OGAgent communications.
// Author: Ramón M. Gómez
// Date:   2016-10-03


// OGAgent sessions log file.
define('LOG_FILE', '/opt/opengnsys/log/ogagent.log');

/**
 * @brief    OGAgent notifies that its service is started on a client.
 * @note     Route: /ogagent/started, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string mac        MAC (Ethernet) address
 * @param    string ostype     OS type (Linux, Windows)
 * @param    string osversion  OS version
 * @param    string secret     random secret key to access client's REST API
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/started',
    function() use ($app) {
	global $cmd;
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
		    if (isset($input->secret))  $secret = htmlspecialchars($input->secret));
		    $cmd->texto = "UPDATE ordenadores
				      SET agentkey='$secret'
				    WHERE ip='$ip' AND mac=UPPER(REPLACE('$mac',':',''))
				    LIMIT 1";
		    if ($cmd->Ejecutar() !== true or mysql_affected_rows() !== 1) {
			// DB access error or not updated.
			throw new Exception("Cannot store new secret key: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		    }
		} else {
		    // Insecure agent exception.
		    throw new Exception("Insecure OGAgent started: ip=$ip, mac=$mac, os=$osType:$osVersion.");
		}
		// Default processing: log activity.
		file_put_contents(LOG_FILE, date(DATE_RSS).": OGAgent started: ip=$ip, mac=$mac, os=$osType:$osVersion.\n", FILE_APPEND);
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Comunication error.
		$response["message"] = $e->getMessage();
		file_put_contents(LOG_FILE, date(DATE_RSS).": ".$app->request()->getResourceUri().": ERROR: ".$response["message"]."\n", FILE_APPEND);
		jsonResponse(400, $response);
	}
    }
);

/**
 * @brief    OGAgent notifies that its service is stopped on client.
 * @note     Route: /ogagent/stopped, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string mac        MAC (Ethernet) address
 * @param    string ostype     OS type (Linux, Windows)
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
		file_put_contents(LOG_FILE, date(DATE_RSS).": OGAgent stopped: ip=$ip, mac=$mac, os=$osType:$osVersion.\n", FILE_APPEND);
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Comunication error.
		$response["message"] = $e->getMessage();
		file_put_contents(LOG_FILE, date(DATE_RSS).": ".$app->request()->getResourceUri().": ERROR: ".$response["message"]."\n", FILE_APPEND);
		jsonResponse(400, $response);
	}
    }
);

/**
 * @brief    OGAgent notifies that an user logs in.
 * @note     Route: /ogagent/loggedin, Method: POST, Format: JSON
 * @param    string ip         IP address
 * @param    string user       username
 * @return   Null string if OK, else error message.
 */
$app->post('/ogagent/loggedin',
    function() use ($app) {
	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$user = htmlspecialchars($input->user);
		// Check sender IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// May check if client is included in the server database?
		// Default processing: log activity.
		file_put_contents(LOG_FILE, date(DATE_RSS).": User logged in: ip=$ip, user=$user.\n", FILE_APPEND);
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Comunication error.
		$response["message"] = $e->getMessage();
		file_put_contents(LOG_FILE, date(DATE_RSS).": ".$app->request()->getResourceUri().": ERROR: ".$response["message"]."\n", FILE_APPEND);
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
	try {
		// Reading POST parameters in JSON format.
		$input = json_decode($app->request()->getBody());
		$ip = htmlspecialchars($input->ip);
		$user = htmlspecialchars($input->user);
		// Check sender agent type and IP address consistency (same as parameter value).
		if (empty(preg_match('/^python-requests\//', $_SERVER['HTTP_USER_AGENT'])) or $ip !== $_SERVER['REMOTE_ADDR']) {
		    throw new Exception("Bad OGAgent: ip=$ip, sender=".$_SERVER['REMOTE_ADDR'].", agent=".$_SERVER['HTTP_USER_AGENT']);
		}
		// May check if client is included in the server database?
		// Default processing: log activity.
		file_put_contents(LOG_FILE, date(DATE_RSS).": User logged out: ip=$ip, user=$user.\n", FILE_APPEND);
		// Response. 
		$response = "";
		jsonResponse(200, $response);
	} catch (Exception $e) {
		// Comunication error.
		$response["message"] = $e->getMessage();
		file_put_contents(LOG_FILE, date(DATE_RSS).": ".$app->request()->getResourceUri().": ERROR: ".$response["message"]."\n", FILE_APPEND);
		jsonResponse(400, $response);
	}
    }
);

?>
