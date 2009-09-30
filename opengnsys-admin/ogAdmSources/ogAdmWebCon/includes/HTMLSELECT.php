<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon.
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación:2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: HTMLSELECT.php
// Descripción :
//		Crea la etiqueta html <SELECT> con valores procedentes de una tabla
//	Parametros: 
//		- cmd:Un comando ya operativo (con conexión abierta)  
//		- idcentro:Centro al que pertene el registro donde se encuentra el dato a recuperar, será  0 para no contemplar este dato
//		- nombretabla: Nombre de la tabla origen de los datos
//		- identificador: Valor del campo identificador del registro
//		- nombreid: Nombre del campo identificador del registro 
//		- nombreliteral: Nombre del campo de la tabla que mostrará el desplegable
//		- ancho: Anchura del desplegable
//		- eventochg: Nombre de la función que se ejecutará en respuesta al evento onchange( por defecto: ninguna)
//		- clase: Clase que define su estilo (por defecto: formulariodatos)
//		- clausulawhere: Clausula Where adicional
// *************************************************************************************************************************************************
function HTMLSELECT($cmd,$idcentro,$nombretabla,$identificador,$nombreid,$nombreliteral,$ancho,$eventochg = "",$clase="",$clausulawhere=""){
	if (!empty($eventochg))	$eventochg='onchange="'.$eventochg.'(this);"';
	if (empty($clase))	$clase='formulariodatos';
	$SelectHtml="";
	$rs=new Recordset; 
	if ($idcentro>0){
			$cmd->texto='SELECT * FROM '.$nombretabla.' WHERE idcentro='.$idcentro;
			if(!empty($clausulawhere))
				$cmd->texto.=" AND (".$clausulawhere.")";
	}
	else{
			$cmd->texto='SELECT * FROM '.$nombretabla;
			if(!empty($clausulawhere))
				$cmd->texto.=" WHERE (".$clausulawhere.")";
	}
	$cmd->texto.=' ORDER BY '.$nombreliteral;

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir())	return(""); // Error al abrir recordset
	$SelectHtml.= '<SELECT  '.$eventochg.' class="'.$clase.'" name="'.$nombreid.'" style="WIDTH: '.$ancho.'">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos[$nombreid].'"';
		If ($rs->campos[$nombreid]==$identificador)  $SelectHtml.= ' selected ' ;
		$SelectHtml.= '>'.$rs->campos[$nombreliteral].'</OPTION>';
		$rs->Siguiente();
	}$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}