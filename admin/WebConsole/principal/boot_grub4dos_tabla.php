<td valign="top">
<TABLE width="150" align=left border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR>
	<TD width="150" height="45" valign="middle">
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="opcioncrear" value="">
		</form>
	</TD>
</TR>
<TR>
	<TD width="150" height="100" valign="middle"> <?php echo $TbMsg[3]?><br />
		<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="opcioncrear" value="1">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="submit" value=<?php echo $TbMsg[0]?> name="nuevoarran">
		</form>
	</TD>
</TR>
<TR>
	<TD width="150" height="100" valign="middle"> <?php echo $TbMsg[4]?><br />
		<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="submit" value=<?php echo $TbMsg[1]?> name="nuevoarran">
		</form>
	</TD>
</TR>
<TR>  
	<TD width="150" height="100" valign="middle"> <?php echo $TbMsg[5]?><br />
		<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="opcioncrear" value="3">
		<input type="submit" value=<?php echo $TbMsg[2]?> name="nuevoarran">
		</form>
	</TD>
</TR>
<TR>
	<TD width="150" height="150" valign="middle">
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="opcion"crear value="">
		</form>
	</TD>
</TR>
</TABLE>
</td>
