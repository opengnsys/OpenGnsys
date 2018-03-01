<?php

include_once("EncripDescrip.php");

/*================================================================================
	Clase para conectarse con el servidor hidra y enviar comandos
	Cualquier error producido en los procesos se puede recuperar con los m�odos
================================================================================*/
class SockHidra{
	var $ultimoerror;				// Ultimo error detectado
	var $descripultimoerror;		// Descripción del ltimo error detectado
	var $socket;					// Stream socket
	var $servidor;					// El servidor hidra
	var $puerto;						// El puerto odnde se conectar�
	var $timeout;					// El tiempo de espera para la conexi�
	var $encripdescrip;     // El encriptador
	var $LONGITUD_TRAMA; // M�ima longitud de la trama
	
	//________________________________________________________________________________________
	//
	//  Constructor
	// Parámetros:
	//	- servidor: El nombre o la IP del servidor
	//	- puerto: El puerto usado para las comunicaciones
	//	- timeout: El tiempo de espera para la conexi�
	//________________________________________________________________________________________
	function __construct($servidor, $puerto, $timeout=30){
		$this->servidor=$servidor;
		if (!$this->_esIP($this->servidor))
			$this->servidor = gethostbyname ($servidor);
		$this->puerto=$puerto;
		$this->timeout=$timeout;
		$this->LONGITUD_TRAMA=4048;

		$this->encripdescrip=new EncripDescrip();
	}
	//________________________________________________________________________________________
	//
	// Averigua si el parametro pasado es una IP. devuelve true en caso afirmativo
	//________________________________________________________________________________________
	function _esIP(){
		return(false);
	}
	//________________________________________________________________________________________
	//
	//	Conecta con el servidor 
	//	Devuelve:
	//		- false: Si falla la conexi�
	//		- true: En caso contrario
	//________________________________________________________________________________________
	function conectar(){ 
		$this->socket = socket_create (AF_INET, SOCK_STREAM, 0);
		if ($this->socket < 0) {
			$this->ultimoerror=socket_strerror($socket);
			$this->descripultimoerror="socket_create() fallo";
			return(false);
		}
		$result = socket_connect ($this->socket,$this->servidor,$this->puerto);
		if ($result < 0) {
			$this->ultimoerror=socket_strerror($result);
			$this->descripultimoerror="socket_connect() fallo";
			return(false);
		}
		return(true);
	}
	//________________________________________________________________________________________
	//
	//	Cerrar la conexióncon el servidor 
	//	Devuelve:
	//		- false: Si falla la conexi�
	//		- true: En caso contrario
	//________________________________________________________________________________________
	function desconectar(){
		socket_close ($this->socket);
	}
	//________________________________________________________________________________________
	//
	//		Devuelve el c�igo del ltimo error ocurrido durante el proceso anterior.
	//________________________________________________________________________________________
	function UltimoError(){
		return($this->ultimoerror);
	}
	//________________________________________________________________________________________
	//
	//		Devuelve una cadena con el mensage del ltimo error ocurrido durante el proceso anterior.
	//________________________________________________________________________________________
	function DescripUltimoError(){
		return($this->descripultimoerror);
	}
	//________________________________________________________________________________________
	//
	//	Envia una petición de comando al servidor 
	//	Parámetros:
	//		- Parámetros: Parámetros del mensaje
	//________________________________________________________________________________________
	function envia_comando($parametros)
	{
		global $MSG_COMANDO;
		
		$tipo=$MSG_COMANDO;
		return($this->envia_trama($parametros,$tipo));
	}
	//________________________________________________________________________________________
	//
	//	Envia una petición de información al servidor 
	//	Parámetros:
	//		- Parámetros: Parámetros del mensaje
	//________________________________________________________________________________________
	function envia_peticion($parametros)
	{
		global $MSG_PETICION;

		$tipo=$MSG_PETICION;
		return($this->envia_trama($parametros,$tipo));
	}
	//________________________________________________________________________________________
	//
	//	Envia un mensaje al servidor 
	//	Parámetros:
	//		- trama: Trama a enviar
	//		- tipo: Tipo de mensaje
	//________________________________________________________________________________________
	function envia_trama($parametros,$tipo)
	{
		global $LONHEXPRM;
		global $LONCABECERA;
		
		$arroba="@";
		$identificador="JMMLCAMDJ_MCDJ";
		
		$lonprm=strlen($parametros);
		/* Encripta los parámetros */
		$parametros=$this->encripdescrip->Encriptar($parametros,$lonprm);
		/* Pasa a hexadecimal la longitud de los parámetros ya encriptados para incluirla dentro de la cabecera */
		$hlonprm=str_pad(dechex($LONCABECERA+$LONHEXPRM+$lonprm),$LONHEXPRM,"0",STR_PAD_LEFT);	// Rellena con ceros 									

		$trama=$arroba.$identificador.$tipo.$hlonprm.$parametros; 
		$resul=socket_write($this->socket,$trama,$LONCABECERA+$LONHEXPRM+$lonprm);
		if (!$resul) {
			$this->ultimoerror=socket_strerror($resul);
			$this->descripultimoerror="socket_write() fallo";
			return(false);
		}
		return(true);
	}
	//________________________________________________________________________________________
	//
	//	Recibe una trama del servidor 
	//	Parámetros:
	//		- lon: Longitud de la trama
	// Devuelve:
	//		La trama recibida
	//________________________________________________________________________________________
	function recibe_respuesta()
	{
		global $LONHEXPRM;
		global $LONCABECERA;
		global $LONBLK;

		$lon=$lSize=0;
		$buffer="";
		$cadenaret="";
		do{
			$bloque = socket_read ($this->socket,$LONBLK);// Lee bloque
			$buffer.=$bloque; // Añade bloque
			$lon+=strlen($bloque);
			if($lSize==0){ // Comprueba tipo de trama y longitud total de los parámetros
				if (substr($buffer,0,15)!="@JMMLCAMDJ_MCDJ")
					return($cadenaret); // No se reconoce la trama
				$hlonprm=hexdec(substr($buffer,$LONCABECERA,$LONHEXPRM));
				$lSize=$hlonprm; // Longitud total de la trama con los parametros encriptados
			}
		}while($lon<$lSize);
	
		$lon=$lSize-($LONCABECERA+$LONHEXPRM); // Longitud de los parametros aún encriptados
		$parametros=substr($buffer,$LONCABECERA+$LONHEXPRM,$lon); // Parametros encriptados
		$parametros=$this->encripdescrip->Desencriptar($parametros,$hlonprm); // Parametros sin encriptar
		$hlonprm=str_pad(dechex($lon),$LONHEXPRM,"0",STR_PAD_LEFT);	// Rellena con ceros 									
		$cadenaret=substr($buffer,0,$LONCABECERA).$hlonprm.$parametros;
		return($cadenaret);
	}
 }
?>
