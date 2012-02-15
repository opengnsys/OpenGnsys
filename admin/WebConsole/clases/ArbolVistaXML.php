<?
include_once("XmlPhp.php");
/*===============================================================
Esta clase implementa la apariencia y comportamiento de un treeview en código HTML y javascript.
La clase utiliza la clase XmlPhp.php para acceder al documento XML. 

	Parámetros del constructor:
		-fXML=Fichero XML
		-fileocade=Indica si el dato anterior es un fichero o una variable con el contenido del árbol	
			0: Es una cadena
			1: Es un fichero
		-baseurlimg= Url base de las imágenes de los nodos de contracción-expansión
		-clasedefault=Clase por defecto de los literales de los nodos
		-nivelexp= Máximo nivel que aparecera expandido 
		-x= Posición x donde aparecerá la tabla 
		-y= Posición y donde aparecerá la tabla 
=================================================================*/
class ArbolVistaXML{
	var $baseurlimg;	// Base de la URL de las imágenes de los nodos de contracción-expansión
	var $clasedefault;	// Clase por defecto de los literales de los nodos
	var $gXML;			// Objeto gestor del documento XML
	var $nivelexp;		// Nivel máximo que aprecerá visible
	var $x;		// Posición x donde aparecerá la tabla 
	var $y;		// Posición y donde aparecerá la tabla 
	var $c_imgnivel=array();	// Imagenes de expansión y contracción para los distintos niveles
	var $v_imgnivel=array();	// Valores de expansión y contracción para los distintos niveles
	var $nodos_count;	// Contador de nodo

