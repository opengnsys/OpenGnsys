<?php
// version 1.1: se incluye el atributo atrib_restore (ticket #757)
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-10-27
$disksPartitions = array();
$diskPartIndex = 0;
// Recorremos todas las configuraciones y vamos creando un array con disco - particion
for($cfgIndex = 0; $cfgIndex < $conKeys; $cfgIndex++){
	if($tbKeys[$cfgIndex]["numpar"] != 0 && $tbKeys[$cfgIndex]["clonable"] == 1){
		$disksPartitions["Disco " . $tbKeys[$cfgIndex]["numdisk"] . " - Part " .$tbKeys[$cfgIndex]["numpar"] ] = $tbKeys[$cfgIndex]["numdisk"].";".$tbKeys[$cfgIndex]["numpar"].";".$tbKeys[$cfgIndex]["tipopar"];
		$diskPartIndex++;
	}
}

?>
<input type="hidden" name="atrib_restore" value="">
<?php echo $TbMsg["WDI13"] ?> <br>
		<input type="radio" name="modo" id="check" value="deployImage" onClick="enableDirect(this.form);MuestraInsires();" checked ><?php echo $TbMsg["WDI14"] ?> <br>
		<input type="radio" name="modo" id="check" value="updateCache" onClick="disableDirect(this.form);MuestraInsires();" > <?php echo $TbMsg["WDI15"] ?> <br>

<tr> <td> <?php echo $TbMsg["WDI16"] ?>  </td> <td> <?php echo $TbMsg["WDI17"] ?> </td> <td>  <?php echo $TbMsg["WDI18"] ?> </td> <td>  <?php echo $TbMsg["WDI19"] ?> </td></tr>

<tr>  
	<td class="op_basic">
	   
	    <?php echo $TbMsg["WDI20"] ?> 
		<select name="idparticion" id="idparticion" style="width:220px">
				<?php
				foreach($disksPartitions as $key => $value){
					echo "<option value='".$value."'>".$key." </option>";
				}
				?>
		</select>
		<br />
	    <?php echo $TbMsg["WDI21"] ?>
		<select name="idimagen" id="idimagen" style="width:220px">
				<option value=""> <?php echo $TbMsg["WDI22"] ?></option>
				<?php echo ''. htmlOPTION_images($cmd,$ambito,$idambito) .'';   ?>
		</select>		
		<br />		
	    <?php echo $TbMsg["WDI23"] ?>
	
		<br />
		<select name="idmetodo" id="idmetodo" style="width:220px;">
			<option value="TORRENT"> TORRENT </option>
			<option value="MULTICAST"> MULTICAST </option>
			<option value="MULTICAST-DIRECT"> MULTICAST-DIRECT </option>
			<option value="UNICAST"> UNICAST </option>
			<option value="UNICAST-DIRECT"> UNICAST-DIRECT </option>
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




