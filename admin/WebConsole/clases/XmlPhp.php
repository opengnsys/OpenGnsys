<?php
/*================================================================================
Esta clase implementa funciones de utilidad para tratar ficheros XML

	Parametros del constructor:
		fxml=Fichero XML que contiene los atributos de los nodos
		fileocade=Indica si el dato anterior es un fichero o una variable con el contenido del árbol:
			0: Es una cadena
			1: Es un fichero

	Especificaciones:
		Se le llama información del nodo al nombre del nodo + sus atributos eliminando los marcadores
		de comienzo:"<" y fin:">"
================================================================================*/
class XmlPhp{
	var $buffer;
	var $nptr;

	function __construct($fxml, $fileocade){ // Constructor
		if ($fileocade==0){
			$this->nptr=1;
			$this->buffer=trim($fxml);
		}
		else{
			$tbuffer=filesize($fxml); // Calcula tamaño del fichero
			if ($tbuffer>0){ // EL fichero tiene contenido
				$fd=fopen($fxml, "r");
				$this->buffer=fread ($fd,$tbuffer);
				fclose ($fd);
				$this->nptr=1;
				$this->buffer=trim($this->buffer);
			}
		}
		$this->buffer=preg_replace("/[\n\r\t]/"," ", $this->buffer );
	}
	/* -------------------------------------------------------------------------------------------
		Recupera la información del primer nodo (nodo raiz) del arbol.Devuelve false en caso de que
		no tenga hijos o bien no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function InfoNodoRaiz(){
		if (!$this->NodoRaiz()) // No existe documento XML
			return(false);
		return($this->Infonodo());
	}
	/* -------------------------------------------------------------------------------------------
		Establece el puntero de nodos al primer nodo del árbol (nodo raiz). Devuelve  false en caso
		de que no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function NodoRaiz(){
		if ($this->buffer==null) return(false); // No existe documento XML
		$this->nptr=0;
		while ($this->nptr<strlen($this->buffer))
			if ('<'==substr($this->buffer,$this->nptr++,1)) return(true);
		return(false);
	}
	/* -------------------------------------------------------------------------------------------
		Recupera la información del primer nodo hijo del nodo actual. Devuelve false en caso de que
		no tenga hijos o bien no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function InfoPrimerNodoHijo(){
		if (!$this->PrimerNodoHijo()) // No tiene hijos o no existe documento XML
			return(false);
		return($this->Infonodo());
	}
	/* -------------------------------------------------------------------------------------------
		Establece el puntero de nodos al primer nodo hijo del nodo actual. Devuelve  false en caso
		de que no tenga hijos o bien no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function PrimerNodoHijo(){
		if ($this->buffer==null) return(false); // No existe documento XML
		$gnptr=$this->nptr;
		while ($this->nptr<strlen($this->buffer))
			if ('<'==substr($this->buffer,$this->nptr++,1)) break;
			$lon=$this->nptr;
			if ('/'==substr($this->buffer,$lon,1)){ // No tiene hijos
			$this->nptr=$gnptr;
			return(false);
		}
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Recupera la información del siguiente nodo hermano del actual. Devuelve false en caso de que
		el nodo actual sea el último de sus hermanos o bien no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function InfoSiguienteNodoHermano(){
		if (!$this->SiguienteNodoHermano()) // No tiene hermanos o no existe documento XML
			return(false);
		return($this->Infonodo());
	}
	/* -------------------------------------------------------------------------------------------
		Establece el puntero de nodos al siguiente nodo hermano del nodo actual. Devuelve  false en 
		caso de que el nodo actual sea el último de los hermanos o bien no exista documento XML que analizar.
	---------------------------------------------------------------------------------------------*/
	function SiguienteNodoHermano(){
		if ($this->buffer==null) return(false); // No existe documento XML
		$gnptr=$this->nptr;
		$resul=$this->_siguiente_hermano();
		if (!$resul){
			$this->nptr=$gnptr; // Es el último hermano
			return(false);
		}
		return(true);
	}
	/* -------------------------------------------------------------------------------------------
		Establece el puntero de nodos al siguiente nodo hermano del actual
	---------------------------------------------------------------------------------------------*/
	function _siguiente_hermano(){
		$lon=$this->nptr;
		$sw=1;
		$nombrenodo=$this->NombreNodo();
		while (1){
			$lon = strpos($this->buffer,'<',++$lon);
			if (substr($this->buffer,++$lon,1)=='/')
				$sw--;
			else
				$sw++;
			if ($sw==0){
				while ($lon<strlen($this->buffer)){
					if (substr($this->buffer,$lon++,1)=='<'){
						if (substr($this->buffer,$lon,1)=='/')
							return(false); // Es el último hermano
						else{
							$this->nptr=$lon;
							return(true);
						}
					}
				}
				return(false); // Se trata del nodo raiz
			}
		}
	}
	/* -------------------------------------------------------------------------------------------
		Recupera el número de hijos del nodo actual
	---------------------------------------------------------------------------------------------*/
	function NumerodeHijos(){
		$gnptr=$this->nptr;
		$nh=0;
		if ($this->PrimerNodoHijo()){
			$nh++;
			while ($this->SiguienteNodoHermano()) $nh++;
		}
		$this->nptr=$gnptr;
		return($nh);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve true si el nodo es el último de sus hermanos
	---------------------------------------------------------------------------------------------*/
	function EsUltimoHermano(){
		$gnptr=$this->nptr;
		if (!$this->SiguienteNodoHermano()){
			$this->nptr=$gnptr;
			return(true);
		}
		$this->nptr=$gnptr;
		return(false);
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve los atributos del nodo. 
		Parámetros:
			Si se aporta el puntero del nodo se devolverán los atributos del nodo apuntado
			pero si no se especifican argumentos se devuelven los atributos del nodo actual.
	---------------------------------------------------------------------------------------------*/
	function Atributos($ptrnodo=-1){
		if ($ptrnodo!=-1)
			$this->_setnodo($ptrnodo);
		$atributosHTML="";
		$info=$this->Infonodo();
		$pos=strpos($info," ");
		if ($pos) // El nodo tiene atributos
			$atributosHTML=" ".substr($info,$pos);
		return($atributosHTML);
	}
	/* -------------------------------------------------------------------------------------------
		Posiciona el puntero de nodos
	---------------------------------------------------------------------------------------------*/
	function _setnodo($ptrnodo){
		$this->nptr=$ptrnodo;
	}
	/* -------------------------------------------------------------------------------------------
		Devuelve el puntero del nodo actual
	---------------------------------------------------------------------------------------------*/
	function Nodo(){
		return($this->nptr);
	}
	/* -------------------------------------------------------------------------------------------
		Recupera el nombre del nodo
	---------------------------------------------------------------------------------------------*/
	function NombreNodo(){
		$infonodo=$this->Infonodo();
		$trozos=explode(" ",$infonodo);
		return ($trozos[0]);
	}
	/* -------------------------------------------------------------------------------------------
		Recupera la información del nodo actual
	---------------------------------------------------------------------------------------------*/
	function Infonodo(){
		if ($this->buffer==null) return(false); // No existe documento XML
		$lon=$this->nptr; 
		while ($lon<strlen($this->buffer))
			if ('>'==substr($this->buffer,++$lon,1)) break;
		$info=trim(substr($this->buffer,$this->nptr,$lon-$this->nptr));
		$info=str_replace("[","<",$info); 
		$info=str_replace("]",">",$info); 
		return $info;
	}
	/* -------------------------------------------------------------------------------------------
		Recorre el arbol de nodos del documento XML y para cada nodo genera un evento que se 
		puede capturar a través de una funcion que tiene esta forma:
			fNodoXML(nivel,infonodo) donde:
				- nivel es el nivel de profundidad del nodo (en base 0)
				- infonodo es toda la información contenida en el nodo.
	---------------------------------------------------------------------------------------------*/
	function RecorreArboXML(){
		if (!$this->NodoRaiz()) return; // No existe documento XML que analizar
		$this->_arbolXmlrecur(0);
	}
	// -------------------------------------------------------------------------------------
	// Recorrido recursivo del arbol XML 
	// -------------------------------------------------------------------------------------
	function _arbolXmlrecur($nivel){
		do{
			$infonodo=$this->Infonodo();
			fNodoXML($nivel,$infonodo); 
			$gnptr=$this->nptr;
			if ($this->PrimerNodoHijo())
				$this->_arbolXmlrecur($nivel+1);
			$this->nptr=$gnptr;
		}while($this->SiguienteNodoHermano());
	}
	/*------------------------------------------------------------------------------------------------
		Elimina un atributo de la información del nodo
			Parametros: 
				- nombreatributo:El nombre del atributo
				- info: La información del Nodo
	------------------------------------------------------------------------------------------------*/
	function EliminaAtributo($nombreatributo,$info){
		$nada="";
		return($this->TomaAtributo($nombreatributo,$nada,$info,true));
	}
	/*------------------------------------------------------------------------------------------------
		Recupera el valor del atributo y lo elimina de la información del nodo
			Parametros: 
				- nombreatributo:El nombre del atributo
				- puntero: Referencia a la variable que contendrá el valor del atributo
				- info: La información del Nodo
	------------------------------------------------------------------------------------------------*/
	function TomaAtributoEspecial($nombreatributo,&$puntero,$info){
		return($this->TomaAtributo($nombreatributo,$puntero,$info,true));
	}
	/*------------------------------------------------------------------------------------------------
		Recupera el valor del atributo 
			Parametros: 
				- nombreatributo:El nombre del atributo
				- puntero: Referencia a la variable que contendrá el valor del atributo
				- info: La información del Nodo
				- sw: Si vale true el atributo se eliminará de la información del nodo
	------------------------------------------------------------------------------------------------*/
	function TomaAtributo($nombreatributo,&$puntero,$info,$swkill=false){
		$doblescomillas='"';
		$strAtributo=" ".$nombreatributo."=";
		$pos=strpos($info,$strAtributo); 
		if (!$pos){
			$puntero=null;
			return($info);
		}
		$pos+=strlen($strAtributo);  // Avanza hasta el signo igual
		$posa=$pos; // Primera posición del valor del atributo
		$swcomillas=false;
		while ($pos<strlen($info)){
			if ($doblescomillas==substr($info,$pos,1)) $swcomillas=!$swcomillas;
			if (' '==substr($info,$pos,1) || '> '==substr($info,$pos,1))
				if (!$swcomillas) break;
			$pos++;
		}
		$posb=$pos;
		$valoratributo=substr($info,$posa,$posb-$posa);
		if ($swkill) // Eliminar el atributo de la la cadena 
			$info=str_replace($strAtributo.$valoratributo,"",$info); // Elimina el atributo de la información
		if ($doblescomillas==substr($valoratributo,0,1)) // Elimina las comillas
				$valoratributo=str_replace($doblescomillas,"",$valoratributo);
		$puntero=$valoratributo;
		return($info);
	}
} // Fin de la clase 
?>
