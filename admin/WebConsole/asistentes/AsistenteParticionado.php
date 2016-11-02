<?php 
// *************************************************************************************************************************************************
// Aplicacion WEB: ogAdmWebCon
// Autor: Antonio J. Doblas Viso
// Baso en Codigo  Comando.php de : Jose Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
//	Xajax
// version 1.1: Se incluye aviso para particiones GTP.
// autor: Irina Gomez, ETSII Universidad de Sevilla
// fecha: 2016-06-21
// *************************************************************************************************************************************************


require_once('xajax.common.php'); 
//Haciendo referencia a las funciones registradas y que seran interpretadas como funciones javascript



/********HACIENDO CONSULTA A LA TABLA ordenadores***********/
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/ejecutarscripts_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/configuraciones_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../includes/RecopilaIpesMacs.php");

 include_once("./includes/asistentes/AyudanteFormularios.php");


//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="./jscripts/EjecutarScripts.js"></SCRIPT>
	<SCRIPT language="javascript" src="../comandos/jscripts/comunescomandos.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/asistentes.js"></SCRIPT>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/ejecutarscripts_'.$idioma.'.js"></SCRIPT>'?>
	<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>


<?php 
//indicamos al objeto xajax se encargue de generar el javascript de las funciones registradas por ejm: ListarParticionesXip
$xajax->printJavascript('../xajax/'); 
?>
<script>
function doOnload(){
	calculateFreeDisk(document.fdatos);
}

</script>
</head>


<body onload="doOnload()">
<?php
	switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			if (isset($_GET["idambito"])) $idambito=$_GET["idambito"];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
	
	echo '<p align=center><span class=cabeceras>'.$descricomando.'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[1].'
			: '.$textambito.'</U></span>&nbsp;&nbsp;</span></p>';

	$sws=0x11111;	// Mostrar todas las configuraciones diferentes.
	$configuraciones = pintaConfiguraciones($cmd,$idambito,$ambito,7,$sws,false);
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	// numero de discos minimo: partimos de un valor alto y comparamos con la configuracion de cada pc.
	$mindisks = 10;
	foreach($configuraciones as $configuracion){
		// Separamos las configuraciones segun el disco al que pertenezcan
		$diskConfigs = splitConfigurationsByDisk($configuracion);
		// En diskconfigs tendremos un array con tantas configuraciones como discos, 
		// no quedamos con su length que será el numero de discos
		$aux = count($diskConfigs);
		if ( $mindisks > $aux ) 
			 $mindisks = $aux;
	}	
?>

	<form  align=center name="fdatos" > 

		<table class="tabla_datos">
		<tr>
		<td>
			<?php echo $TbMsg[35].":\n"; 	// Disco ?>
		        <select id="n_disk" onchange="calculateFreeDisk(document.fdatos)">
			<?php	for($d = 1; $d <= $mindisks; $d++){
					echo "<option value=\"$d\">$d</option>\n";
		      		} ?>
		        </select>
		</td>
		</tr>
		<tr>
		<td>
			<?php echo $TbMsg["CONFIG_PARTTABLE"].":\n"; ?>
			<select name="tipo_part_table" id="tipo_part_table" onchange="showPartitionForm(this.value)">
				<option value="MSDOS">MSDOS</option>
				<option value="GPT">GPT</option>
			</select>
		</td>
		</tr>
		</table>
		<div id="formMSDOS">
			<table class="tabla_datos" border="0" cellpadding="0" cellspacing="1">
				<?php include_once("includes/asistentes/formParticionado_msdos.php");?>
			</table>
		</div>
		<div id="formGPT" style="display:none">
			<table class="tabla_datos" border="0" cellpadding="0" cellspacing="1">
				<?php include_once("includes/asistentes/formParticionado_gpt.php");?>
			</table>
		</div>
		<table class="tabla_datos">		
			<tr> 
				<th><input type="button" name="GenerarInstruccion" Value="<?php echo $TbMsg[41];?>" onclick="codeParticionado(this.form)" /> </th>
				<td colspan="2"><textarea class="cajatexto" name="codigo" id="codigo" cols="70" rows="7"></textarea></td>
			</tr>
			<tr>
				<th colspan="3"><?php echo $TbMsg["WARN_REBOOTAFTER"]; ?></th>
			</tr>
			<tr id="warngpt" style="display:none">
				<th colspan="3" ><?php echo $TbMsg["WARN_GPT"]; ?></th>
			</tr>
		</table>
	</form>	

<?php
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________

?>


</body>
</html>

