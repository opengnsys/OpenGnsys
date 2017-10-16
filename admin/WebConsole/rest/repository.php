<?php
/**
 * @file    repository.php
 * @brief   OpenGnsys Repository REST API manager.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Juan Manuel Bardallo SIC Universidad de Huelva
 * @version 1.0
 * @date    2016-04-06
 */

// Auxiliar functions.
/**
 * @brief    Validate API key included in "Authorization" HTTP header.
 * @return   JSON response on error.
 */
function validateRepositoryApiKey() {
	$response = array();
	$app = \Slim\Slim::getInstance();

	// Read Authorization HTTP header.
	$headers = apache_request_headers();
	if (! empty($headers['Authorization'])) {
		// Assign user id. that match this key to global variable.
		$apikey = htmlspecialchars($headers['Authorization']);
		// El repositorio recupera el token desde el fichero de configuracion ogAdmRepo.cfg
		$confFile = fopen("../../etc/ogAdmRepo.cfg", "r");

		// Leemos cada linea hasta encontrar la clave "ApiToken"
		if ($confFile) {
			$found = false;
			while(!feof($confFile)){
				$line = fgets($confFile);
				$key = strtok($line,"=");
				if($key == "ApiToken"){
					$token = trim(strtok("="));
					if(strcmp($apikey,$token) == 0){
						$found = true;
					}
				}
			}
			if (!$found){
				// Credentials error.
                		$response['message'] = 'Login failed. Incorrect credentials';
				jsonResponse(401, $response);
				$app->stop();
			}
		} else {
			// Access error.
			$response['message'] = "An error occurred, please try again";
			jsonResponse(500, $response);
		}
	} else {
		// Error: missing API key.
       		$response['message'] = 'Missing Repository API key';
		jsonResponse(400, $response);
		$app->stop();
	}
}

function commandExist($cmd) {
    $returnVal = shell_exec("which $cmd");
    return (empty($returnVal) ? false : true);
}


// Define REST routes.


/**
 * @brief    List all images in the repository
 * @note     Route: /repository/images, Method: GET
 * @param    no
 * @return   JSON object with directory, images array, ous array and disk data.
 */
$app->get('/repository/images(/)', 'validateRepositoryApiKey', 
    function() use ($app) {
	$response = array();
	// Read repository information file.
	$cfgFile = '/opt/opengnsys/etc/repoinfo.json';
	$response = json_decode(@file_get_contents($cfgFile), true);
        // Check if directory exists.
	$imgPath = @$response['directory'];
	if (is_dir($imgPath)) {
		// Complete global image information.
		for ($i=0; $i<sizeof(@$response['images']); $i++) {
			$img = $response['images'][$i];
			$file = $imgPath."/".($img['type']==="dir" ? $img["name"] : $img["name"].".".$img["type"]);
			$response['images'][$i]['size'] = @stat($file)['size'];
			$response['images'][$i]['modified'] = date("Y-m-d H:i:s", @stat($file)['mtime']);
			$response['images'][$i]['mode'] = substr(decoct(@stat($file)['mode']), -4);
			$backupfile = $file.".ant";
			if (file_exists($backupfile)) {
				$response['images'][$i]['backedup'] = true;
				$response['images'][$i]['backupsize'] = @stat($backupfile)['size'];
			} else {
				$response['images'][$i]['backedup'] = false;
			}
		}
		// Complete image in OUs information.
		for ($j=0; $j<sizeof(@$response['ous']); $j++) {
			for ($i=0; $i<sizeof(@$response['ous'][$j]['images']); $i++) {
				$img = $response['ous'][$j]['images'][$i];
				$file = $imgPath."/".$response['ous'][$j]['subdir']."/".($img['type']==="dir" ? $img["name"] : $img["name"].".".$img["type"]);
				$response['ous'][$j]['images'][$i]['size'] = @stat($file)['size'];
				$response['ous'][$j]['images'][$i]['modified'] = date("Y-m-d H:i:s", @stat($file)['mtime']);
				$response['ous'][$j]['images'][$i]['mode'] = substr(decoct(@stat($file)['mode']), -4);
			}
		}
		// Retrieve disk information.
		$total = disk_total_space($imgPath);
		$free = disk_free_space($imgPath);
		$response['disk']['total'] = $total;
		$response['disk']['free'] = $free;
                // JSON response.
		jsonResponse(200, $response);
	} else {
		// Print error message.
		$response['message'] = 'Images directory not found';
		jsonResponse(404, $response);
	}
	$app->stop();
    }
);


/**
 * @brief    Power on a pc or group of pcs with the MAC specified in POST parameters
 * @note     Route: /poweron, Method: POST
 * @param    macs      OU id.
 * @return   JSON string ok if the power on command was sent
 */
$app->post('/repository/poweron', 'validateRepositoryApiKey',
    function() {
		$app = \Slim\Slim::getInstance();
		// Debe venir el parametro macs en el post (objeto JSON con array de MACs)
		$data = $app->request()->post();
		if(empty($data->macs)){
			// Print error message.
			$response['message'] = 'Required param macs not found';
			jsonResponse(400, $response);
		}
		else{
			$macs = $data->macs;
			$strMacs = "";
			foreach($macs as $mac){
				$strMacs .= " ".$mac;
			}
			// Ejecutar comando wakeonlan, debe estar disponible en el sistema operativo
			if(commandExist("wakeonlan")){
				$response["output"] = "Executing wakeonlan ".trim($strMacs)."\n";
				$response["output"] .= shell_exec("wakeonlan ".trim($strMacs));
				// Comprobar si el comando se ejecutórrectamente
	    		jsonResponse(200, $response);
			}
			else{
				// Print error message.
				$response['message'] = 'Wakeonlan command not found in this repository';
				jsonResponse(404, $response);
			}
		}
	}
);

?>
