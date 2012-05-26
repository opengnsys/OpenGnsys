<tr>
<td>
        Aplicar acciones al disco:
        <input type="text" name="n_disk" value="1">
</td>
</tr>
<tr>
<td>
        Tabla de particiones:
        <select name="tipo_part_table" id="tipo_part_table" onchange="showPartitionForm(this.value)">
                <option value="MSDOS">MSDOS</option>
                <option value="GPT">GPT</option>
        </select>
</td>
</tr>
<div id="formMSDOS">
	<? include_once("includes/asistentes/formParticionado_msdos.php");?>
</div>
<div id="formGPT">
	 <? include_once("includes/asistentes/formParticionado_gpt.php");?>
</div>
