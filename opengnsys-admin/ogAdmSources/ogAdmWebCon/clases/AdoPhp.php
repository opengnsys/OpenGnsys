<?php
/*================================================================================
Clase para conectar con una base de datos.

Especificaciones:
	- Estado de la conexión($estado)
		0: No conectado
		1: Conectado
		2: Se est�intentando conectar

================================================================================*/

class Conexion{
	var $basedatos;					// Base de datos
	var $servidor;					// Servidor de Base de datos
	var $usuario;					// Nombre de usuario
	var $password;					// Clave de usuario
	var $controlador;				// Controlador
	var $estado;					// Estado de la conexion
	var $proveedor;					// Proveedor de BD
	var $error;						// Colecci� de errores ocurridos durante el proceso (C�igo de error)
	var $ultimoerror;				// Ultimo error detectado
	var $inderror;					// Nmero de errores ocurridos durante el proceso
	var $msgerrores=array(
		"No se ha producido ningn error",
		"001 : conexiónError - La conexion no se pudo establecer",
		"002 : conexiónError - Se estableci� la conexióncon el servidor pero la base de datos no responde",
		"003 : conexiónError - No se ha podido cerrar la actual conexi�",
		"004 : conexiónError - El objeto est�ocupado intentando establecer una conexiónanterior",
		"005 : conexiónError - La conexiónya est�cerrada",
		"006 : conexiónError - No se ha especificado ningn servidor de base de datos",
		"007 : conexiónError - No se ha especificado ningn usuario de la base de datos",
		"008 : conexiónError - No se ha especificado password de usuario",
		"009 : conexiónError - No se ha especificado ninguna base de datos",
		"010 : conexiónError - No se ha especificado ningn proveedor de bases de datos",
	);
	/*--------------------------------------------------------------------------------------------*/
	function Conexion(){ // Constructor de la clase
		$this->inderror=0;
		$this->ultimoerror=0;
		$this->estado=0;
	} 
	/* -------------------------------------------------------------------------------------------
		Adquiere o actualiza los datos necesarias para establecer conexiones
		
		Par�etros de entrada:
			servidor: Servidor donde se ubica la base de datos
			usuario : Un usuario con acceso al servidor
			passwor : Clave de usuario
			basedato: Base de datos a la se quiere acceder
			proveedor: Proveedor de Base de datos

		Devuelve :
			true : Si los datos aportadospara establecer conexiones son correctos
			false: En caso contrario

		En el caso de devolver false, la funci� TomaUltimoError() devuelve el error ocurrido
	----------------------------------------------------------------------------------------------*/
	function CadenaConexion($servidor,$usuario,$password,$basedatos,$proveedor){
		$this->servidor=$servidor; 
		$this->usuario=$usuario;
		$this->password=$password;
		$this->basedatos=$basedatos;
		$this->proveedor=$proveedor;
		if (!$this->_cadena_conexion()) return(false); else return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Abre una conexión

		Devuelve :
			true : Si la apertura de la conexiónha sido satisfactoria
			false: En caso contrario

		En el caso de devolver false, la funci� TomaUltimoError() devuelve el error ocurrido
	----------------------------------------------------------------------------------------------*/
	function Abrir(){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		$MAXIMOS_INTENTOS_DE_CONECCION=10;
		if (!$this->_cadena_conexion()) return(false); // Comprueba si los datos necesarios para conexiones se han aportado
		switch ($this->estado) {
			case 1:	// Existe actualmente una conexiónabierta que se sustituir�por la nueva
				if (mysql_close($this->controlador)){ // Se cierra la conexion actual
					$this->estado=0;
					$intentos_de_conexion=0;
					while(true){
						$intentos_de_conexion++;
						$resul=($this->_nueva_conexion());
						if ($resul || $intentos_de_conexion>$MAXIMOS_INTENTOS_DE_CONECCION) return($resul);
						sleep(1); // Espera 1 segundo para intentar la conexiónde nuevo
					}
				}	
				else{ // Error al cerrar la conexi�
					$this->error[$this->inderror++]=3;
					$this->ultimoerror=3;
					return(false);
				}
				break;
			case 2:	// Actualmente est�objeto est�ocupado intentando establecer otra conexi�
				$this->error[$this->inderror++]=4;
				$this->ultimoerror=4;
				return(false);
				break;
			default : // No existe actualmente ninguna conexiónabierta, se abrir�una nueva
				$intentos_de_conexion=0;
				while(true){
					$intentos_de_conexion++;
					$resul=($this->_nueva_conexion());
					if ($resul || $intentos_de_conexion>$MAXIMOS_INTENTOS_DE_CONECCION) return($resul);
					sleep(1); // Espera 1 segundo para intentar la conexiónde nuevo
				}
		}
	}
	/* -------------------------------------------------------------------------------------------
		Cierra una conexión
		
		Devuelve :
			true : Si la conexiónse ha cerrado satisfactoriamente
			false: En caso contrario

		En el caso de devolver false, la funci� TomaUltimoError() devuelve el error ocurrido
	----------------------------------------------------------------------------------------------*/
	function Cerrar(){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		switch ($this->estado) {
			case 1:	// Actualmente la conexion est�abierta
				if (mysql_close($this->controlador)){ // Se cierra la conexion actual
					$this->estado=0;
					$this->error[$this->inderror++]=0;
					$this->ultimoerror=0;
					return(true);
				}
				else{ // Error al cerrar la conexi�
					$this->error[$this->inderror++]=3;
					$this->ultimoerror=3;
					return(false);
				}
				break;
			case 2:	// Actualmente est�objeto est�ocupado intentando establecer otra conexi�
				$this->error[$this->inderror++]=4;
				$this->ultimoerror=4;
				return(false);
				break;

			default :	// Actualmente la conexiónest�ya cerrada
				$this->error[$this->inderror++]=5;
				$this->ultimoerror=5;
				return(false);
		}
	}
	/* -------------------------------------------------------------------------------------------
		Establece una nueva conexi�. Este m�odo es privado y s�o lo puede ejecutar la propia
		clase desde el m�odo pblico Abrir.
	----------------------------------------------------------------------------------------------*/
	function _nueva_conexion(){
		$this->estado=2;// Intenta la conexion
		if ($this->controlador=mysql_connect($this->servidor,$this->usuario,$this->password)){// Conexion O.K. 
			$this->estado=1; // La conexion con el servidor se estableci�			
			if (mysql_select_db($this->basedatos, $this->controlador)){// Base datos O.K.
				$this->error[$this->inderror++]=0;
				$this->ultimoerror=0;
				return(true);
			}
			else{ // Problemas con la base de datos
				$this->error[$this->inderror++]=2;
				$this->ultimoerror=2;
				if (mysql_close ($this->controlador)) $this->estado=0; // Se cierra la conexion
				return(false); 
			}
		}
		else{ // Problemas con la conexion
			$this->estado=0;
			$this->error[$this->inderror++]=1;
			$this->ultimoerror=1;
			return(false); 
		}
	}
	/* -------------------------------------------------------------------------------------------
		Revisa y detecta las condiciones que deben cumplir los datos necesarios para establecer 
		conexiones

		Devuelve :
			true : Si los datos aportados son correctos
			false: Si algn dato NO ha sido aportado o es incorrecto
		
		Este m�odo es privado y s�o lo ejecutan m�odos pblicos de la propia clase
	----------------------------------------------------------------------------------------------*/
	function _cadena_conexion(){

		if ($this->servidor==null){
			$this->error[$this->inderror++]=6; // Servidor no establecido
			$this->ultimoerror=6;
			return(false);
		}
		if ($this->usuario==null){
			$this->error[$this->inderror++]=7;// usuario no establecido
			$this->ultimoerror=7;
			return(false);
		}
		if ($this->password==null){
			$this->error[$this->inderror++]=8; // password no establecido
			$this->ultimoerror=8;
			return(false);
		}
		if ($this->basedatos==null){
			$this->error[$this->inderror++]=9; // base de datos no establecido
			$this->ultimoerror=9;
			return(false);
		}
		if ($this->proveedor==null){
			$this->error[$this->inderror++]=10; // proveedor no establecido
			$this->ultimoerror=10;
			return(false);
		}
		$this->error[$this->inderror++]=0; // Datos de conexióncorrectos
		$this->ultimoerror=0;
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve el c�igo del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function UltimoError(){
		return($this->ultimoerror);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve una cadena con el mensage del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function DescripUltimoError(){
		return($this->msgerrores[$this->ultimoerror]);
	}
}
/*=========================================================================================
	Clase para usarla con la clase comando.

	Especificaciones:
	
		Esta clase tiene dos propiedades que definen su contenido
			nombre=nombre del parametro
			valor = valor de dicho par�etro
			tipo = tipo de parametro:
						0: El valor del par�etro debe ir encerrado entre comillas simples
						1: El valor del par�etro no necesita ir entre comillas simples
========================================================================================*/
class parametro{
	var $nombre;
	var $valor;
	var $tipo;
	/*--------------------------------------------------------------------------------------------*/
	function parametro($nombre="SinNombre",$valor="",$tipo="0"){ // Constructor de la clase
		$this->SetParametro($nombre,$valor,$tipo);
	}
	/* -------------------------------------------------------------------------------------------
		Modifica los valores de las propiedades de la clase
	----------------------------------------------------------------------------------------------*/
	function SetParametro($nombre,$valor,$tipo){
		$this->nombre=$nombre;
		$this->valor=$valor;
		$this->tipo=$tipo;
		if($tipo==1 && empty($valor)) $this->valor=0;
	}
}
/*==========================================================================================
	Clase para manipular bases de datos a traves de una conexiónprevia.

	Especificaciones:
	
		Las sentencias SQL pueden contener par�etros que pueden ser sustituidos por el valor
		de los objetos par�etro. Estos par�etros tendr� la forma:@nombre_del_parametro
==================================================================================================*/
class Comando{
	var $texto;
	var $Conexion;
	var $parametros=array();
	var $Recordset;
	var $resul;
	var $error;						// Error
	var $ultimoerror;				// Ultimo error detectado
	var $inderror;					// Contador de errores
	var $msgerrores=array(
		"No se ha producido ningn error",
		"001 : Comando Error - No se ha establecido el texto del comando",
		"002 : Comando Error - No se ha establecido la conexióndel comando",
		"003 : Comando Error - No se ha abierto la conexi�",
		"004 : Comando Error - La sentencia SQl del comando no es correcta",
		"005 : Comando Error - No se ha podido recuperar el valor @@identity de la ltima clave insertada",
	);	
	/*--------------------------------------------------------------------------------------------*/
	function Comando(){ // Constructor de la clase
		$this->inderror=0;
		$this->ultimoerror=0;
		$this->Recordset=new Recordset;
	} 
	/* -------------------------------------------------------------------------------------------
		Devuelve el c�igo del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function UltimoError(){
		return($this->ultimoerror);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve una cadena con el mensage del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function DescripUltimoError(){
		return($this->msgerrores[$this->ultimoerror]);
	}
	/* -------------------------------------------------------------------------------------------
		A�de un par�etro a la colecci� de parametros. La matriz que implementa la colecci�
		es una matriz asociativa cuyo indice asociativo es el nombre del par�etro
		
		Par�etros de entrada:
			objparam: Un objeto parametro
	---------------------------------------------------------------------------------------------*/
	function AddParametro($objparam){
		$tbparametro["nombre"]=$objparam->nombre;
		$tbparametro["valor"]=$objparam->valor;
		$tbparametro["tipo"]=$objparam->tipo;
		$this->parametros[]=$tbparametro;
	} 
	/* -------------------------------------------------------------------------------------------
		A�de un par�etro a la colecci� de parametros. La matriz que implementa la colecci�
		es una matriz asociativa cuyo indice asociativo es el del par�etro
		
		Par�etros de entrada:
			nombre: El nombre del par�etro
			valor : El valor del par�etro
			tipo = tipo de parametro:
						0: El valor del par�etro debe ir encerrado entre comillas simples
						1: El valor del par�etro no necesita ir entre comillas simples


	---------------------------------------------------------------------------------------------*/
	function CreaParametro($nombre,$valor,$tipo){
		for($i=0;$i<sizeof($this->parametros);$i++){
			if($this->parametros[$i]["nombre"]==$nombre){
				$this->parametros[$i]["valor"]=$valor;
				return;
			}
		}	
		$p = new parametro($nombre,$valor,$tipo);
		$this->AddParametro($p);
	}

	/* -------------------------------------------------------------------------------------------
		Sustituye el valor de un par�etro existente por otro
		Par�etros de entrada:
			nombre: El nombre del par�etro
			valor : El nuevo valor del par�etro
	---------------------------------------------------------------------------------------------*/
	function ParamSetValor($nombre,$valor){
		for($i=0;$i<sizeof($this->parametros);$i++){
			if($this->parametros[$i]["nombre"]==$nombre)
				$this->parametros[$i]["valor"]=$valor;
		}
	}
	/* -------------------------------------------------------------------------------------------
		Establece la conexiónque se usar�para ejecutar las acciones pertinentes

		Par�etros de entrada:
			objconexion: Un objeto conexion
	---------------------------------------------------------------------------------------------*/
	function EstableceConexion($objconexion){
		$this->Conexion= $objconexion;
	}
	/* -------------------------------------------------------------------------------------------
		Establece la conexiónque se usar�para ejecutar las acciones pertinentes

		Par�etros de entrada:
			textocomando: Un texto con la sentencia SQL (Puede contener par�etros)
	---------------------------------------------------------------------------------------------*/
	function EstableceTexto($textocomando){
		$this->texto=$textocomando;
	}
	/* -------------------------------------------------------------------------------------------
		Sustituye el valor de los parametros en la expresi� que forma el texto del Comando
	---------------------------------------------------------------------------------------------*/
	function Traduce(){
		$execomando=$this->texto;
		if (sizeof($this->parametros)>0){ // Hay par�etros que sustituir
			foreach($this->parametros as $parametro){
				if ($parametro["tipo"]==0) // Tipo alfanum�ico
					$execomando=str_replace($parametro["nombre"],"'".$parametro["valor"]."'",$execomando);
				else
					$execomando=str_replace($parametro["nombre"],$parametro["valor"],$execomando);
			}
		}
		$this->texto=$execomando;
	}
	/* -------------------------------------------------------------------------------------------
		Ejecuta la sentencia SQL contenida en la propiedad texto
	---------------------------------------------------------------------------------------------*/
	function Ejecutar(){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		if ($this->texto==null){
			$this->error[$this->inderror++]=1; // Texto no especificado
			$this->ultimoerror=1;
			return(false);
		}
		else{
			if ($this->Conexion==null){
				$this->error[$this->inderror++]=2; // conexiónNO establecida
				$this->ultimoerror=2;
				return(false);
			}
			else{
				if ($this->Conexion->estado==0){
					$this->error[$this->inderror++]=3; // conexiónNO abierta
					$this->ultimoerror=3;
					return(false);
				}
			}
		}
		$this->Traduce();
		if (!$this->resul=mysql_query($this->texto,$this->Conexion->controlador)){
			$this->error[$this->inderror++]=4; // Error en la sentencia SQL del comando
			$this->ultimoerror=4;
			return(false);
		}
		if (stristr($this->texto,"select")){
			$this->Recordset->Inicializar();
			$this->Recordset->filas=$this->resul;
			$this->Recordset->numerodecampos=mysql_num_fields($this->Recordset->filas);
			$this->Recordset->numeroderegistros=mysql_num_rows($this->Recordset->filas); 
			if ($this->Recordset->numeroderegistros>0){
				$this->Recordset->BOF=false;
				$this->Recordset->EOF=false;
				$this->Recordset->campos=mysql_fetch_array($this->Recordset->filas);
			}
		}

		$this->error[$this->inderror++]=0; // Comando ejecutado correctamante
		$this->ultimoerror=0;
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Esta funci� recupera el ltimo nmero asignado a una clave autonum�ica de una tabla
	---------------------------------------------------------------------------------------------*/
	function Autonumerico(){
		$ulreg=mysql_insert_id();
		return($ulreg);
	}
}
/*=========================================================================================
	Clase para consultar tablas y vistas de una base de datos.

	Especificaciones:
		- Estado del recordset ($estado)
		0: Cerrado
		1: Abierto
=========================================================================================*/
class Recordset{
	var $Comando;
	var $filas= array();
	var $BOF,$EOF,$estado;
	var $campos;
	var $numeroderegistros,$numerodecampos,$posicion;

	var $error;						// Error
	var $ultimoerror;				// Ultimo error detectado
	var $inderror;					// Contador de errores
	var $msgerrores=array(
		"No se ha producido ningn error",
		"001 : Recordset Error - Comando no establecido",
		"002 : Recordset Error - No se ha establecido la conexióndel comando",
		"003 : Recordset Error - No se ha abierto la conexi�",
		"004 : Recordset Error - No se pudo abrir la consulta",
		"005 : Recordset Error - La sentencia SQl del comando no contiene la clausula SELECT",
		"006 : Recordset Error - No se puede liberar la consulta",
	);	
	/*--------------------------------------------------------------------------------------------*/
	function Recordset(){ // Constructor de la clase
		$this->Inicializar();
	}
	/* -------------------------------------------------------------------------------------------
		Inicializa propiedades de las clase
	----------------------------------------------------------------------------------------------*/
	function Inicializar(){
		$this->BOF=true;
		$this->EOF=true;
		$this->posicion=0;
		$this->numeroderegistros=0;
		$this->numerodecampos=0;
		$this->estado=0;
	} 
	/* -------------------------------------------------------------------------------------------
		Devuelve el c�igo del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function UltimoError(){
		return($this->ultimoerror);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve una cadena con el mensage del ltimo error ocurrido durante el proceso anterior.
	----------------------------------------------------------------------------------------------*/
	function DescripUltimoError(){
		return($this->msgerrores[$this->ultimoerror]);
	}
	/* -------------------------------------------------------------------------------------------
		Establece el comando que se usar�para ejecutar las consultas pertinentes

		Par�etros de entrada:
			objcomando: Un objeto comando con la sentencia SQL (Puede contener par�etros)

		Devuelve :
			true : Si el texto del comando contiene la clausula SELECT
			false: En caso contrario

		En el caso de devolver false, la funci� TomaUltimoError() devuelve el error ocurrido
	---------------------------------------------------------------------------------------------*/
	function EstableceComando($objcomando){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		if (stristr($objcomando->texto,"select")){
			$this->Comando=$objcomando;
			$this->error[$this->inderror++]=0; // Comando v�ido, contiene "SELECT"
			$this->ultimoerror=0;
			return(true);
		}
		else{
			$this->error[$this->inderror++]=5; // Comando no valido, NO contiene "SELECT"
			$this->ultimoerror=5;
			return(false);
		}
	}
	/* -------------------------------------------------------------------------------------------
		Sustituye el valor de los parametros en la expresi� que forma el texto del Comando
	---------------------------------------------------------------------------------------------*/
	function Traduce(){
		$execomando=$this->Comando->texto;
		if (sizeof($this->Comando->parametros)>0){ // Hay par�etros que sustituir
			foreach($this->Comando->parametros as $parametro){
				if ($parametro["tipo"]==0) // Tipo alfanum�ico
					$execomando=str_replace($parametro["nombre"],"'".$parametro["valor"]."'",$execomando);
				else
					$execomando=str_replace($parametro["nombre"],$parametro["valor"],$execomando);
			}
		}
		$this->Comando->texto=$execomando;
	}
	/* -------------------------------------------------------------------------------------------
		Recupera registros de la base de datos
	---------------------------------------------------------------------------------------------*/
	function Abrir(){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		if ($this->Comando==null){
			$this->error[$this->inderror++]=1; // Comando no especificado
			$this->ultimoerror=1;
			return(false);
		}
		else{
			if ($this->Comando->Conexion==null){
				$this->error[$this->inderror++]=2; // conexiónNO establecida
				$this->ultimoerror=2;
				return(false);
			}
			else{
				if ($this->Comando->Conexion->estado==0){
					$this->error[$this->inderror++]=3; // conexiónNO abierta
					$this->ultimoerror=3;
					return(false);
				}
			}
		}
		$this->Traduce();
		$this->Inicializar();
		if (!$this->filas=mysql_query($this->Comando->texto,$this->Comando->Conexion->controlador)){
			$this->error[$this->inderror++]=4; // Error en la sentencia SQL del comando o al abrir la consula
			$this->ultimoerror=4;
			return(false);
		}
		$this->numeroderegistros=mysql_num_rows($this->filas); // La consulta se ha realizado con �ito
		$this->numerodecampos=mysql_num_fields($this->filas);
		if ($this->numeroderegistros>0){
			$this->BOF=false;
			$this->EOF=false;
			$this->campos=mysql_fetch_array($this->filas);
		}
		$this->estado=1; // Recordset abierto
		$this->error[$this->inderror++]=0; // Recuperaci� de registros correcta
		$this->ultimoerror=0;
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Libera los registros de una consulta de la base de datos
	---------------------------------------------------------------------------------------------*/
	function Cerrar(){
		$this->inderror=-1; // Inicializar contador de errores
		$this->ultimoerror=-1;
		if (!mysql_free_result($this->filas)){
			$this->error[$this->inderror++]=6; // Error al cerrar la consulta (Al liberar memoria)
			$this->ultimoerror=6;
			return(false);
		}
		$this->Inicializar();
		$this->error[$this->inderror++]=0; // Recuperaci� de registros correcta
		$this->ultimoerror=0;
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Mueve el puntero de lectura al siguiente registro del recordset
	---------------------------------------------------------------------------------------------*/
	function Siguiente(){
		if (!$this->EOF){
			$this->posicion++;
			if ($this->posicion==$this->numeroderegistros)
				$this->EOF=true;
			else{
				if (mysql_data_seek($this->filas,$this->posicion))
					$this->campos=mysql_fetch_array($this->filas);
			}
		}
	}
	/* -------------------------------------------------------------------------------------------
		Mueve el puntero de lectura al anterior registro del recordset
	---------------------------------------------------------------------------------------------*/
	function Anterior(){
		if (!$this->BOF){
			$this->posicion--;
			if ($this->posicion<0)
				$this->BOF=true;
			else{
				if (mysql_data_seek($this->filas,$this->posicion));
					$this->campos=mysql_fetch_array($this->filas);
			}
		}
	}
	/* -------------------------------------------------------------------------------------------
		Mueve el puntero de lectura al primer registro del recordset
	---------------------------------------------------------------------------------------------*/
	function Primero(){
		if ($this->numeroderegistros>0){
			$this->posicion=0;
			if (mysql_data_seek($this->filas,$this->posicion))
				$this->campos=mysql_fetch_array($this->filas);
		}
	}
	/* -------------------------------------------------------------------------------------------
		Mueve el puntero de lectura al ltimo registro del recordset
	---------------------------------------------------------------------------------------------*/
	function Ultimo(){
		if ($this->numeroderegistros>0){
			$this->posicion=$this->numeroderegistros-1;
			if (mysql_data_seek($this->filas,$this->posicion))
				$this->campos=mysql_fetch_array($this->filas);
		}
	}
}	
?>