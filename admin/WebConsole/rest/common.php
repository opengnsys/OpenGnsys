<?php
/**
 * @file    index.php
 * @brief   OpenGnsys REST API: common functions and routes
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1.0 - First version
 * @date    2016-11-17
 */


// Common functions.



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
	$app = \Slim\Slim::getInstance();
	// Read Authorization HTTP header.
	$headers = apache_request_headers();
	if (! empty($headers['Authorization'])) {
		// Assign user id. that match this key to global variable.
		$apikey = htmlspecialchars($headers['Authorization']);
		$cmd->texto = "SELECT idusuario
				 FROM usuarios
				WHERE apikey='$apikey' LIMIT 1";
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
 * @fn       sendCommand($serverip, $serverport, $reqframe, &$values)
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

/**
 * @brief   Hook to write an error log message.
 * @warning Message will be written in web server's error file.
 */
$app->hook('slim.after', function() use ($app) {
	if ($app->response->getStatus() != 200 ) {
		$app->log->error(date(DATE_ATOM) . ': ' .
				 $app->getName() . ' ' .
				 $app->response->getStatus() . ': ' .
				 $app->request->getPathInfo() . ': ' .
				 substr($app->response->getBody(), 0, 50));
	}
   }
);


// Common routes.

/**
 * @brief    Get general server information 
 * @note     Route: /info, Method: GET
 * @param    no
 * @return   JSON object with basic server information (version, services, etc.)
 */
$app->get('/info', function() {
      // Reading version file.
      @list($project, $version, $release) = explode(' ', file_get_contents('/opt/opengnsys/doc/VERSION.txt'));
      $response['project'] = trim($project);
      $response['version'] = trim($version);
      $response['release'] = trim($release);
      // Getting actived services.
      @$services = parse_ini_file('/etc/default/opengnsys');
      $response['services'] = Array();
      if (@$services["RUN_OGADMSERVER"] === "yes") {
          array_push($response['services'], "server");
          $hasOglive = true;
      }
      if (@$services["RUN_OGADMREPO"] === "yes")  array_push($response['services'], "repository");
      if (@$services["RUN_BTTRACKER"] === "yes")  array_push($response['services'], "tracker");
      // Reading installed ogLive information file.
      if ($hasOglive === true) {
          $data = json_decode(@file_get_contents('/opt/opengnsys/etc/ogliveinfo.json'));
          if (isset($data->oglive)) {
              $response['oglive'] = $data->oglive;
          }
      }
      jsonResponse(200, $response);
   }
);

/**
 * @brief    Get the server status
 * @note     Route: /status, Method: GET
 * @param    no
 * @return   JSON object with all data collected from server status (RAM, %CPU, etc.).
 */
$app->get('/status', function() {
      // Getting memory and CPU information.
      exec("awk '$1~/Mem/ {print $2}' /proc/meminfo",$memInfo);
      $memInfo = array("total" => $memInfo[0], "used" => $memInfo[1]);
      $cpuInfo = exec("awk '$1==\"cpu\" {printf \"%.2f\",($2+$4)*100/($2+$4+$5)}' /proc/stat");
      $cpuModel = exec("awk -F: '$1~/model name/ {print $2}' /proc/cpuinfo");
      $response["memInfo"] = $memInfo;
      $response["cpu"] = array("model" => trim($cpuModel), "usage" => $cpuInfo);
      jsonResponse(200, $response);
   } 
);

