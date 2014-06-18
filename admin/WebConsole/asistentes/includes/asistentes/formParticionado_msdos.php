
<tr>
<td>
<table id="primarias">
<caption><?php echo $TbMsg[36]?></caption>
<tr>
<td><?php echo $TbMsg[20]?></td>
<td><?php echo $TbMsg[24]?></td>
<td><?php echo $TbMsg[22]?></td>
</tr>

<?php
for ($p=1; $p<4; $p++) {
    echo '
<tr>
<td>
<input type="checkbox" name="check'.$p.'" value="check'.$p.'" onclick="clickPartitionCheckbox(this.form, '.$p.');" /> '.$TbMsg[20].' '.$p.'</td>
<td>
<select name="part'.$p.'" id="part'.$p.'" style="width:220" disabled="true" onclick="
	//if (this.form.part'.$p.'.options[this.form.part'.$p.'.selectedIndex].value == \'CUSTOM\') {
	if (this.options[this.selectedIndex].value == \'CUSTOM\') {
		this.form.part'.$p.'custom.disabled=false;
	} else {
		this.form.part'.$p.'custom.disabled=true;
	}
" onchange="checkExtendedPartition(form);">'
	.htmlForm_typepart($cmd,$p).'
	<option value="CUSTOM"> '.$TbMsg[39].' </option>
</select>
<br>
<select name="part'.$p.'custom" id="part'.$p.'custom" style="width:220" disabled="true" onchange="checkExtendedPartition(form);">'. htmlForm_typepartnotcacheEngine10($p) .'</select>
</td>
<td>
<select name="size'.$p.'" id="size'.$p.'" style="width:220" disabled="true" onclick="
	if (this.form.size'.$p.'.options[this.form.size'.$p.'.selectedIndex].value == \'CUSTOM\') {
		this.form.size'.$p.'custom.disabled=false;
	} else {
		this.form.size'.$p.'custom.disabled=true;
	}
" onchange="calculateFreeDisk(this.form);">'
	.htmlForm_sizepart($cmd,$p).'
	<option value="CUSTOM"> '.$TbMsg[39].'</option>
</select>
<br />
<input type="text" style="width:100" name="size'.$p.'custom" value="0" disabled="true" onchange="calculateFreeDisk(this.form);" />
</td>
</tr>
    ';
}
?>

<tr>
<td><input type="checkbox" name="check4" value="check4" onclick="clickPartitionCheckbox(this.form, 4);" /> <?php echo $TbMsg[20].' '.$p;?> </td>
<td>
<!--
<select disabled="true" name="part4" id="part4" size="1"  onclick="if (this.form.part4.options[this.form.part4.selectedIndex].value == 'PART') { this.form.part4custom.disabled=false } else { this.form.part4custom.disabled=true }"  onchange="checkExtendedPartition(form);" />
	<option value="CACHE" selected="selected">CACHE</option>
	<option value="PART">Particion</option>
</select>
<br />
-->
<select name="part4" id="part4" style="width:220" disabled="true" onchange="checkExtendedPartition(form);"><? echo htmlForm_typepartnotcacheEngine10(4) ?></select>
</td>
<td><select name="size4" id="size4" style="width:220" disabled="true" onclick="if (this.form.size4.options[this.form.size4.selectedIndex].value == 'CUSTOM') { this.form.size4custom.disabled=false } else { this.form.size4custom.disabled=true }" onchange="calculateFreeDisk(this.form);" />
	<option value="0"> <?php echo $TbMsg[40];?> </option>
	<?php echo ''. htmlForm_sizepart($cmd,4) .''; ?>
	<option value="CUSTOM"> <?php echo $TbMsg[39];?> </option>		
</select>
<br />
<input type="text" style="width:100" name="size4custom" value="0" disabled="true" onchange="calculateFreeDisk(this.form);" /></td>
</tr>

</table>
</td>

<td>
<table id="logicas" style="visibility:hidden">
<caption><?php echo $TbMsg[37]?></caption>
<tr>
<td><?php echo $TbMsg[20]?></td>
<td><?php echo $TbMsg[24]?></td>
<td><?php echo $TbMsg[22]?></td>
</tr>

<?php
for ($p=5; $p<=9; $p++) {
    echo '
<tr>
<td>
<input type="checkbox" name="check'.$p.'" value="check'.$p.'" onclick="clickPartitionCheckbox(this.form, '.$p.');" /> '.$TbMsg[20].' '.$p.'</td>
<td>
<select name="part'.$p.'" id="part'.$p.'" style="width:220" disabled="true" onclick="
	if (this.form.part'.$p.'.options[this.form.part'.$p.'.selectedIndex].value == \'CUSTOM\') {
		this.form.part'.$p.'custom.disabled=false;
	} else {
		this.form.part'.$p.'custom.disabled=true;
	}
">'. htmlForm_typepart($cmd,$p). '
	<option value="CUSTOM"> '.$TbMsg[39].' </option>
</select>
<br>
<select name="part'.$p.'custom" id="part'.$p.'custom" style="width:220" disabled="true" >'. htmlForm_typepartnotcacheEngine10($p) .'</select>
</td>
<td>
<select name="size'.$p.'" id="size'.$p.'" style="width:220" disabled="true" onclick="
	if (this.form.size'.$p.'.options[this.form.size'.$p.'.selectedIndex].value == \'CUSTOM\') {
		this.form.size'.$p.'custom.disabled=false;
	} else {
		this.form.size'.$p.'custom.disabled=true;
	}
" onchange="calculateFreeDisk(this.form);"
">'.htmlForm_sizepart($cmd,$p).'
	<option value="CUSTOM"> '.$TbMsg[39].'</option>
</select>
<br />
<input type="text" style="width:100" name="size'.$p.'custom" value="0" disabled="true" />
</td>
</tr>
    ';
}
?>

</table>
</td>

</tr>

<tr>
<th>
<input type="hidden" id="minsize" />
<?php echo $TbMsg[38];?>: <input type="text" id="freedisk" width="15" disabled="true" />
</th>
</tr>


