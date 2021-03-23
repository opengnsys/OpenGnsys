<?php
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
<FORM  align=center name="fdatosejecucion" id="fdatosejecucion" action="<?php echo $gestor?>" method="post"> 
	<INPUT type="hidden" name="idcomando" value="<?php echo $idcomando?>">
	<INPUT type="hidden" name="descricomando" value="<?php echo $descricomando?>">
	<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">
	<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
	<INPUT type="hidden" name="funcion" value="<?php echo $funcion?>">
	<INPUT type="hidden" name="atributos" value="<?php echo $atributos?>">
	<INPUT type="hidden" name="sw_ejsis" value="<?php echo $sw_ejsis?>">
	<INPUT type="hidden" name="cadenaip" value="<?php echo $cadenaip?>">
	<INPUT type="hidden" name="gestor" value="<?php echo $gestor?>">
	<INPUT type="hidden" name="filtro" value="">

