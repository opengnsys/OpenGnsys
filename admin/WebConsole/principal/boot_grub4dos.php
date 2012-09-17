<html>
<TITLE>Administración web de aulas</TITLE>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../idiomas/javascripts/esp/propiedades_aulas_esp.js"></SCRIPT></HEAD>

<script language="javascript" type="text/javascript">

function move(fbox, tbox) {
	var arrFbox = new Array();
	var arrTbox = new Array();
	var arrLookup = new Array();
	var i;
	for (i = 0; i < tbox.options.length; i++) {
		arrLookup[tbox.options[i].text] = tbox.options[i].value;
		arrTbox[i] = tbox.options[i].text;
	}
	var fLength = 0;
	var tLength = arrTbox.length;
	for(i = 0; i < fbox.options.length; i++) {
		arrLookup[fbox.options[i].text] = fbox.options[i].value;
		if (fbox.options[i].selected && fbox.options[i].value != "") {
			arrTbox[tLength] = fbox.options[i].text;
			tLength++;
		}
		else {
			arrFbox[fLength] = fbox.options[i].text;
			fLength++;
  		  }
		}
	arrFbox.sort();
	arrTbox.sort();
		fbox.length = 0;
		tbox.length = 0;
	var c;

for(c = 0; c < arrFbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrFbox[c]];
no.text = arrFbox[c];
fbox[c] = no;
}

for(c = 0; c < arrTbox.length; c++) {
var no = new Option();
no.value = arrLookup[arrTbox[c]];
no.text = arrTbox[c];
tbox[c] = no;
    }
}

function allSelect()
{
var saveString = "";
// seleccionamos cada uno de los select
var input = document.getElementsByTagName('select');
//alert(input.length);
for(var i=0; i<input.length; i++){
//if(inputs[i].getAttribute('type')=='button'){
// your statements
patron = "L";
parm = input[i].name;
//alert(parm);
parm = parm.replace(patron,'');
//alert(parm);
for (j=0;j<input[i].length;j++)
		{
			//List.options[i].selected = true;
			saveString = saveString + parm + '|' + input[i].options[j].value + ';';
			//alert(saveString);			
		}
}
document.forms['myForm'].listOfItems.value = saveString;
}


</script>
</head>
<body>

<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/SockHidra.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/aulas_".$idioma.".php");

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________

$litambito=0; 
$idambito=0; 
$nombreambito=""; 
$id_aula=0; 


if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; // Recoge parametros
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if (isset($_GET["id_aula"])) $idambito=$_GET["id_aula"]; 


switch($litambito){
	case "aulas":
		$seleccion="and idaula=" .  $idambito ."";
		break;
	case "gruposordenadores":
		$seleccion= "and grupoid=" .  $idambito . "";
	break;
}
?>

<TABLE  align=center border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<form name="myForm" method="post" action="../gestores/gestor_pxe_grub4dos.php?idaula=<?php echo $idambito ?>&nombreambito=<?php echo $nombreambito?>&litambito=<?php echo $litambito?>" >


	<P align=center class=cabeceras><?php echo $TbMsg[42]; ?><BR>
	<SPAN align=center class=subcabeceras> <?php echo $nombreambito; ?> </SPAN>
	<input type="submit" value=<?php echo $TbMsg[43]; ?> name="saveButton"  onclick="allSelect()"> </P>
	
	
	



<input type="hidden" name="listOfItems" value="">
<input type="hidden" name="rungrupo" value="<?php echo $id_aula;?>">


<!-- primer file, nombre de las equipos por pxe hace falta  <td>  </td>-->
<tr>
<td> 
 <!-- <a href="./muestramenu.php?labelmenu=pxe">  OGclient </a><br> pxe <br> -->
<br>OGclient <br><br>
<select multiple size="30" name="Lpxe" id="Lpxe">

<?php
#### listado de equipos con menu pxe
$menupxe=pxe;
$listadopxe=listaequipos($cmd,$menupxe,$seleccion);
echo $listadopxe;
?>
</select>
</td>


<?php

$cmd->texto="SELECT * FROM menuboot where label <> 'pxe' "; 
$rsmenu=new Recordset; 
$rsmenu->Comando=&$cmd; 
if (!$rsmenu->Abrir()) echo "error";
$rsmenu->Primero(); 
while (!$rsmenu->EOF)
{ 
	echo "<td></td>";
	echo "<td> ";
	echo $rsmenu->campos['description'];
	#echo "<a href='./muestramenu.php?labelmenu=" . $rsmenu->campos['label'] ."'>  " . $rsmenu->campos['description']  . " </a> <br>". $rs->campos['label'] . "<br>";
 	echo " <br>";
	echo "<input type='button' onClick='move(this.form.L" . $rsmenu->campos['label'] . ",this.form.Lpxe)' value='OUT' style='height: 25px; width: 50px' >";
 	echo "<input type='button' onClick='move(this.form.Lpxe,this.form.L" . $rsmenu->campos['label'] .")' value='IN' style='height: 25px; width: 35px' >";
 	echo " <br>";
	echo "<select multiple size='30' name='L" . $rsmenu->campos['label'] . "' >";
    $listadopxe="";
	$listadopxe=listaequipos($cmd,$rsmenu->campos['label'],$seleccion);
	echo $listadopxe;
	echo "</select>";
	echo "</td>";
	$rsmenu->Siguiente();
}
$rsmenu->Cerrar();







// esta funcion genera los elementos de un select(formulario html) donde aparecen los nombres de los ordenadores, según su menu pxe
function listaequipos($cmd,$menupxe,$seleccion)
{
$cmd->texto="SELECT * FROM ordenadores where arranque='" . $menupxe ."' " . $seleccion; 
$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
while (!$rs->EOF)
{ 
	echo "<option value='";
	echo $rs->campos["nombreordenador"];
	echo "'>";
	echo $rs->campos["nombreordenador"];
	echo "</option>";
	$rs->Siguiente();
}
$rs->Cerrar();
}

?>

</tr>

</form>
</table>

</body>
</html>
