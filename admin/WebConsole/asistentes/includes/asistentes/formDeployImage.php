
<? echo $TbMsg["WDI13"] ?> <br>
		<input type="radio" name="modo" value="deployImage" checked ><? echo $TbMsg["WDI14"] ?> <br>
		<input type="radio" name="modo" value="updateCache" > <? echo $TbMsg["WDI15"] ?> <br> 
	
<tr> <td> <? echo $TbMsg["WDI16"] ?>  </td> <td> <? echo $TbMsg["WDI17"] ?> </td> <td>  <? echo $TbMsg["WDI18"] ?> </td> <td>  <? echo $TbMsg["WDI19"] ?> </td></tr>

<tr>  
	<td class="op_basic">
	   
	    <? echo $TbMsg["WDI20"] ?> 
		<select name="idparticion" id="idparticion" style="WIDTH:220">
				<option value="1"> 1 </option>
				<option value="2"> 2 </option>
				<option value="3"> 3 </option>
		</select>
		<br />
	    <? echo $TbMsg["WDI21"] ?>
		<select name="idimagen" id="idimagen" style="WIDTH:220">
				<option value=""> <? echo $TbMsg["WDI22"] ?></option>
				<?php echo ''. htmlOPTION_images($cmd,$ambito,$idambito) .'';   ?>
		</select>		
		<br />		
	
		<br />
		<select name="idmetodo" id="idmetodo" style="WIDTH:220";">
		<!--	<option value="UNICAST"> UNICAST </option> -->
			<option value="TORRENT"> TORRENT </option>
			<option value="MULTICAST"> MULTICAST </option>
			<option value="UNICAST"> UNICAST </option>
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




