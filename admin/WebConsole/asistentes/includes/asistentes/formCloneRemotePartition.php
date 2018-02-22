


<tr>
	<td class="op_basic">
	    <?php echo $TbMsg["WCRP32"] ?> 
		<select name="ipMaster" id="ipMaster" style="width:220" onChange="xajax_ListarOrigenMaster(this.value);">
				<option value=""> -- <?php echo $TbMsg["WCRP32"] ?> -- </option>
				<?php echo ''.htmlOPTION_equipos($cmd,$ambito,$idambito).'';   ?>
		</select>		
		<br />		
		<!--DIV donde se mostrara la respuesta AJAX sobre las particiones clonables del equipo-->
		<?php echo $TbMsg["WCRP33"] ?>
		<div id="divListado"></div>
		<br />
		<?php echo $TbMsg["WCRP34"] ?> 
		<select name="targetpart" id="targetpart" style="width:220;">
			<option value="1 1"> 1er disco - 1ª particion </option>
			<option value="1 2"> 1er disco - 2ª particion </option>
			<option value="1 3"> 1er disco - 3ª particion </option>
			<option value="1 4"> 1er disco - 4ª particion </option>
		</select>
		<br />
		<?php echo $TbMsg["WCRP35"] ?>
		<select name="idmetodo" id="idmetodo" style="width:220;">
			<option value="MULTICAST"> MULTICAST </option>
			<option value="UNICAST"> UNICAST </option> 			
		</select>
		<br />
		<?php echo $TbMsg["WCRP36"] ?>
		<select name="tool" id="tool" style="width:220;">
			<option value="partclone"> partclone </option>
		<!--	<option value="partimage"> partimage </option> -->
		</select>
		<br />
		<?php echo $TbMsg["WCRP37"] ?>
		<select name="compresor" id="compresor" style="width:220;">
				<option value="lzop"> lzop </option>
				<option value="gzip"> gzip </option>
		</select>
		<br />
		<br />

	</td>
	
	<td class="op_mcast">
		<?php  echo ''. htmlForm_mcast($cmd,$ambito,$idambito).'';   ?>
	</td>
	
	<td class="op_unicast">
		<?php  echo ''. htmlForm_unicast($cmd,$ambito,$idambito).'';   ?>
	</td>
</tr>





