<!---------------------------------------------------------------------------------------->
<p align=center>
<span align=center class=subcabeceras><? echo "Performance options"?></span>
<FORM  name="fdatosejecucion"> 
	<TABLE class=opciones_ejecucion BORDER=0>
	 <TR>
	  <TD><INPUT  name=sw_ejya type=checkbox checked></TD>
	  <TD colspan=3>Perform immediately </TD></TR>
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT   name=sw_seguimiento type=radio value=1></TD><TD>Follow-up action&nbsp;</TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT  checked  name=sw_seguimiento type=radio value=0></TD><TD>Don't  follow-up&nbsp;</TD></TR>

	<!-------------------------------------------------------------------------------------------------------------------------------->
		 <TR HEIGHT=5><TD colspan=4><HR></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	 <TR>
	  <TD><INPUT  onclick="clic_mkprocedimiento(this)"  name=sw_mkprocedimiento type=checkbox></TD>
	  <TD colspan=3>Save as a procedure</TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_nwprocedimiento(this)"  name=sw_procedimiento type=radio value=0></TD>
	  <TD>Save as a new procedure&nbsp;</TD>
	 <TD><INPUT onclick="clic_nomprocedimiento(this)" style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px" name=nombreprocedimiento style="HEIGHT: 22px; WIDTH: 275px"></TD></TR>
	 
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_exprocedimiento(this)" name=sw_procedimiento type=radio value=1></TD>
	  <TD>To include in an existing procedure&nbsp;</TD>
	  <TD><?echo HTMLSELECT($cmd,$idcentro,'procedimientos',0,'idprocedimiento','descripcion',275,"procedimientoexistente");?></TD></TR>
	  
	<!-------------------------------------------------------------------------------------------------------------------------------->
		 <TR HEIGHT=10><TD colspan=4>&nbsp; <HR></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->
	 <TR>
	  <TD><INPUT  onclick="clic_mktarea(this)"  name=sw_mktarea type=checkbox></TD>
	  <TD colspan=3>Save as a task</TD></TR>

	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_nwtarea(this)"  name=sw_tarea type=radio value=0></TD>
	  <TD>Save as a new task&nbsp;</TD>
	 <TD><INPUT onclick="clic_nomtarea(this)" style="FONT-FAMILY:Arial, Helvetica, sans-serif;FONT-SIZE: 11px" name=nombretarea style="HEIGHT: 22px; WIDTH: 275px"></TD></TR>
	 
	 <TR>
	  <TD>&nbsp; </TD>
	  <TD><INPUT onclick="clic_extarea(this)" name=sw_tarea type=radio value=1></TD>
	  <TD>To include in an existing task&nbsp;</TD>
	  <TD><?echo HTMLSELECT($cmd,$idcentro,'tareas',0,'idtarea','descripcion',275,"tareaexistente");?></TD></TR>
	<!-------------------------------------------------------------------------------------------------------------------------------->

	</TABLE>
</FORM>