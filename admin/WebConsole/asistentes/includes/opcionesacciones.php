<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Abril-2010
// Nombre del fichero: opcionesacciones.php
// Descripción : 
//		Opciones comunes para la ejecución de comandos
// *************************************************************************************************************************************************
?>
<P align=center><span align=center class=subcabeceras><? echo "Opciones de Ejecución"?></span></P>

<?if($idcomando!=10){?>
	<TABLE align=center>
		<TR>
			<TD><IMG border=0 style="cursor:pointer" src="../images/boton_aceptar.gif" onclick="confirmar()" ></TD>
		</TR>
	</TABLE>
	<BR>
<?}?>	
<TABLE align=center  class=opciones_ejecucion BORDER=0>
	<TR>
		<TD><INPUT name=sw_ejya type=checkbox checked></TD>
		<TD colspan=3>Ejecutar inmediatamente</TD>
	</TR>
	<TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT name=sw_seguimiento type=radio value=1></TD><TD>Ejecutar con seguimiento&nbsp;</TD>
	</TR>
	<TR>
		<TD>&nbsp; </TD>
		<TD><INPUT  checked  name=sw_seguimiento type=radio value=0></TD><TD>Sin seguimiento&nbsp;</TD>
	</TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
		 <TR HEIGHT=5><TD colspan=4><HR></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	<TR>
		<TD><INPUT name=sw_ejprg type=checkbox></TD>
		<TD colspan=3>Programar comando</TD>
	</TR>	
	<!-------------------------------------------------------------------------------------------------------------------------------->
		 <TR HEIGHT=5><TD colspan=4><HR></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	 <TR>
	  <TD><INPUT  onclick="clic_mkprocedimiento(this)"  name=sw_mkprocedimiento type=checkbox></TD>
	  <TD colspan=3>Guardar como procedimiento</TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_nwprocedimiento(this)"  name=sw_procedimiento type=radio value=0></TD>
	  <TD>Guardar como nuevo procedimiento&nbsp;</TD>
	 <TD><INPUT onclick="clic_nomprocedimiento(this)" style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px" name=nombreprocedimiento style="HEIGHT: 22px; WIDTH: 275px"></TD></TR>
	 
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_exprocedimiento(this)" name=sw_procedimiento type=radio value=1></TD>
	  <TD>Incluir en un procedimiento existente&nbsp;</TD>
	  <TD><?echo HTMLSELECT($cmd,$idcentro,'procedimientos',0,'idprocedimiento','descripcion',275,"procedimientoexistente");?></TD></TR>
	  
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD>&nbsp; </TD>
	  <TD>Orden de ejecución&nbsp;</TD>
	  <TD><INPUT maxlength=3  style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px;WIDTH:30" name=ordprocedimiento type=text value=""></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
		 <TR HEIGHT=10><TD colspan=4>&nbsp; <HR></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	 <TR>
	  <TD><INPUT  onclick="clic_mktarea(this)"  name=sw_mktarea type=checkbox></TD>
	  <TD colspan=3>Guardar como tarea</TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_nwtarea(this)"  name=sw_tarea type=radio value=0></TD>
	  <TD>Guardar como una tarea nueva&nbsp;</TD>
	 <TD><INPUT onclick="clic_nomtarea(this)" style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px" name=nombretarea style="HEIGHT: 22px; WIDTH: 275px"></TD></TR>
	 
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_extarea(this)" name=sw_tarea type=radio value=1></TD>
	  <TD>Incluir en una tarea existente&nbsp;</TD>
	  <TD><?echo HTMLSELECT($cmd,$idcentro,'tareas',0,'idtarea','descripcion',275,"tareaexistente");?></TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD>&nbsp; </TD>
	  <TD>Orden de ejecución&nbsp;</TD>
	  <TD><INPUT maxlength=3  style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px;WIDTH:30" name=ordtarea type=text value=""></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>

