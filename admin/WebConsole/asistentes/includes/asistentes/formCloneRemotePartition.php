


<tr>
	<td class="op_basic">
	    Elige equipo MASTER:
		<select name="ipMaster" id="ipMaster" style="WIDTH:220" onChange="xajax_ListarOrigenMaster(this.value);">
				<option value="">-- Elige equipo Master--</option>
				<?php echo ''.htmlOPTION_equipos($cmd,$ambito,$idambito).'';   ?>
		</select>		
		<br />		
		<!--DIV donde se mostrara la respuesta AJAX sobre las particiones clonables del equipo-->
		Elige desde el Master la imagen o particion a enviar
		<div id="divListado"></div>
		<br />
		Elige la identificacion de la partición destino de los clientes:
		<select name="targetpart" id="targetpart" style="WIDTH:220";">
			<option value="1 1"> 1er disco - 1ª particion </option>
			<option value="1 2"> 1er disco - 2ª particion </option>
			<option value="1 3"> 1er disco - 3ª particion </option>
			<option value="1 4"> 1er disco - 4ª particion </option>
		</select>
		<br />
		Elige el metodo de transferencia
		<select name="idmetodo" id="idmetodo" style="WIDTH:220";">
			<option value="MULTICAST"> MULTICAST </option>
			<option value="UNICAST"> UNICAST </option> 			
		</select>
		<br />
		Elige herramienta de clonacion:
		<select name="tool" id="tool" style="WIDTH:220";">
			<option value="partclone"> partclone </option>
		<!--	<option value="partimage"> partimage </option> -->
		</select>
		<br />
		Elige compresor para la herramienta de clonacion:
		<select name="compresor" id="compresor" style="WIDTH:220";">
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





