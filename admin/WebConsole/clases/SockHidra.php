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
	// Par�etros:
	//	- servidor: El nombre o la IP del servidor
	//	- puerto: El puerto usado para las comunicaciones
	//	- timeout: El tiempo de espera para la conexi�
	//________________________________________________________________________________________
	function SockHidra($servidor,$puerto,$timeout=30){ 
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
	//	Envia una trama de comando al servidor 
	//	Par�etros:
	//		- trama: Trama a enviar
	//________________________________________________________________________________________
	function envia_comando($parametros){
		$trama="@JMMLCAMDJ".$parametros;
		$resul=socket_write($this->socket, $this->encripdescrip->Encriptar($trama), strlen($trama));
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
	//	Par�etros:
	//		- lon: Longitud de la trama
	// Devuelve:
	//		La trama recibida
	//________________________________________________________________________________________
	function recibe_respuesta(){
		$trama = socket_read ($this->socket,$this->LONGITUD_TRAMA);
		$cadenaret=$this->encripdescrip->Desencriptar($trama);
		return($cadenaret);
	}
 }
?>
