<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: HTMLCTESELECT.php
// Descripción :
//		Crea la etiqueta html <SELECT> de valores constantes
//	Parametros: 
//		- parametros:Una cadena con la forma valor=literal separada por un caracter INTRO
//		- nombreid: Nombre del desplegable (atributo HTML name)
//		- clase: Clase que define su estilo
//		- defaultlit: Literal de la primera opción cuyo valor es siempre 0
//		- valorselec: Valor del item que saldrá seleccionado por defecto
//		- ancho: Anchura del desplegable
//		- eventochg: Nombre de la función que se ejecutará en respuesta al evento onchange
// *************************************************************************************************************************************************
function HTMLCTESELECT($parametros,$nombreid,$clase,$defaultlit,$valorselec,$ancho,$eventochg=""){
	if (!empty($eventochg))	$eventochg='onchange="'.$eventochg.'(this);"';
	$opciones=split(chr(13),$parametros);
	$SelectHtml= '<select '.$eventochg.' class="'.$clase.'" id='.$nombreid.' name="'.$nombreid.'" style="width: '.$ancho.'">';
	if (!empty($defaultlit)) $SelectHtml.= '<option value="0">'.$defaultlit.'</option>';
	for($i=0;$i<sizeof($opciones);$i++){
		$item=split("=",$opciones[$i]);
		// Comprobar formato de línea: "nombre=valor".
		if (! empty ($item[1])) {
			$SelectHtml.= '<option value="'.$item[0].'"';
			if($valorselec==$item[0])
				$SelectHtml.=" selected ";
			$SelectHtml.= '>'.$item[1].'</option>';
		}
	}
	$SelectHtml.= '</select>';
	return($SelectHtml);
}
