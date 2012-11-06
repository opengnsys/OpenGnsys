<?php
/** Universidad de Huelva
        Fichero para validacion de usuarios antes del menu
        Es necesario crear la variable de sesion $validated y asignar true o false dependiendo del caso

**/
include_once("functions.php");


$action=(isset($_POST["action"]))?$_POST["action"]:$action;



if($action == "checkValidation"){
	$idordenador;
	$nombreordenador;
	$ip = TomaIP();
	$validacion;
	$paginalogin;
	$paginavalidacion;

	// Carga la configuracion del ordenador
	$cmd=CreaComando($cadenaconexion); // Crea objeto comando
	if (!$cmd)
        	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.

	$resul=TomaPropiedades($cmd);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
	// Registramos las variables en sesion
	$_SESSION["validacion"] = $validacion;
	$_SESSION["paginalogin"] = $paginalogin;
	$_SESSION["paginavalidacion"] = $paginavalidacion;
}
else{
	// Cogemos las variables de sesion
	$validacion = $_SESSION["validacion"];
        $paginalogin = $_SESSION["paginalogin"];
        $paginavalidacion = $_SESSION["paginavalidacion"];

}


/**/
// Solo se usa si se requiere validacion
if($_SESSION["validacion"] == true &&  isset($paginavalidacion) && $paginavalidacion != "")
	include_once($paginavalidacion);


switch($action){
        case "checkValidation":
                // Comprobamos si es necesaria la validacion
		if($validacion == 1){
                        $action="Login";
			 // Comprobamos si es necesaria la validacion, y llamamos a synchronize
	                // La funcion synchronize se usa por si hace falta sincronizar alguna base de datos externa a opengnsys
        	        // Es obligatoria en el fichero de validacion, pero puede no hacer nada
                	synchronize($validacion);
                }
                else{
                        $action="default";
                }
                include("access_controller.php");

        break;
        case "Login":
                include($paginalogin);
        break;
        case "validate":
                if(!isset($_SESSION)){
                        session_start();
                }
		// en la pagina "paginavalidacion" debe existir la funcion validate($_POST) forzosamente
                $_SESSION["validated"]=validate($_POST);
                if($_SESSION["validated"] == true){
                        include("../varios/menucliente.php");
                }
                else{
                        $_error="Usuario no v&aacute;lido";
                        include($paginalogin);
                }
        break;
        default:
                if(!isset($_SESSION)){
                        session_start();
                }
                $_SESSION["validated"]=true;
                include("menucliente.php");
}
/**/

?>

