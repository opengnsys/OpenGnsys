

<tr> <td> opciones basicas </td> <td> opciones multicast </td> <td> opciones torrent </td> <td> opciones unicast </td></tr>

<tr>  
	<td class="op_basic">
	    Elige la particion del disco donde se restaurar la imagen:
		<select name="idparticion" id="idparticion" style="WIDTH:220">
				<option value="1"> 1 </option>
				<option value="2"> 2 </option>
				<option value="3"> 3 </option>
		</select>
	
	<!--DIV donde se mostrara la respuesta AJAX sobre las particiones clonables del equipo-->
		<div id="divListado"></div>
	    Elige la imagen a distribuir:	
		<select name="idimagen" id="idimagen" style="WIDTH:220">
				<option value="">--Selecciona imagen --</option>
				<?php echo ''. htmlOPTION_images($cmd) .'';   ?>
		</select>		
		<br />		
	
		<br />
		Elige el metodo de transferencia
		<select name="idmetodo" id="idmetodo" style="WIDTH:220";">
		<!--	<option value="UNICAST"> UNICAST </option> -->
			<option value="TORRENT"> TORRENT </option>
			<option value="MULTICAST"> MULTICAST </option>
		</select>
		<br />
	</td>
	
	<td class="op_mcast">
		<?php  echo ''. htmlForm_mcast($cmd,$ambito,$idambito).'';   ?>
	</td>
	   
	<td class="op_torrent">
		 <?php  echo ''. htmlForm_p2p($cmd,$ambito,$idambito).'';   ?>
	</td>
	
	<td class="op_unicast">
	
	</td>
</tr>




