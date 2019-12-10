<?php

$disksPartitions = array();
$diskPartIndex = 0;
// Recorremos todas las configuraciones y vamos creando un array con disco - particion
for($cfgIndex = 0; $cfgIndex < $conKeys; $cfgIndex++){
	if($tbKeys[$cfgIndex]["numpar"] != 0 && $tbKeys[$cfgIndex]["clonable"] == 1){
		$disksPartitions["Disco " . $tbKeys[$cfgIndex]["numdisk"] . " - Part " .$tbKeys[$cfgIndex]["numpar"] ] = $tbKeys[$cfgIndex]["numdisk"]." ".$tbKeys[$cfgIndex]["numpar"];
		$diskPartIndex++;
	}
}

?>


<tr>
	<td class="op_basic">
	    <?php echo $TbMsg["WCRP32"] ?> 
		<select name="ipMaster" id="ipMaster" style="width:220px" onChange="ListarOrigenesMaster(this.value);">
				<option value=""> -- <?php echo $TbMsg["WCRP32"] ?> -- </option>
				<?php echo ''.htmlOPTION_equipos($cmd,$ambito,$idambito).'';   ?>
		</select>		
		<br />		
		<!--DIV donde se mostrara la respuesta AJAX sobre las particiones clonables del equipo-->
		<?php echo $TbMsg["WCRP33"] ?>
		<div id="ajaxDiv"></div>
		<br />
		<?php echo $TbMsg["WCRP34"] ?> 
		<select name="targetpart" id="targetpart" style="width:220px;">
		<?php
				foreach($disksPartitions as $key => $value){
					echo "<option value='".$value."'>".$key." </option>";
				}
		?>
		</select>
		<br />
		<?php echo $TbMsg["WCRP35"] ?>
		<select name="idmetodo" id="idmetodo" style="width:220px;">
			<option value="MULTICAST"> MULTICAST </option>
			<option value="UNICAST"> UNICAST </option> 			
		</select>
		<br />
		<?php echo $TbMsg["WCRP36"] ?>
		<select name="tool" id="tool" style="width:220px;">
			<option value="partclone"> partclone </option>
		<!--	<option value="partimage"> partimage </option> -->
		</select>
		<br />
		<?php echo $TbMsg["WCRP37"] ?>
		<select name="compresor" id="compresor" style="width:220px;">
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





