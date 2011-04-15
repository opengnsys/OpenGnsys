

<TR>
<TD > Partici&oacute;n </TD>
<TD > Tipo </TD>
<TD > Tama&ntilde;o KB </TD>
</TR>

<TR>
<TD > <input type="checkbox" name="check1" value="check1" onclick="if (this.form.check1.checked) { this.form.part1.disabled=false; this.form.size1.disabled=false; if (this.form.part1.options[this.form.part1.selectedIndex].value == 'CUSTOM') { this.form.part1custom.disabled=false } if (this.form.size1.options[this.form.size1.selectedIndex].value == 'CUSTOM') { this.form.size1custom.disabled=false } } else { this.form.part1.disabled=true; this.form.size1.disabled=true; this.form.part1custom.disabled=true; this.form.size1custom.disabled=true }" /> <br> Partici&oacute;n 1 </TD>
<TD>
<select name="part1" id="part1" style="WIDTH:220" disabled="true" onclick="if (this.form.part1.options[this.form.part1.selectedIndex].value == 'CUSTOM') { this.form.part1custom.disabled=false } else { this.form.part1custom.disabled=true }" >
	<?php echo ''. htmlForm_typepart($cmd,1)  .''; ?> 
		<option value="CUSTOM"> Personalizar </option>
</select>
<br>
<select name="part1custom" id="part1custom" style="WIDTH:220" disabled="true" >
		<?php echo ''. htmlForm_typepartnotcacheEngine10()  .''; ?>
</select></TD>

<TD><select name="size1" id="size1" style="WIDTH:220" disabled="true" onclick="if (this.form.size1.options[this.form.size1.selectedIndex].value == 'CUSTOM') { this.form.size1custom.disabled=false } else { this.form.size1custom.disabled=true }" >
		<?php echo ''. htmlForm_sizepart($cmd,1)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>
</select>
<br >
<INPUT type="text" style="width:100" name="size1custom" value="0" disabled="true"></TD>
</TR>

<TR>
<TD > <input type="checkbox" name="check2" value="check2" onclick="if (this.form.check2.checked) { this.form.part2.disabled=false; this.form.size2.disabled=false; if (this.form.part2.options[this.form.part2.selectedIndex].value == 'CUSTOM') { this.form.part2custom.disabled=false } if (this.form.size2.options[this.form.size2.selectedIndex].value == 'CUSTOM') { this.form.size2custom.disabled=false } } else { this.form.part2.disabled=true; this.form.size2.disabled=true; this.form.part2custom.disabled=true; this.form.size2custom.disabled=true }" /> <br> Partici&oacute;n 2 </TD>
<TD><select name="part2" id="part2" style="WIDTH:220" disabled="true" onclick="if (this.form.part2.options[this.form.part2.selectedIndex].value == 'CUSTOM') { this.form.part2custom.disabled=false } else { this.form.part2custom.disabled=true }" >
		<?php echo ''. htmlForm_typepart($cmd,2)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>
</select>
<br>
<select name="part2custom" id="part2custom" style="WIDTH:220" disabled="true" >
		<?php echo ''. htmlForm_typepartnotcacheEngine10()  .''; ?>
</select></TD>

<TD><select name="size2" id="size2" style="WIDTH:220" disabled="true" onclick="if (this.form.size2.options[this.form.size2.selectedIndex].value == 'CUSTOM') { this.form.size2custom.disabled=false } else { this.form.size2custom.disabled=true }" >
		<?php echo ''. htmlForm_sizepart($cmd,2)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>
</select>
<br >
<INPUT type="text" style="width:100" name="size2custom" value="0" disabled="true"></TD>
</TR>

<TR>
<TD > <input type="checkbox" name="check3" value="check3" onclick="if (this.form.check3.checked) { this.form.part3.disabled=false; this.form.size3.disabled=false; if (this.form.part3.options[this.form.part3.selectedIndex].value == 'CUSTOM') { this.form.part3custom.disabled=false } if (this.form.size3.options[this.form.size3.selectedIndex].value == 'CUSTOM') { this.form.size3custom.disabled=false } } else { this.form.part3.disabled=true; this.form.size3.disabled=true; this.form.part3custom.disabled=true; this.form.size3custom.disabled=true }" /> <br> Partici&oacute;n 3 </TD>
<TD><select name="part3" id="part3" style="WIDTH:220" disabled="true" onclick="if (this.form.part3.options[this.form.part3.selectedIndex].value == 'CUSTOM') { this.form.part3custom.disabled=false } else { this.form.part3custom.disabled=true }" >
		<?php echo ''. htmlForm_typepart($cmd,3)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>
</select>
<br>
<select name="part3custom" id="part3custom" style="WIDTH:220" disabled="true" >
		<?php echo ''. htmlForm_typepartnotcacheEngine10()  .''; ?>
</select></TD>

<TD><select name="size3" id="size3" style="WIDTH:220" disabled="true" onclick="if (this.form.size3.options[this.form.size3.selectedIndex].value == 'CUSTOM') { this.form.size3custom.disabled=false } else { this.form.size3custom.disabled=true }" >
		<?php echo ''. htmlForm_sizepart($cmd,3)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>
</select>
<br >
<INPUT type="text" style="width:100" name="size3custom" value="0" disabled="true"></TD>
</TR>

<TR>
<TD > <input type="checkbox" name="check4" value="check4" onclick="if (this.form.check4.checked) { this.form.part4.disabled=false; this.form.size4.disabled=false; if (this.form.size4.options[this.form.size4.selectedIndex].value == 'CUSTOM') { this.form.size4custom.disabled=false } } else { this.form.part4.disabled=true; this.form.size4.disabled=true; this.form.size4.options[0].selected=true; }" /> <br> Partici&oacute;n 4 </TD>
<TD> <INPUT type="label" readonly size="8" name="part4" disabled="true" value="CACHE"></TD>
<TD><select name="size4" id="size4" style="WIDTH:220" disabled="true" onclick="if (this.form.size4.options[this.form.size4.selectedIndex].value == 'CUSTOM') { this.form.size4custom.disabled=false } else { this.form.size4custom.disabled=true }" > 
		<option value="0"> Sin modificar tama&ntilde;o </option>
		<?php echo ''. htmlForm_sizepart($cmd,4)  .''; ?>
		<option value="CUSTOM"> Personalizar </option>		
</select>
<br >
<INPUT type="text" style="width:100" name="size4custom" value="0" disabled="true"></TD>
</TR>
