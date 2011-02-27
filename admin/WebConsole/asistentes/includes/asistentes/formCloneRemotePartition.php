


<tr>
	<td class="op_basic">
	    Elige equipo MASTER:
		<select name="ipMaster" id="ipMaster" style="WIDTH:220" onChange="xajax_ListarParticionesXip(this.value);">
				<option value="">-- Elige equipo Master--</option>
				<?php echo ''.htmlOPTION_equipos($cmd,$ambito,$idambito).'';   ?>
		</select>		
		<br />		
		<!--DIV donde se mostrara la respuesta AJAX sobre las particiones clonables del equipo-->
		Elige particion del Master a enviar
		<div id="divListado"></div>
		<br />
		Elige herramienta de clonacion:
		<select name="tool" id="tool" style="WIDTH:220";">
			<option value="partclone"> partclone </option>
			<option value="partimage"> partimage </option>
		</select>
		<br />
		Elige compresor para la herramienta de clonacion:
		<select name="compresor" id="compresor" style="WIDTH:220";">
				<option value="lzop"> lzop </option>
				<option value="gzip"> gzip </option>
		</select>
	</td>
	
	<td class="op_net_1">
		<?php  echo ''. htmlForm_mcast($cmd,$ambito,$idambito).'';   ?>
	</td>
	
	<td class="op_tools">
	</td>
	
	<td class="op_target"></td>
	
	<td class="op_tools"></td>
</tr>
<tr>
	<td class="op_basic">		
		
	</td>
	<td class="op_net_1"></td>
	

	<td class="op_net_1">

	
	</td>
	<td class="op_target"></td>
	<td class="op_tools"></td>
</tr>
<tr>
	<td class="op_basic"></td>
	<td class="op_net_1"></td>
	<td class="op_net_1"></td>
	<td class="op_target"></td>
	<td class="op_tools"></td>
</tr>
<tr>
	<td class="op_basic"></td>
	<td class="op_net_1"></td>
	<td class="op_net_1"></td>
	<td class="op_target"></td>
	<td class="op_tools"></td>
</tr>





