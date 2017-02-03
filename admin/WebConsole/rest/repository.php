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
		$app = \Slim\Slim::getInstance();
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
 * @note     Route: /images, Method: GET
 * @param    no
 * @return   JSON array with imagename, file size
 */
$app->get('/repository/images', 'validateRepositoryApiKey', 
	function() {
		$imgPath = '/opt/opengnsys/images';
		$app = \Slim\Slim::getInstance();
		// Comprobar si en la peticion se especificó un filtro por extensiones
		$extensions = $app->request->get('extensions');

		if ($manager = opendir($imgPath)) {
			$repoInfo=exec("df -h ".$imgPath);
			$repoInfo=split(" ",preg_replace('/\s+/', ' ', $repoInfo));

			$response['disk']["total"]=$repoInfo[1];
		    $response['disk']["used"]=$repoInfo[2];
		    $response['disk']["free"]=$repoInfo[3];
		    $response['disk']["percent"]=$repoInfo[4];

		    $response['images'] = array();
		    while (false !== ($entry = readdir($manager))) {
		    	$include = true;
		        if ($entry != "." && $entry != "..") {
		        	// Si se especificó algun filtro por extension, comprobamos si el fichero la cumple
		        	if($extensions){
		        		$ext = pathinfo($imgPath."/".$entry, PATHINFO_EXTENSION);
		        		// Puede ser una o varias dependiendo de si es array o no
		        		if(is_array($extensions) && !in_array($ext, $extensions)){
		        			$include = false;
		        		}
		        		else if(!is_array($extensions) && $extensions != $ext){
		        			$include = false;
		        		}

		        	}
		        	if($include == true){
						$strFileName = $imgPath."/".$entry;
						$fileInfo["file"]["name"] = $entry;
						$fileInfo["file"]["size"] = filesize($strFileName);
						$fileInfo["file"]["modified"] = date( "D d M Y g:i A", filemtime($strFileName));
						$fileInfo["file"]["permissions"] = (is_readable($strFileName)?"r":"-").(is_writable($strFileName)?"w":"-").(is_executable($strFileName)?"x":"-");
						array_push($response['images'], $fileInfo);
					}
		        }
		    }
		    closedir($manager);
		    jsonResponse(200, $response);
		}else{
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
