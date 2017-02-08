<?php
/**
 * @file    index.php
 * @brief   OpenGnsys REST API manager.
 * @warning All input and output messages are formatted in JSON.
 * @note    Some ideas are based on article "How to create REST API for Android app using PHP, Slim and MySQL" by Ravi Tamada, thanx.
 * @license GNU GPLv3+
 * @author  Ramón M. Gómez, ETSII Univ. Sevilla
 * @version 1.1
 * @date    2016-05-19
 */

// Inclussion files.

// Server access data.
include_once("../controlacceso.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
// Connection class.
@include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../clases/SockHidra.php");
// REST functions.
@include_once("../includes/restfunctions.php");
// Slim framework.
include_once("Slim/Slim.php");
\Slim\Slim::registerAutoloader();

// Server access control.
$cmd = CreaComando($cnx);
if (!$cmd)
	die("Access Error");

// Install Slim application (development mode).
//$app = new \Slim\Slim(array('mode' => 'production', ... );
$app = new \Slim\Slim(array(
		'mode' => 'development',
		'log.enabled' => true,
		'log.level' => \Slim\Log::ERROR,
		'debug' => false));
$app->setName('opengnsys');

// Global variables.
$userid = NULL;			// User id. with access to REST API.

// Common funtions and routes.
include("common.php");

// Check if services are running.
$config = parse_ini_file("/etc/default/opengnsys");

// If server is running, include its routes and OGAgent push routes.
if ($config['RUN_OGADMSERVER'] === "yes") {
    include("server.php");
    include("ogagent.php");
    include("remotepc.php");
}

// If repository is running, include its routes.
if ($config['RUN_OGADMREPO'] === "yes") {
    include("repository.php");
}

// Showing API information page using Swagger-UI.
$app->get('/',
    function() use ($app) {
        $app->response->redirect('swagger-ui/index.html?url=../../opengnsys-api.yml');
    }
);


// Execute REST using Slim.
$app->run();

?>

