<?php
// version 1.1: se incluye el atributo atrib_restore (ticket #757)
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-10-27

$disksPartitions = array();
$diskPartIndex = 0;
// Recorremos todas las configuraciones y vamos creando un array con disco - particion
for($cfgIndex = 0; $cfgIndex < $conKeys; $cfgIndex++){
	if($tbKeys[$cfgIndex]["numpar"] != 0 && $tbKeys[$cfgIndex]["clonable"] == 1){
		$disksPartitions[$diskPartIndex]["text"] = "Disco ".$tbKeys[$cfgIndex]["numdisk"]." - Part ".$tbKeys[$cfgIndex]["numpar"];
		$disksPartitions[$diskPartIndex]["value"] = $tbKeys[$cfgIndex]["numdisk"].";".$tbKeys[$cfgIndex]["numpar"];
		$diskPartIndex++;
	}
}

?>
		<input type="hidden" name="atrib_restore" value="">
<? echo $TbMsg["WDI13"] ?> <br>
		<input type="radio" name="modo" value="deployImage" onClick="enableDirect(this.form)" checked ><? echo $TbMsg["WDI14"] ?> <br>
		<input type="radio" name="modo" value="updateCache" onClick="disableDirect(this.form)" > <? echo $TbMsg["WDI15"] ?> <br> 
	
<tr> <td> <? echo $TbMsg["WDI16"] ?>  </td> <td> <? echo $TbMsg["WDI17"] ?> </td> <td>  <? echo $TbMsg["WDI18"] ?> </td> <td>  <? echo $TbMsg["WDI19"] ?> </td></tr>

<tr>  
	<td class="op_basic">
	   
	    <? echo $TbMsg["WDI20"] ?> 
		<select name="idparticion" id="idparticion" style="WIDTH:220">
				<?php
				foreach($disksPartitions as $diskPartition){
					echo "<option value='".$diskPartition["value"]."'>".$diskPartition["text"]." </option>";
				}
				?>
				<!--
				<option value="1"> 1 </option>
				<option value="2"> 2 </option>
				<option value="3"> 3 </option>
				-->
		</select>
		<br />
	    <? echo $TbMsg["WDI21"] ?>
		<select name="idimagen" id="idimagen" style="WIDTH:220">
				<option value=""> <? echo $TbMsg["WDI22"] ?></option>
				<?php echo ''. htmlOPTION_images($cmd,$ambito,$idambito) .'';   ?>
		</select>		
		<br />		
	    <? echo $TbMsg["WDI23"] ?>
	
		<br />
		<select name="idmetodo" id="idmetodo" style="WIDTH:220;">
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




