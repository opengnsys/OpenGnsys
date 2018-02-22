<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: HTMLCTEMULSELECT.php
// Descripción :
//		Crea la etiqueta html <SELECT> multiselección, de valores constantes
//	Parametros: 
//		- parametros: Una cadena con la forma valor=literal separada por un caracter INTRO
//		- nombreid: Nombre del desplegable (atributo HTML name)
//		- tbvalor: Array con los valores de las opciones que aparecerán seleccionadas
//		- clase: Clase que define su estilo
//		- eventochg: Nombre de la función que se ejecutará en respuesta al evento onchange( por defecto: ninguna)
//		- ancho: Anchura del desplegable
//		- alto: Altura del desplegable
// *************************************************************************************************************************************************
function HTMLCTEMULSELECT($parametros,$nombreid,$tbvalor,$clase,$eventochg,$ancho,$alto){
	if (!empty($eventochg)) $eventochg='onchange="'.$eventochg.'(this);"';
	if (empty($clase))	$clase='formulariodatos';
	$x=0;
	$opciones=split(chr(13),$parametros);
	$SelectHtml= '<SELECT '.$eventochg.' class="'.$clase.'" name="'.$nombreid.'" multiple size='.$alto.' style="WIDTH: '.$ancho.'">';
	for($i=0;$i<sizeof($opciones);$i++){
		$item=split("=",$opciones[$i]);
		$SelectHtml.= '<OPTION value="'.$item[0].'"';
		if (isset($tbvalor[$x])){
			if($tbvalor[$x]==$item[0]) {
				$SelectHtml.=" selected ";
				$x++;
			}
		}
		$SelectHtml.= '>'.$item[1].'</OPTION>';
	}
	return($SelectHtml);
}