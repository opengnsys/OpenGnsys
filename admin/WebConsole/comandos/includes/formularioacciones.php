<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Abril-2010
// Nombre del fichero: opcionesacciones.php
// Descripción : 
//		Formulario para paso de parametros comunes para la ejecución de comandos
// *************************************************************************************************************************************************
?>
<FORM  align=center name="fdatosejecucion" action="<? echo $gestor?>" method="post"> 
	<INPUT type="hidden" name="idcomando" value="<? echo $idcomando?>">
	<INPUT type="hidden" name="descricomando" value="<? echo $descricomando?>">
	<INPUT type="hidden" name="ambito" value="<? echo $ambito?>">
	<INPUT type="hidden" name="idambito" value="<? echo $idambito?>">
	<INPUT type="hidden" name="funcion" value="<? echo $funcion?>">
	<INPUT type="hidden" name="atributos" value="<? echo $atributos?>">
	<INPUT type="hidden" name="gestor" value="<? echo $gestor?>">
	<INPUT type="hidden" name="filtro" value="">

