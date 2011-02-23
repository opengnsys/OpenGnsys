

<TR>
<TD >  Particion </TD>
<TD > Tipo </TD>
<TD > Tamaño </TD>
</TR>

<TR>
<TD > 1 </TD>
<TD>	<select name="part1" id="part1" style="WIDTH:220" >
				<option value="">-- particon--</option>
				<?php echo ''. htmlOPTION_typepartnotcache($cmd)  .''; ?>
</select></TD>
<TD ><INPUT type="text" style="width:100" name="size1" value="0"></TD>
</TR>

<TR>
<TD > 2 </TD>
<TD>	<select name="part2" id="part2" style="WIDTH:220" >
				<option value="">-- particon--</option>
				<?php echo ''. htmlOPTION_typepartnotcache($cmd)  .''; ?>
</select></TD>
<TD ><INPUT type="text" style="width:100" name="size" value="0"></TD>
</TR>

<TR>
<TD > 3 </TD>
<TD>	<select name="part3" id="part3" style="WIDTH:220" >
				<option value="">-- particon--</option>
				<?php echo ''. htmlOPTION_typepartnotcache($cmd)  .''; ?>
</select></TD>
<TD ><INPUT type="text" style="width:100" name="size3" value="0"></TD>
<TR>

<TR>
<TD > 4 </TD>
<TD> <INPUT type="label" readonly size="8" name="part4" value="CACHE"></TD>
<TD ><INPUT type="text" style="width:100" name="size4" value="0"></TD>
</TR>