<?php
/*______________________________________________________________________
	Cambia de posicion los extremos de una fecha. Devuelve una fecha con formato 
	dd-mm-aaaa si el formato de entrada es aaaa-mm-dd y viseversa
	Parametros: 
		- fecha: Una cadena con los datos de una fecha
_______________________________________________________________________*/
function InvFecha($fecha){
	if ($fecha=="1970-01-01")return("");

	$auxexplode=explode(" ",$fecha);
	list($anno_p,$mes_p,$dia_p)=explode("-",str_replace("/","-",$auxexplode[0]));
	$fecha_p=$dia_p.'-'.$mes_p.'-'.$anno_p;
	return($fecha_p);
}
//////////////////////////////////////////////////// 
//Convierte fecha de mysql a normal 
//////////////////////////////////////////////////// 
function sacafechaDB($fecha){ 
    preg_match("~([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})~", $fecha, $mifecha);
    $lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1]; 
    return $lafecha; 
} 

//////////////////////////////////////////////////// 
//Convierte fecha de normal a mysql 
//////////////////////////////////////////////////// 

function metefechaDB($fecha){ 
    preg_match("~([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})~", $fecha, $mifecha);
    $lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1]; 
    return $lafecha; 
} 
function HoraValida($hora){
	if ($hora=="00:00:00")return("");
}

