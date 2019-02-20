<script>
function accion (opcion) {
	document.forms[0].opcioncrear.value = opcion.name;
	document.forms[0].action = "./boot_grub4dos_crear.php";
}

</script>

<td valign="top">
<TABLE width="150" align=left border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR>
	<TD width="150" height="45" valign="middle">
		<input type="radio" name="boottype" value="bios" checked>bios
		<input type="radio" name="boottype" value="uefi">uefi

	</TD>
</TR>
<TR>
	<TD id="crear" width="150" height="100" valign="middle"> <?php echo $TbMsg[3]?><br />
		<input type="submit" value=<?php echo $TbMsg[0]?> name="crear" onclick="accion(this)">
	</TD>
</TR>
<TR>
	<TD id="modificar" width="150" height="100" valign="middle"> <?php echo $TbMsg[4]?><br />
		<input type="submit" value=<?php echo $TbMsg[1]?> name="modificar" onclick="accion(this)">
	</TD>
</TR>
<TR>  
	<TD id="eliminar" width="150" height="100" valign="middle"> <?php echo $TbMsg[5]?><br />
		<input type="submit" value=<?php echo $TbMsg[2]?> name="eliminar" onclick="accion(this)">
	</TD>
</TR>
<TR>
	<TD width="150" height="150" valign="middle">&nbsp; </TD>
</TR>
</TABLE>
</td>
