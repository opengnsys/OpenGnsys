<?php
/*================================================================================
	Clase para encriptar y desencriptar cadenas 
================================================================================*/
class EncripDescrip{
	var $cadena;					// La cadena encriptada o desencriptada que ser�devuelta
	var $clave;						// La clave de la cadena  encriptada o desencriptada que ser�devuelta
	//________________________________________________________________________________________
	//
	//  Constructor
	//________________________________________________________________________________________
	function EncripDescrip($clave=12){ 
		$this->cadena="";
		$this->clave=$clave;
	}
	// ____________________________________________________________________________
	//
	//		Encripta una cadena 
	//_____________________________________________________________________________
	function Encriptar($cadena){
		
		return( $cadena);
		
		$clave=(int)$this->clave;
		$clave = (int)$clave  & 0xFF; 
		$lon=strlen($cadena);
		$this->cadena="";
		for($i=0;$i<$lon;$i++){
			$ch=(int)ord($cadena[$i]);
			$pot=(int)$ch^(int)$clave;
			$this->cadena.=chr($pot);
		}
		return( $this->cadena);
	}
	// ____________________________________________________________________________
	//
	//		Desencripta una cadena 
	//_____________________________________________________________________________
	function Desencriptar($cadena){
	
		return( $cadena);	
	
		$clave=(int)$this->clave;
		$clave = (int)$clave  & 0xFF; 
		$lon=strlen($cadena);
		$this->cadena="";
		for($i=0;$i<$lon;$i++){
			$ch=(int)ord($cadena[$i]);
			$pot=(int)$ch^(int)$clave;
			$this->cadena.=chr($pot);
		}
		return( $this->cadena);
	}
}
?>