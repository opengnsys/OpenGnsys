<?
//________________________________________________________________________________________
//
//	Trocea en elementos de una matriz la cadena enviada como parametro separando por parametros
//	Parámetros:
//		- trama: La trama
//	 Devuelve:
//		Una matriz con las parejas de paramertos "nombre=valor"
//________________________________________________________________________________________
function extrae_parametros($parametros,$chsep,$chval){
	$ParametrosCadena="";
	$auxP=split($chsep,$parametros);
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split($chval,$auxP[$i]);
		if (isset($dualparam[0]) && isset($dualparam[1])){
			$streval='$ParametrosCadena["'.$dualparam[0].'"]="'.$dualparam[1].'";';
			eval($streval);
		}
	}
	return($ParametrosCadena);
}
//________________________________________________________________________________________
//
//	Trocea en elementos de una matriz la cadena enviada como parametro separando por parametros y devolviendo el elegido
//	Parámetros:
//	 Devuelve:
//________________________________________________________________________________________
function extrae_parametro($parametros,$chsep,$chval,$chr){
	$ParametrosCadena="";
	$auxP=split($chsep,$parametros);
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split($chval,$auxP[$i]);
		if (isset($dualparam[0]) && isset($dualparam[1])){
			if($dualparam[0]==$chr)
				return($dualparam[1]);
		}
	}
	return("");
}


//________________________________________________________________________________________
//
//	Busca una cadena dentro de otra.
// Especificaciones:
//		Puede ser sensible a las  mayúsculas
// Parametros:
//		cadena; cadena donde se va a buscar
//		subcadena; cadena a buscar
//		swsensible; si es sensible o no a las mayúsculas y minúsculas
// Devuelve:
//		La posición de comienzo de la subcadena dentro de la cadena, o (-1) en caso de no estar dentro
//________________________________________________________________________________________
function EnCadena($cadena,$subcadena,$swsensible = false) {
	$i=0;
	while (strlen($cadena)>=$i) {
		unset($substring);
		if ($swsensible) {
			$subcadena=strtolower($subcadena);
			$cadena=strtolower($cadena);
		}
		$substring=substr($cadena,$i,strlen($subcadena));
		if ($substring==$subcadena) return$i;
		$i++;
	}
	return -1;
 }
//_____________________________________________________________________________________________
// Búsqueda binaria o dicotómica en una tabla y devuelve el índice del elemento buscado tabla de una dimension
//_____________________________________________________________________________________________
function busca_indicebinario($dato,$tabla,$cont){
	if (empty($tabla)) return(-1);
	$a=0;
	$b=$cont-1;
	do{
		$p=round(($a+$b)/2,0);
		if ($tabla[$p]==$dato)
			return($p);
		
		else{
				if ($tabla[$p]<$dato){
					$a=$p+1;
				}
				else
					$b=$p-1;
		}
	}while($b>=$a);
	return(-1);
}
//_____________________________________________________________________________________________
// Búsqueda binaria o dicotómica en una tabla y devuelve el índice del elemento buscado tabla de dos dimensiones
//_____________________________________________________________________________________________
function busca_indicebinariodual($dato,$tabla,$cont){
	$a=0;
	$b=$cont-1;
	do{
		$p=round(($a+$b)/2,0);
		if ($tabla[$p][0]==$dato)
			return($p);
		
		else{
				if ($tabla[$p][0]<$dato){
					$a=$p+1;
				}
				else
					$b=$p-1;
		}
	}while($b>=$a);
	return(-1);
}
//___________________________________________________________________________________
function CreaTablaParametros($cmd){

	global  $tabla_parametros;
	global  $cont_parametros;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM parametros";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return; // Error al abrir recordset
	$rs->Primero(); 
	$cont=0;
	while (!$rs->EOF){
		$auxtabla_parametros="";
		$auxtabla_parametros["nemonico"]=$rs->campos["nemonico"];
		$auxtabla_parametros["descripcion"]=$rs->campos["descripcion"];
		$auxtabla_parametros["nomidentificador"]=$rs->campos["nomidentificador"];
		$auxtabla_parametros["nomtabla"]=$rs->campos["nomtabla"];
		$auxtabla_parametros["nomliteral"]=$rs->campos["nomliteral"];
		$auxtabla_parametros["tipopa"]=$rs->campos["tipopa"];
		$tabla_parametros[$cont][0]=$auxtabla_parametros["nemonico"];
		$tabla_parametros[$cont][1]=$auxtabla_parametros;
		$cont++;
		$rs->Siguiente();
	}
	$auxnemonico="";
	// Ordena según el nemonico
	for ($i=0;$i<$cont-1;$i++){
		for ($j=$i+1;$j<$cont;$j++){
			if($tabla_parametros[$i][0]>$tabla_parametros[$j][0]){
				$auxnemonico=$tabla_parametros[$i][0];
				$tabla_parametros[$i][0]=$tabla_parametros[$j][0];
				$tabla_parametros[$j][0]=$auxnemonico;

				$auxtabla_parametros=$tabla_parametros[$i][1];
				$tabla_parametros[$i][1]=$tabla_parametros[$j][1];
				$tabla_parametros[$j][1]=$auxtabla_parametros;
			}
		}
	}
	$cont_parametros=$cont;
}
/*______________________________________________________________________
	Redirecciona a la página de error
	Parametros: 
		- Literal del error
_______________________________________________________________________*/
function RedireccionaError($herror){

	$urlerror=urldecode($herror);
	$wurl="../seguridad/logerror.php?herror=".$urlerror;
	Header('Location: '.$wurl);
}

/*______________________________________________________________________
	Elimina de la cadena de parametros, el parametro iph ( que debe ser el ultimo)
	Parametros: 
		- cadena de parametros de un comando
	Devuelve:
		- la cadena sin el parametro iph y su valor
_______________________________________________________________________*/
function Sin_iph($cadena){

	$pos=EnCadena($cadena,"iph=") ;
	if($pos==-1) return($cadena);
	return(substr($cadena,0,$pos));
}
/*______________________________________________________________________
	Elimina de la cadena de parametros, el parametro mac ( que debe ser el ultimo)
	Parametros: 
		- cadena de parametros de un comando
	Devuelve:
		- la cadena sin el parametro iph y su valor
_______________________________________________________________________*/
function Sin_mac($cadena){

	$pos=EnCadena($cadena,"mac=") ;
	if($pos==-1) return($cadena);
	return(substr($cadena,0,$pos));
}