	Function ArbolVistaXML($fXML,$fileocade,$baseurlimg="/.images/",$clasedefault,$nivelexp=0,$x=0,$y=0,$tipotabla=0,$titulotabla=""){
		// Constructor
		$this->gXML=new XmlPhp($fXML,$fileocade);
		$this->baseurlimg=$baseurlimg;
		$this->clasedefault=$clasedefault;
		$this->nivelexp=$nivelexp;
		$this->x=$x;
		$this->y=$y;
		$this->tipotabla=$tipotabla;
		$this->titulotabla=$titulotabla;

		// Anchura de los distibtos tipos de tablas
		if($this->tipotabla>0){
			$this->anchoM=" width=100% ";
			$this->ancho=" width=100% ";
		}
		else{
			$this->anchoM=" width=100% ";
			$this->ancho="";
		}
		for($i=0;$i<=5;$i++){ // Inicializar 
			$this->c_imgnivel[$i]=$this->baseurlimg.'/nada.gif';
			$this->v_imgnivel[$i]="nada";
		}
		$this->nodos_count=0;
	}
	/*------------------------------------------------------------------------------------------------
		Esta función devuelve una cadena con el contenido de un treeview en código HTML
	------------------------------------------------------------------------------------------------*/
	Function CreaArbolVistaXML(){
		if (!$this->gXML->NodoRaiz()) return; // No existe documento XML que analizar
		$arbol_total='<TABLE  border=0 '.$this->anchoM.' class="'.$this->clasedefault. '" style="POSITION:absolute;TOP:'.$this->y.'px;LEFT:'.$this->x.'px" class=texto_arbol cellspacing=0 cellpadding=0 border=0>';
		if($this->tipotabla>0) $arbol_total.='<TR><TH>'. $this->titulotabla .'</TH></TR>';
			$arbol_total.='<TR style="display:block">';
				$arbol_total.='<TD>';
					$arbol_total.='<TABLE id=tablanivel-0 border=0 cellspacing=0 cellpadding="0">';
						$arbol_total.=$this->_arbolXmlrecur(0);
					$arbol_total.='</TABLE>';	
				$arbol_total.='</TD>';	
			$arbol_total.='</TR>';	
		$arbol_total.='</TABLE>';
		return($arbol_total);
	}
	// -------------------------------------------------------------------------------------
	// Recorrido recursivo del arbol XML
	//		Parámetros: 
	//			nivel: nivel del nodo
	// -------------------------------------------------------------------------------------
	function _arbolXmlrecur($nivel){
		$arbol_total="";
		if ($nivel+1>$this->nivelexp) $displai="none"; else $displai="block";
		do{
			$gnptr=$this->gXML->nptr;
			$arbol_total.='<TR style="display:block" id=TRNodo-'.$this->nodos_count.'>';
				$arbol_total.='<TD>';
					$arbol_total.=$this->_dibujo_nodo($this->gXML->Nodo(),$nivel,$this->gXML->NumerodeHijos(),$this->gXML->EsUltimoHermano());
				$arbol_total.='</TD>';
			$arbol_total.='</TR>';
			$this->nodos_count++;
			if ($this->gXML->PrimerNodoHijo()){
				$arbol_total.='<TR id="TRNodoHijo-'.$this->nodos_count.'" style="display:'.$displai.'">';
					$arbol_total.='<TD>';
						$arbol_total.='<TABLE id="tablanivel-'.($nivel+1).'" border=0 cellspacing=0 cellpadding=0>';
							$arbol_total.=$this->_arbolXmlrecur($nivel+1);
						$arbol_total.='</TABLE>';	
					$arbol_total.='</TD>';	
				$arbol_total.='</TR>';	
			}
			$this->gXML->nptr=$gnptr;
		}while($this->gXML->SiguienteNodoHermano());
		return($arbol_total);
	}
	// -------------------------------------------------------------------------------------
	// Crea un  nodo
	//		Parámetros: 
	//			nivel: nivel del nodo
	// -------------------------------------------------------------------------------------
	function CreaNodo($nivel){
		$nodo=$this->_dibujo_nodo($this->gXML->Nodo(),$nivel,0,true);
		return($nodo);
	}
	/*------------------------------------------------------------------------------------------------
		Dibuja los nodos del árbol 
			parámetros:
				nodo: La información del nodo
				nivel: Nivel del nodo
				nhijos: numero de hijos
				uhermano: Es true si el nodo es el último de sus hermanos

			Especificaciones:
				Los atributos de los nodos pueden ser HTML o especificos de
				esta aplicación. Lso atributos del nodo propios de ésta son:

					- clicksupimg: Función suplementaria de la imagen de signo
					- imagenid: Identificador de la imagen de signo
					- clickimg: La función que se ejecutará al hacer click sobre la imagen de nodo
					- downimg: La función que se ejecutará al pulsar el ratón  sobre la imagen de nodo
					- clickcontextualimg: Función que se ejecutara al hacer click con el boton derecho sobre la imagen del nodo
					- imagenodo: Es la url de la imagen de nodo
					- infonodo: Es texto que se visualiza del nodo
					- mouseovernodo: La función a ejecutar cuando se posa el ratón sobre el literal del nodo
					- clicksupnodo: Función suplementaria del literal del nodo
					- clickcontextualnodo: Función que se ejecutara al hacer click con el boton derecho sobre el nodo
					- classnodo: Clase (style) a  la que pertenece el nodo
					- nodoid: identificador del nodo
					- nodovalue: parametro value del nodo
	------------------------------------------------------------------------------------------------*/
	function _dibujo_nodo($nodo,$nivel,$nhijos,$uhermano){
		// Comprobar descendencia y posición dentro de los hermanos
		$swu=false; // switch para saber si el nodo es el último hermano
		$swh=false; // switch para saber si el nodo tiene hijos
		if ($nhijos>0) $swh=true;
		$swu=$uhermano;
		if ($swh){	// Si tiene hijos ..
			if ($swu){ // Si es el último de sus hermanos ..
				if ($nivel<$this->nivelexp){
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/menos_c.gif';
					$this->v_imgnivel[$nivel]="menos_c";
				}
				else{
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/mas_c.gif';
					$this->v_imgnivel[$nivel]="mas_c";
				}
			}
			else{		// Si NO lo es ..
				if ($nivel<$this->nivelexp){
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/menos_t.gif';
					$this->v_imgnivel[$nivel]="menos_t";
				}
				else{
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/mas_t.gif';
					$this->v_imgnivel[$nivel]="mas_t";
				}
			}
			if ($nivel==0){
				if ($this->nivelexp>0)
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/menos_root.gif';
				else
					$this->c_imgnivel[$nivel]=$this->baseurlimg.'/mas_root.gif';
			}
		}
		else{		// Si NO tiene hijos ..
			if ($swu){	// Si es el último de sus hermanos ..
				$this->c_imgnivel[$nivel]=$this->baseurlimg.'/nada_c.gif';
				$this->v_imgnivel[$nivel]="nada_c";
			}
			else{		// Si no lo es ..
				$this->c_imgnivel[$nivel]=$this->baseurlimg.'/nada_t.gif';
				$this->v_imgnivel[$nivel]="nada_t";
			}
		}
		// Fin Comprobar descendencia y posición dentro de los hermanos
		if($this->tipotabla==0)
			$arbol='<TABLE  border=0 cellspacing=0 cellpadding=0>';
		else
			$arbol='<TABLE style="BORDER-BOTTOM:#000000 1px solid;" border=0 cellspacing=0 cellpadding=0>';
		$arbol.='<TR height="16px">';
		$atributosHTML=" ";
		$atributosHTML=$this->gXML->Atributos($nodo);
		$colornodo="";
		$fondonodo="";
		$estilo="";
		$atributosHTML=$this->gXML->TomaAtributoEspecial("colornodo",$colornodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("fondonodo",$fondonodo,$atributosHTML);
		if ($colornodo!="" ) $colornodo=' COLOR:'.$colornodo.";";
		if ($fondonodo!="" ) $fondonodo=' BACKGROUND-COLOR:'.$fondonodo.";";
		$estilo=$colornodo || $fondonodo;
		if ($estilo!="" )	$estilo='style="'.$colornodo.$fondonodo.'"';

		for ($i=0;$i<$nivel;$i++){ // Niveles previos
			$arbol.='<TD  '.$estilo.'width="3px"></TD>';
			$arbol.='<TD  '.$estilo.' width="16px"><IMG src="'.$this->c_imgnivel[$i].'" width="16px" height="16px" ></TD>';
		}
		$arbol.='<TD  '.$estilo.' width="3px"></TD>'; // Desplazamiento de la imagen
		$arbol.='<TD  '.$estilo.' width="16px">';
		
		$imagenid="";
		$clicksupimg="";
		$atributosHTML=$this->gXML->TomaAtributoEspecial("imagenid",$imagenid,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("clicksupimg",$clicksupimg,$atributosHTML);
		if ($clicksupimg!="") $clicksupimg.=';';
		if ($swh){ // El nodo tiene hijos
			$arbol.='<A href="#nodo"><IMG border=0 '.$estilo.' id="'.$imagenid.'" onclick="clickImagenSigno(this,' ."'".$this->baseurlimg."'".','.$nivel.');'.$clicksupimg.'"  src="'.$this->c_imgnivel[$nivel].'" width="16px" height="16px" value="'.$this->v_imgnivel[$nivel].'"></A></TD>';
		}
		else
			$arbol.='<SPAN><IMG  '.$estilo.' id="'.$imagenid.'" src="'.$this->c_imgnivel[$nivel].'" width="16px" height="16px" value="'.$this->v_imgnivel[$nivel].'"></SPAN></TD>';

		$imagenodo="";
		$clickimg="";
		$downimg="";
		$clickcontextualimg="";
		$styleimg="";
		
		$atributosHTML=$this->gXML->TomaAtributoEspecial("imagenodo",$imagenodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("clickimg",$clickimg,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("downimg",$downimg,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("clickcontextualimg",$clickcontextualimg,$atributosHTML);
		if ($clickimg!="" ) $clickimg=' onclick="'.$clickimg.'" ';
		if ($downimg!="" ) $downimg=' onmousedown="'.$downimg.'" ';
		if ($clickcontextualimg!="" ) $clickcontextualimg=' oncontextmenu=" '.$clickcontextualimg.'" ';

		if ($clickimg!="" || $downimg!="" || $clickcontextualimg!="" ) $styleimg=' style="cursor:hand"';

		$arbol.='<TD  '.$estilo.' width=16px><IMG '.$styleimg.' src="'.$imagenodo.'"'.$clickimg.$downimg.$clickcontextualimg.' width="16px" height="16px"></TD>';
		$arbol.='<TD  '.$estilo.' width="4px"></TD>';

		$clicksupnodo="";
		$clickcontextualnodo="";
		$classnodo="";
		$nodoid="";
		$nodovalue="";
		$mouseovernodo="";
		$infonodo="";

		$atributosHTML=$this->gXML->TomaAtributoEspecial("clickcontextualnodo",$clickcontextualnodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("clicksupnodo",$clicksupnodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("classnodo",$classnodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("nodoid",$nodoid,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("nodovalue",$nodovalue,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("colornodo",$colornodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("fondonodo",$fondonodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("mouseovernodo",$mouseovernodo,$atributosHTML);
		$atributosHTML=$this->gXML->TomaAtributoEspecial("infonodo",$infonodo,$atributosHTML);
		if ($clickcontextualnodo!="" ) $clickcontextualnodo=' oncontextmenu="'.$clickcontextualnodo.'" ';
		if ($mouseovernodo!="" ) $mouseovernodo=' onmouseover="'.$mouseovernodo.'" ';
		if ($nodovalue!="" ) $nodovalue=' value="'.$nodovalue.'" ';
		if (!$classnodo) $classnodo=$this->clasedefault;
		
		$arbol.='<TD  width="1024px"  '.$estilo.' class="'.$classnodo.'">';
		$arbol.='<A href="#nodo" class="'.$this->clasedefault. '" style="text-decoration: none"><SPAN id="'.$nodoid.'"  ';
		if($this->tipotabla<2){
			$arbol.=' onclick="clickLiteralNodo(this ,' ."'".$this->baseurlimg."'".');';
			$arbol.=" ".$clicksupnodo.'"'.$nodovalue.$mouseovernodo.$clickcontextualnodo;
		}
		$arbol.=' >'.$infonodo.'</SPAN></A></TD>';
		$arbol.='</TR>';
		$arbol.='</TABLE>';
		if ($swu)
			$this->c_imgnivel[$nivel]=$this->baseurlimg.'/nada.gif';
		else
			$this->c_imgnivel[$nivel]=$this->baseurlimg.'/nada_l.gif';
		return($arbol);
	}
} // Fin de la clase 