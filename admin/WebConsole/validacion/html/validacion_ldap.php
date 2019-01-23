<?php

function connect_to_ldap(){	
	$server = "IP_SERVIDOR";
	$port = '389';
	$user = "USUARIO";
	$pass = "PASSWORD";
		
	$result = null;
	$ds=ldap_connect($server,$port);
	if ($ds){
		if ($r=@ldap_bind($ds,$user,$pass))
                        $result = $ds;
	}
	return $result;
}
	
function validate_user($user,$password){
	if (($user=='') || ($password=='')){
		$result['validation'] = -1;
	}else{
		if($ds = connect_to_ldap()){
			$dc = "dc=uhu, dc=es";
			$search = "uid=".$user;
			$sr=@ldap_search($ds,$dc,$search);
	 		$info = @ldap_get_entries($ds, $sr);

			if ($info["count"]==1){					
				$thedata = $info[0]["dn"];					
				if ($r=@ldap_bind($ds,$thedata,$password)){ 
					$result['validation'] = 1;
					// A parte de la validacion, se podrían coger otros datos...
					/*
					$result['name'] = $info[0]["cn"][0];
					$result['dni'] = $info[0]["uhuuserdni"][0];
					$result['email'] =  $info[0]["mail"][0];
					*/
				}
				else{ 
					$result['validation'] = -1;
				}
			}
			else{ 
				$result['validation'] = -1;
			}				
		}
		ldap_close($ds);
	}
	return $result;
}

/**
 * Sincroniza una base de datos externa con la de OpenGnsys.
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
	$result=false;
	$validationInfo = validate_user($VARS["login"], $VARS["password"]);
	if($validationInfo["validation"] == 1){
		$result = true;
	}
	return $result;
}
