<?php
/**
 * @file    index.php
 * @brief   OpenGnsys REST API: common routes
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1.0 - First version
 * @date    2016-11-17
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

