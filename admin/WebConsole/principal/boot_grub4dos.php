<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/MenuContextual.php");
include_once("../clases/SockHidra.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/boot_grub4dos_".$idioma.".php");

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexiÃ³n con servidor B.D.
//________________________________________________________________________________________________________

$litambito=0; 
$idambito=0; 
$nombreambito=""; 
$opcion=0;
$modo="";


if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; // Recoge parametros
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["nombreambito"])) $nombreambito=$_GET["nombreambito"]; 
if (isset($_POST["litambito"])) $litambito=$_POST["litambito"]; // Recoge parametros
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 
if (isset($_POST["opcion"])) $opcion=$_POST["opcion"];
if (isset($_POST["modo"])) $modo=$_POST["modo"];

switch($litambito){
	case "aulas":
		$seleccion="and idaula=" .  $idambito ."";
		break;
	case "gruposordenadores":
		$seleccion= "and grupoid=" .  $idambito . "";
	break;
}

// Leyendo el directorio de plantillas PXE (/opt/opengnsys/tftboot/menu.lst/templates)
$dirtemplates="/opt/opengnsys/tftpboot/menu.lst/templates/";
// Leer nombres de ficheros y quitar plantilla "pxe".
chdir($dirtemplates);
$pn=glob("*");
unset($pn[array_search("pxe", $pn)]);
sort($pn);
chdir(__DIR__);

//Leemos el ultimo fichero y extraemos su numero 
$ultimofichero=end($pn);
$ultimonumero=substr($ultimofichero,0,2);

//Comprobamos que no se mayor que 99 (tendria 3 caracteres)
if ($ultimonumero==99)
	{$ultimonumero=20;
}else{
	$ultimonumero++;
}

//Buscamos si el siguiente numero esta disponible
$encontrado=FALSE;
while($encontrado==FALSE)
{
	if (in_array($ultimonumero, $pn))
	{
		$ultimonumero++;
	}else{
		$encontrado=TRUE;
	}
}
?>
<html>
<TITLE>Administración web de aulas</TITLE>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/boot_grub4dos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../idiomas/javascripts/esp/propiedades_aulas_esp.js"></SCRIPT></HEAD>
</head>
<body>
<TABLE  align=center border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR valign="bottom"><TD colspan="100%" align="left" nowrap>
<form name="modoadmin" id="modoadmin" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
   		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
   		<input type="hidden" name="opcion" value="<?php echo $opcion?>">
<?php	
	if (empty($_SESSION["widcentro"])) {
		$modo=1;
	}
?>
</form>

</TD></TR>
<tr>
<?php
if (! empty($modo))
{ ?>
<td valign="top">
<?php include_once("./boot_grub4dos_tabla.php");?>
</td>
<?php }?>
<form name="myForm" method="post" action="../gestores/gestor_pxe_grub4dos.php?idaula=<?php echo $idambito ?>&nombreambito=<?php echo $nombreambito?>&litambito=<?php echo $litambito?>" >

	<P align=center class=cabeceras><?php echo $TbMsg[42]; ?><BR>
	<span align=center class=subcabeceras> <?php echo $nombreambito; ?> </span>
<?php /////////////////////////////////////////////////
 if (!empty($_SESSION["widcentro"])){ ?>
	<input type="submit" value=<?php echo $TbMsg[43]; ?> name="saveButton"  onclick="allSelect()"></P>
<?php /////////////////////////////////////////////////
 } ?>


<input type="hidden" name="listOfItems" value="">
<?php
?>
<!-- primer file, nombre de las equipos por pxe hace falta  <td>  </td>-->
<td width="80"> 
 <!-- <a href="./muestramenu.php?labelmenu=pxe">  OGlive </a><br> pxe <br> -->
<?php 
//Leer fichero pxe
$description=exec("awk 'NR==1 {print $2}' ".$dirtemplates."pxe");//$text=trim($text);
?> 
<br><?php echo $description;?> <br><br>
<select multiple size="28" name="Lpxe" id="Lpxe">

