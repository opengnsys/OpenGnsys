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


// Common constants.
define('REST_LOGFILE', '/opt/opengnsys/log/rest.log');
define('VERSION_FILE', '/opt/opengnsys/doc/VERSION.json');

// Set time zone.
if (function_exists("date_default_timezone_set")) {
    if (exec("timedatectl status | awk '/Time zone/ {print $3}'", $out, $err)) {
        date_default_timezone_set($out[0]);
    }
}

// Common functions.

/**
 * @brief   Function to write a line into log file.
 * @param   string message  Message to log.
 * warning  Line format: "Date: ClientIP: UserId: Status: Method Route: Message"
 */
function writeRestLog($message = "") {
	global $userid;
	if (is_writable(REST_LOGFILE)) {
		$app = \Slim\Slim::getInstance();
		file_put_contents(REST_LOGFILE, date(DATE_ISO8601) .": " .
						$_SERVER['REMOTE_ADDR'] . ": " .
						(isset($userid) ? $userid : "-") . ": " .
						$app->response->getStatus() . ": " .
						$app->request->getMethod() . " " .
						$app->request->getPathInfo() . ": $message\n",
			  	FILE_APPEND);
	}
}

/**
 * @brief   Compose JSON response.
 * @param   int status      Status code for HTTP response.
 * @param   array response  Response data.
 * @param   int opts        Options to encode JSON data.
 * @return  string          JSON response.
 */
function jsonResponse($status, $response, $opts=0) {
	$app = \Slim\Slim::getInstance();
	// HTTP status code.
	$app->status($status);
	// Content-type HTTP header.
	$app->contentType('application/json; charset=utf-8');
	// JSON response.
	echo json_encode($response, $opts);
}

/**
 * @brief   Print immediately JSON response to continue processing.
 * @param   int status      Status code for HTTP response.
 * @param   array response  Response data.
 * @param   int opts        Options to encode JSON data.
 * @return  string          JSON response.
 */
function jsonResponseNow($status, $response, $opts=0) {
	// Compose headers and content.
	ignore_user_abort();
	http_response_code((int)$status);
	header('Content-type: application/json; charset=utf-8');
	ob_start();
	echo json_encode($response, $opts);
	$size = ob_get_length();
	header("Content-Length: $size");
	// Print content.
	ob_end_flush();
	flush();
	session_write_close();
}

/**
 * @brief    Validate API key included in "Authorization" HTTP header.
 * @return   string  JSON response on error.
 */
function validateApiKey() {
	global $cmd;
	global $userid;
	$response = [];
	$app = \Slim\Slim::getInstance();
	// Read Authorization HTTP header.
	if (! empty($_SERVER['HTTP_AUTHORIZATION'])) {
		// Assign user id. that match this key to global variable.
		$apikey = htmlspecialchars($_SERVER['HTTP_AUTHORIZATION']);
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
				$response['message'] = 'Login failed, incorrect credentials';
				jsonResponse(401, $response);
				$app->stop();
			}
			$rs->Cerrar();
		} else {
			// Database error.
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
	$response = [];
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
 * @brief    Check if all parameters are positive integer numbers.
 * @param    int id ...      Identificators to check (variable number of parameters).
 * @return   boolean         "true" if all ids are int>0, otherwise "false".
 */
function checkIds() {
	$opts = ['options' => ['min_range' => 1]];	// Check for int>0
	foreach (func_get_args() as $id) {
		if (filter_var($id, FILTER_VALIDATE_INT, $opts) === false) {
			return false;
		}
	}
	return true;
}

/**
 * @brief   Show custom message for "not found" error (404).
 */
$app->notFound(
    function() {
        $response['message'] = 'REST route not found';
        jsonResponse(404, $response);
   }
);

/**
 * @brief   Hook to write an error log message and a REST exit log message if debug is enabled.
 * @warning Error message will be written in web server's error file.
 * @warning REST message will be written in REST log file.
 */
$app->hook('slim.after', function() use ($app) {
	if ($app->response->getStatus() != 200 ) {
		// Compose error message (truncating long lines). 
		$app->log->error(date(DATE_ISO8601) . ': ' .
				 $app->getName() . ': ' .
				 $_SERVER['REMOTE_ADDR'] . ": " .
				 (isset($userid) ? $userid : "-") . ": " .
				 $app->response->getStatus() . ': ' .
				 $app->request->getMethod() . ' ' .
				 $app->request->getPathInfo() . ': ' .
				 substr($app->response->getBody(), 0, 100));
	}
	if ($app->settings['debug'])
		writeRestLog(substr($app->response->getBody(), 0, 30));
   }
);


// Common routes.

/**
 * @brief    Get general server information 
 * @note     Route: /info, Method: GET
 * @return   string  JSON object with basic server information (version, services, etc.)
 */
$app->get('/info', function() {
      $hasOglive = false;
      $response = new \stdClass;
      // Reading version file.
      $data = json_decode(@file_get_contents(VERSION_FILE));
      if (isset($data->project)) {
          $response = $data;
      } else {
          $response->project = 'OpenGnsys';
      }
      // Getting actived services.
      @$services = parse_ini_file('/etc/default/opengnsys');
      $response->services = [];
      if (@$services["RUN_OGADMSERVER"] === "yes") {
          array_push($response->services, "server");
          $hasOglive = true;
      }
      if (@$services["RUN_OGADMREPO"] === "yes")  array_push($response->services, "repository");
      if (@$services["RUN_BTTRACKER"] === "yes")  array_push($response->services, "tracker");
      // Reading installed ogLive information file.
      if ($hasOglive === true) {
          $data = json_decode(@file_get_contents('/opt/opengnsys/etc/ogliveinfo.json'));
          if (isset($data->oglive)) {
              $response->oglive = $data->oglive;
          }
      }
      jsonResponse(200, $response);
   }
);

/**
 * @brief    Get the server status
 * @note     Route: /status, Method: GET
 * @return   string  JSON object with all data collected from server status (RAM, %CPU, etc.).
 */
$app->get('/status', function() {
      $response = [];
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

