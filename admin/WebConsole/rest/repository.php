<?php
/**
 * @file    repository.php
 * @brief   OpenGnsys Repository REST API manager.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Juan Manuel Bardallo SIC Universidad de Huelva
 * @version 1.1.0
 * @date    2016-04-06
 */


// Auxiliary functions.
/**
 * @brief    Validate API key included in "Authorization" HTTP header.
 * @return   JSON response on error.
 */
function validateRepositoryApiKey() {
	$response = [];
	$app = \Slim\Slim::getInstance();

	// Assign user id. that match this key to global variable.
	@$apikey = htmlspecialchars(function_exists('apache_request_headers') ? apache_request_headers()['Authorization'] : $_SERVER['HTTP_AUTHORIZATION']);
	if (isset($apikey)) {
		// fetch repository token from ogAdmRepo.cfg configuration file.
		@$confFile = parse_ini_file(__DIR__ . '/../../etc/ogAdmRepo.cfg', 'r');
		if (isset($confFile)) {
			if(@strcmp($apikey, $confFile['ApiToken']) == 0) {
				// Credentials OK.
				return true;
			} else {
				// Credentials error.
                		$response['message'] = 'Login failed. Incorrect credentials';
				jsonResponse(401, $response);
				$app->stop();
			}
		} else {
			// Cannot access configuration file.
			$response['message'] = "An error occurred, please try again";
			jsonResponse(500, $response);
			$app->stop();
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


// REST routes.


/**
 * @brief    List all images in the repository
 * @note     Route: /repository/images, Method: GET
 * @return   string  JSON object with directory, images array, ous array and disk data.
 */
$app->get('/repository/images(/)', 'validateRepositoryApiKey', 
    function() use ($app) {
	$response = [];
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
			$backupfile = "$file.ant";
			if (file_exists($backupfile)) {
				$response['images'][$i]['backedup'] = true;
				$response['images'][$i]['backupsize'] = @stat($backupfile)['size'];
			} else {
				$response['images'][$i]['backedup'] = false;
			}
			$lockfile = "$file.lock";
			$response['images'][$i]['locked'] = file_exists($lockfile);
		}
		// Complete image in OUs information.
		for ($j=0; $j<sizeof(@$response['ous']); $j++) {
			for ($i=0; $i<sizeof(@$response['ous'][$j]['images']); $i++) {
				$img = $response['ous'][$j]['images'][$i];
				$file = $imgPath."/".$response['ous'][$j]['subdir']."/".($img['type']==="dir" ? $img["name"] : $img["name"].".".$img["type"]);
				$response['ous'][$j]['images'][$i]['size'] = @stat($file)['size'];
				$response['ous'][$j]['images'][$i]['modified'] = date("Y-m-d H:i:s", @stat($file)['mtime']);
				$response['ous'][$j]['images'][$i]['mode'] = substr(decoct(@stat($file)['mode']), -4);
				$response['ous'][$j]['images'][$i]['backedup'] = false;
				$lockfile = "$file.lock";
				$response['ous'][$j]['images'][$i]['locked'] = file_exists($lockfile);
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
 * @brief    List image data
 * @note     Route: /repository/image/:imagename, Method: GET
 * @return   string  JSON object with image data.
 */
$app->get('/repository/image(/:ouname)/:imagename(/)', 'validateRepositoryApiKey', 
    function($ouname="/", $imagename) use ($app) {
	$images = [];
	$response = [];
	// Search image name in repository information file.
	$cfgFile = '/opt/opengnsys/etc/repoinfo.json';
	$json = json_decode(@file_get_contents($cfgFile), true);
	$imgPath = @$json['directory'];
	if (empty($ouname) or $ouname == "/") {
		// Search in global directory.
		$images = @$json['images'];
	} else {
		// Search in OU directory.
		for ($i=0; $i<sizeof(@$json['ous']); $i++) {
			if ($json['ous'][$i]['subdir'] == $ouname) {
				$images = $json['ous'][$i]['images'];
			}
		}
	}
	// Search image.
	foreach ($images as $img) {
		if ($img['name'] == $imagename) {
			$response = $img;
			$file = "$imgPath/$ouname/" . ($img['type']==="dir" ? $img["name"] : $img["name"].".".$img["type"]);
			$response['size'] = @stat($file)['size'];
			$response['modified'] = date("Y-m-d H:i:s", @stat($file)['mtime']);
			$response['mode'] = substr(decoct(@stat($file)['mode']), -4);
			$backupfile = "$file.ant";
			if (file_exists($backupfile)) {
				$response['backedup'] = true;
				$response['backupsize'] = @stat($backupfile)['size'];
			} else {
				$response['backedup'] = false;
			}
			$lockfile = "$file.lock";
			$response['locked'] = file_exists($lockfile);
		}
	}
	if (isset ($response)) {
                // JSON response.
		jsonResponse(200, $response);
	} else {
		// Print error message.
		$response['message'] = 'Image not found';
		jsonResponse(404, $response);
	}
	$app->stop();
    }
);


/**
 * @brief    Power on a pc or group of pcs with the MAC specified in POST parameters
 * @note     Route: /poweron, Method: POST
 * @param    array   Array of MAC addresses
 * @return   string  JSON string ok if the power on command was sent
 */
$app->post('/repository/poweron', 'validateRepositoryApiKey',
    function() use($app) {
		$response = [];
		// The macs parameter must come in the post (JSON object with array of MACs)
		$data = json_decode($app->request()->getBody());
		if (empty($data->macs)) {
			// Print error message.
			$response['message'] = 'Required param macs not found';
			jsonResponse(400, $response);
		} else {
			// Execute local wakeonlan command (may be installed)
			if(commandExist("wakeonlan")) {
				$strMacs = trim(implode(' ', $data->macs));
				if(stristr($strMacs, ':') === false) {
					$strMacs = implode(':', str_split($strMacs, 2));
				}
				$response["output"] = "Executing wakeonlan ".$strMacs."\n";
				$response["output"] .= shell_exec("wakeonlan ".$strMacs);
				jsonResponse(200, $response);
			} else {
				// Print error message.
				$response['message'] = 'Wakeonlan command not found in this repository';
				jsonResponse(404, $response);
			}
		}
		$app->stop();
	}
);

