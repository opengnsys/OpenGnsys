<tr>
<td>
<table id="particionesGPT">
<caption><?php echo $TbMsg[36]?></caption>
<tr>
<td><?php echo $TbMsg[20]?></td>
<td><?php echo $TbMsg[24]?></td>
<td><?php echo $TbMsg[22]?></td>
</tr>

<?php
for ($p=1; $p<4; $p++) {
    echo '
<tr id="trPartition'.$p.'">
<td>
<input type="checkbox" name="checkGPT'.$p.'" value="checkGPT'.$p.'" onclick="clickPartitionCheckbox(this.form, '.$p.',true);" /> '.$TbMsg[20].' '.$p.'</td>
<td>
<select name="partGPT'.$p.'" id="partGPT'.$p.'" style="width:220" disabled="true" onclick="
	//if (this.form.part'.$p.'.options[this.form.part'.$p.'.selectedIndex].value == \'CUSTOM\') {
	if (this.options[this.selectedIndex].value == \'CUSTOM\') {
		this.form.partGPT'.$p.'custom.disabled=false;
	} else {
		this.form.partGPT'.$p.'custom.disabled=true;
	}">'.htmlForm_typepart($cmd,$p).'<option value="CUSTOM"> '.$TbMsg[39].' </option>
</select>
<br>
<select name="partGPT'.$p.'custom" id="partGPT'.$p.'custom" style="width:220" disabled="true" >'. htmlForm_typepartnotcacheGPT($p) .'</select>
</td>
<td>
<select name="sizeGPT'.$p.'" id="sizeGPT'.$p.'" style="width:220" disabled="true" onclick="
	if (this.form.sizeGPT'.$p.'.options[this.form.sizeGPT'.$p.'.selectedIndex].value == \'CUSTOM\') {
		this.form.sizeGPT'.$p.'custom.disabled=false;
	} else {
		this.form.sizeGPT'.$p.'custom.disabled=true;
	}
" onchange="calculateFreeGPTDisk(this.form);">'.htmlForm_sizepart($cmd,$p).'
<option value="CUSTOM"> '.$TbMsg[39].'</option>
</select>
<br />
<input type="text" style="width:100" name="sizeGPT'.$p.'custom" value="0" disabled="true" onchange="calculateFreeDisk(this.form);" />
</td>
</tr>
    ';
}
?>

<tr id="trPartition4">
<td><input type="checkbox" name="checkGPT4" value="checkGPT4" onclick="clickPartitionCheckbox(this.form, 4,true);" /> <?php echo $TbMsg[20].' '.$p;?> </td>
<td>
<select name="partGPT4" id="partGPT4" style="width:220" disabled="true" onchange="checkExtendedPartition(form);"><? echo htmlForm_typepartnotcacheGPT(4) ?></select>
</td>
<td><select name="sizeGPT4" id="sizeGPT4" style="width:220" disabled="true" onclick="if (this.form.sizeGPT4.options[this.form.sizeGPT4.selectedIndex].value == 'CUSTOM') { this.form.sizeGPT4custom.disabled=false } else { this.form.sizeGPT4custom.disabled=true }" onchange="calculateFreeGPTDisk(this.form);" />
	<option value="0"> <?php echo $TbMsg[40];?> </option>
	<?php echo ''. htmlForm_sizepart($cmd,4) .''; ?>
	<option value="CUSTOM"> <?php echo $TbMsg[39];?> </option>		
</select>
<br />
<input type="text" style="width:100" name="sizeGPT4custom" value="0" disabled="true" onchange="calculateFreeGPTDisk(this.form);" /></td>
</tr>
</table>
</td>
</tr>
<tr>
	<td>
		<input type="button" value="A&ntilde;adir particion" onclick="addGPTPartition()"/>
                <input type="button" value="Eliminar particion" onclick="deleteGPTPartition()"/>
        </td>

</tr>
<tr>
<th>
<input type="hidden" id="numGPTpartitions" value="4"/>
<input type="hidden" id="minsizeGPT" />
<?php echo $TbMsg[38];?>: <input type="text" id="freediskGPT" width="15" disabled="true" />
</th>
</tr>

</td>

