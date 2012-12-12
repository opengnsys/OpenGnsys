<?php
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
	//	Trocea en elementos de una matriz la cadena enviada como parametro separando por parametros
	//	y devolviendo el elegido
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
	//
	// Crea un Array con las especificaciones de los parámetros de los comandos
	//___________________________________________________________________________________
	function CreaTablaParametros($cmd)
	{
		$cmd->texto="SELECT * FROM parametros ORDER BY nemonico";
		$rs=new Recordset; 		
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(false); // Error al abrir recordset
		$cont=0;
		while (!$rs->EOF){
			$nemo=$rs->campos["nemonico"];
			$auxtabla_parametros="";
			$auxtabla_parametros["nemonico"]=$nemo;
			$auxtabla_parametros["descripcion"]=$rs->campos["descripcion"];
			$auxtabla_parametros["nomidentificador"]=$rs->campos["nomidentificador"];
			$auxtabla_parametros["nomtabla"]=$rs->campos["nomtabla"];
			$auxtabla_parametros["nomliteral"]=$rs->campos["nomliteral"];
			$auxtabla_parametros["tipopa"]=$rs->campos["tipopa"];
			$auxtabla_parametros["visual"]=$rs->campos["visual"];
			$tabla_parametros[$nemo]=$auxtabla_parametros;
			$cont++;
			$rs->Siguiente();
		}
		return($tabla_parametros);
	}
	//___________________________________________________________________________________
	//
	// Crea una tabla en memoria con los valores reales de los parámetros enviados
	//
	//	Parámetros:
	//		$cmd: Objeto comando (Operativo)
	//		$parámetros: El formato de parámetros que viaje en las trama y que es el mismo
	//		que se almacenan en las tablas de procedimientos_acciones o acciones
	//___________________________________________________________________________________
		
	function ParametrosValor($cmd,$parametros,&$tbParametrosValor,$ch="\r")
	{
		global $tbParametros;
		$html="";
		$auxprm=split($ch,$parametros);
		for($i=0;$i<sizeof($auxprm);$i++){
			list($nemonico,$valor)=split("=",$auxprm[$i]);
			if(isset($tbParametros[$nemonico])){
				if($tbParametros[$nemonico]["visual"]==1){
					$tbParametrosValor[$nemonico]["descripcion"]=$tbParametros[$nemonico]["descripcion"];
					switch($tbParametros[$nemonico]["tipopa"]){
						case 0: // El valor lo toma directamente
							$tbParametrosValor[$nemonico]["valor"]=$valor;
							break;
						case 1: // El valor lo toma de una tabla */
							$tbParametrosValor[$nemonico]["valor"]=TomaDato($cmd,0,$tbParametros[$nemonico]["nomtabla"],$valor,$tbParametros[$nemonico]["nomidentificador"],$tbParametros[$nemonico]["nomliteral"]);
							break;
						case 2: // El parámetro es compuesto de otros parametros
							$blkprm=split(chr(10),substr($auxprm[$i],4));
							for($j=0;$j<sizeof($blkprm);$j++){
								$tbSubParametrosValor=array();
								ParametrosValor($cmd,$blkprm[$j],$tbSubParametrosValor,chr(9));
								for($k=0;$k<sizeof($tbSubParametrosValor);$k++){
									$elem=current($tbSubParametrosValor);
									$tbParametrosValor[$nemonico][$j]["valor"].=$elem["descripcion"];							
									$tbParametrosValor[$nemonico][$j]["valor"].=": ".$elem["valor"];
									$tbParametrosValor[$nemonico][$j]["valor"].=", ";
									next($tbSubParametrosValor); 								
								}
							}
							break;	
						case 3: // El valor lo toma de una array 
							$tbcte=split($tbParametros[$nemonico]["nomidentificador"],$tbParametros[$nemonico]["nomliteral"]);
							$tbParametrosValor[$nemonico]["valor"]=$tbcte[$valor];
							break;
						case 4: // El valor lo toma directamente pero está codificado con urlencode
							$tbParametrosValor[$nemonico]["valor"]='<PRE>'.urldecode($valor).'</PRE>';
							break;
						case 5: // El valor es 0 ó 1 y se muestra NO o SI
							$tbSN[0]="No";
							$tbSN[1]="Si";
							$tbParametrosValor[$nemonico]["valor"]=$tbSN[$valor];
					}
				}
			}	
		}
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
	/*______________________________________________________________________
		Formatea un campo númerico con los puntos de las unidades de millar
		Parametros: 
			- cadena con el valor del campo
		Devuelve:
			- la cadena con los puntos de los miles
	________________________________________________________________________*/
	function formatomiles($cadena){
		$len=strlen($cadena);
		$cadenafinal="";
		$m=1;
		for($i=$len-1;$i>=0;$i--){
			$cadenafinal=substr($cadena,$i,1).$cadenafinal;
			if($m%3==0 && $i>0){
					$cadenafinal=".".$cadenafinal;
					$m=0;
			}
			$m++;
		}
		return($cadenafinal);
	}
	/*______________________________________________________________________
		Devuelve la url de la imagen y la descripción de un ámbito 
		Parametros: 
			- ambito: Identificador del ambito
			- urlimg: Por referencia. Es donde se devuelve la url de la imagen	
			- textambito: Por referencia. Es donde se devuelve la descripción
			
		Devuelve:
			- Los dos parámetros pasados por referencia
	________________________________________________________________________*/
	function tomaAmbito($ambito,&$urlimg,&$textambito)
	{
		global $AMBITO_CENTROS;
		global $AMBITO_GRUPOSAULAS;
		global $AMBITO_AULAS;
		global $AMBITO_GRUPOSORDENADORES;
		global $AMBITO_ORDENADORES;

		switch($ambito){
			case $AMBITO_CENTROS :
				$urlimg='../images/iconos/centros.gif';
				$textambito="Centros";
				break;
			case $AMBITO_GRUPOSAULAS :
				$urlimg='../images/iconos/carpeta.gif';
				$textambito="Grupos de aulas";
				break;
			case $AMBITO_AULAS :
				$urlimg='../images/iconos/aula.gif';
				$textambito="Aulas";
				break;	
			case $AMBITO_GRUPOSORDENADORES :
				$urlimg='../images/iconos/carpeta.gif';
				$textambito="Grupos de ordenadores";
				break;
			case $AMBITO_ORDENADORES :
				$urlimg='../images/iconos/ordenador.gif';
				$textambito="Ordenadores";
				break;
			default: 
				$urlimg='../images/iconos/ordenador.gif';
				$textambito="Ordenadores";
				break;			
		}	
	}
	/*______________________________________________________________________
	
		Devuelve la descripción de un ambito 
		Parametros: 
			-	cmd: Objeto comando (Operativo)
			- ambito: tipo de ambito
			- idambito: Identificador del ambito
			- textambito: Por referencia. Es donde se devuelve la descripción
			
		Devuelve:
			- Los dos parámetros pasados por referencia
	________________________________________________________________________*/
	
	function tomaDescriAmbito($cmd,$ambito,$idambito,$textambito)
	{
		global $AMBITO_CENTROS;
		global $AMBITO_GRUPOSAULAS;
		global $AMBITO_AULAS;
		global $AMBITO_GRUPOSORDENADORES;
		global $AMBITO_ORDENADORES;

			switch($ambito){
				case $AMBITO_CENTROS :
					$textambito=TomaDato($cmd,0,'centros',$idambito,'idcentro','nombrecentro');
					break;
				case $AMBITO_GRUPOSAULAS :
					$textambito=TomaDato($cmd,0,'grupos',$idambito,'idgrupo','nombregrupo');
					break;
				case $AMBITO_AULAS :
					$textambito=TomaDato($cmd,0,'aulas',$idambito,'idaula','nombreaula');
					break;
				case $AMBITO_GRUPOSORDENADORES :
					$textambito=TomaDato($cmd,0,'gruposordenadores',$idambito,'idgrupo','nombregrupoordenador');
					break;
				case $AMBITO_ORDENADORES :
					$textambito=TomaDato($cmd,0,'ordenadores',$idambito,'idordenador','nombreordenador');
					break;
				default: 	
					$textambito;					
			}
	}
	/*______________________________________________________________________
	
		Devuelve el código html de una etiqueta SELECT para un ámbito concreto
		Parametros: 
			- cmd: Objeto comando (Operativo)
			- ambito: tipo de ambito
			- idambito: Identificador del ambito
			- $idcentro: Centro donde pertenecen o 0 para todos
			- $wdth: Ancho del desplegable
		Devuelve:
			- Los dos parámetros pasados por referencia
	________________________________________________________________________*/
	
	function tomaSelectAmbito($cmd,$ambito,$idambito,$idcentro,$wdth)
	{
		global $AMBITO_CENTROS;
		global $AMBITO_GRUPOSAULAS;
		global $AMBITO_AULAS;
		global $AMBITO_GRUPOSORDENADORES;
		global $AMBITO_ORDENADORES;
		
		switch($ambito){
			case $AMBITO_CENTROS :
				$selecHtml=HTMLSELECT($cmd,1,'centros',$idcentro,'idcentro','nombrecentro',$wdth);
				break;
			case $AMBITO_GRUPOSAULAS :
				$selecHtml=HTMLSELECT($cmd,$idcentro,'grupos',$idambito,'idgrupo','nombregrupo',$wdth,"","","tipo=".$AMBITO_GRUPOSAULAS);
				break;
			case $AMBITO_AULAS :
				$selecHtml=HTMLSELECT($cmd,$idcentro,'aulas',$idambito,'idaula','nombreaula',$wdth);
				break;
			case $AMBITO_GRUPOSORDENADORES :
				$selecHtml=HTMLSELECT($cmd,0,'gruposordenadores',$idambito,'idgrupo ','nombregrupoordenador',$wdth);
				break;
			case $AMBITO_ORDENADORES :
				$clsWhere=" idaula IN (SELECT idaula FROM aulas WHERE idcentro=".$idcentro.")";
				$selecHtml=HTMLSELECT($cmd,0,'ordenadores',$idambito,'idordenador','nombreordenador',$wdth,"","",$clsWhere);
				break;
			default: 	
				$selecHtml="";					
		}
		return($selecHtml);	
	}
