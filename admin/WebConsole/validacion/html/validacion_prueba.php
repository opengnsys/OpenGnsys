<?php
/**
 * Sincroniza una base de datos externa con la de opengnsys.
 * No es necesaria su implementacion, puede dejarse en blanco
 */
function synchronize($validation){

}


/**
 * Funcion de validacion para el cliente opengnsys.
 * Recibe como parametros la variable $_POST proveniente de la pagina de login
 * debe devolver true o false
 */
function validate($VARS){
	$result = false;
	if($VARS["login"] == "usuprueba" && $VARS["password"] == "prueba"){
		$result = true;
	}
	return $result;
}