<?php
#### listado de equipos con menu pxe
$menupxe="pxe";
//////////////////////////////////////////////////
if (!empty($_SESSION["widcentro"]))
//////////////////////////////////////////////////
{
	$listadopxe=listaequipos($cmd,$menupxe,$seleccion);
	echo $listadopxe;
}
?>
</select>
</td>
<?php
//##agp
    //$listadopxe="";
    $desconocido="00unknown";
//

//mostrar los datos
for($i=0; $i<count($pn); $i++)
	{//for
    if ($pn[$i]==$desconocido)
	{$listadopxe=listadesconocido($cmd,$desconocido,$seleccion);
		if ($existe==""){}else{

			$description=exec("awk 'NR==1 {print $2}' ".$dirtemplates.$pn[$i]);	//$text=trim($text);
			echo "<td></td>";
			echo "<td> <font color=red>";
			echo $description;
 			echo " <br>";
 			echo "<input type='button' onClick='move(this.form.L" . $pn[$i] . ",this.form.Lpxe)' value='OUT' style='height: 25px; width: 50px' >";
 			echo "<input type='button' onClick='move(this.form.Lpxe,this.form.L" . $pn[$i] .")' value='IN' style='height: 25px; width: 35px' >";
 			echo " <br>";
			echo "<select multiple size='28' name='L" . $pn[$i] . "' >";
 			$listadopxe="";
  			$desconocido="00unknown";
				if ($pn[$i]==$desconocido)
				{
				$listadopxe=listaequipos($cmd,$desconocido,$seleccion);
				echo $listadopxe;
				}else{
				$listadopxe=listaequipos($cmd,$pn[$i],$seleccion);
				echo $listadopxe;
				}
	echo "</select>";
	echo "</td>";
					}

	}else{
	$description=exec("awk 'NR==1 {print $2}' ".$dirtemplates.$pn[$i]);	//$text=trim($text);
	echo "<td></td>";
	echo "<td> ";
	echo $description;
 	echo " <br>";
 	   echo "<input type='button' onClick='move(this.form.L" . $pn[$i] . ",this.form.Lpxe)' value='OUT' style='height: 25px; width: 50px' >";
 	echo "<input type='button' onClick='move(this.form.Lpxe,this.form.L" . $pn[$i] .")' value='IN' style='height: 25px; width: 35px' >";
 	echo " <br>";
	echo "<select multiple size='28' name='L" . $pn[$i] . "' >";
 	$listadopxe="";
 	$desconocido="00unknown";
///////////////////////////////////////////////////////////////
if (!empty($_SESSION["widcentro"]))
{
	if ($pn[$i]==$desconocido)
	{
	$listadopxe=listaequipos($cmd,$desconocido,$seleccion);
	echo $listadopxe;
	}else{
	$listadopxe=listaequipos($cmd,$pn[$i],$seleccion);
	echo $listadopxe;
		}
}
////////////////////////////////////////////////////////////////
	echo "</select>";
	echo "</td>";
		}//Primer if
	}//for
//##agp

// esta funcion genera los elementos de un select(formulario html) donde aparecen los nombres de los ordenadores, según su menu pxe
function listaequipos($cmd,$menupxe,$seleccion)
{
$cmd->texto="SELECT  idordenador, nombreordenador
		FROM ordenadores
		WHERE arranque='" . $menupxe ."' " . $seleccion;
$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
while (!$rs->EOF)
{ 
	echo "<option value='".$rs->campos["idordenador"]."'>".$rs->campos["nombreordenador"]."</option>";
	$rs->Siguiente();
}
$rs->Cerrar();
}

// esta funcion genera los elementos de un select(formulario html) donde aparecen los nombres de los ordenadores, según su menu pxe
function listadesconocido($cmd,$desconocido,$seleccion)
{
global $existe;
$cmd->texto="SELECT * FROM ordenadores where arranque='" . $desconocido ."' " . $seleccion; 
$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
while (!$rs->EOF)
{ 
$existe= $rs->campos["nombreordenador"];
	$rs->Siguiente();
}
$rs->Cerrar();
}


?>
</form>
</tr>



</table>


</body>
</html>
