<?php
// Version 1.1.1 - Muestra las plantillas tipo BIOS y UEFI. Se incluyen algunos id para pasar los datos necesarios a serclientmode (#802).
// Autor: Irina Gomez - ETSII Universidad de Sevilla.
// Fecha: 2019/02/12

include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
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
if (isset($_REQUEST["litambito"])) $litambito=$_REQUEST["litambito"]; // Recoge parametros
if (isset($_REQUEST["idambito"])) $idambito=$_REQUEST["idambito"];
if (isset($_REQUEST["nombreambito"])) $nombreambito=$_REQUEST["nombreambito"];
if (isset($_REQUEST["opcion"])) $opcion=$_REQUEST["opcion"];
if (isset($_REQUEST["modo"])) $modo=$_REQUEST["modo"];
if (empty($_SESSION["widcentro"])) $modo=1;

switch($litambito){
	case "aulas":
		$seleccion="and idaula=" .  $idambito ."";
		break;
	case "gruposordenadores":
		$seleccion= "and grupoid=" .  $idambito . "";
		break;
	default:
		$seleccion="";
	break;
}

//#########################################################################
// LEYENDO EL DIRECTORIO
// /var/lib/tftboot/menu.lst/templates y /var/lib/tftpboot/grub/templates/
//#########################################################################
// Leer nombres de ficheros plantillas bios
$dirtemplatesbios="/opt/opengnsys/tftpboot/menu.lst/templates/";
chdir($dirtemplatesbios);
$pnbios=glob("*");

// Leer nombres de ficheros plantillas uefi
$dirtemplatesuefi="/opt/opengnsys/tftpboot/grub/templates/";
chdir($dirtemplatesuefi);
$pnuefi=glob("*");

// Unimos las plantillas y eliminamos repetidos
$pn=array_unique(array_merge($pnbios,$pnuefi));

// Numero columnas
$column=count($pn);

// Plantilla en los dos directorios
$pncomun=array_intersect($pnbios,$pnuefi);

// quitar plantilla "pxe".
unset($pn[array_search("pxe", $pn)]);
sort($pn);
chdir(__DIR__);


?>
<html>
<head>
<TITLE>Administración web de aulas</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_aulas.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/boot_grub4dos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../idiomas/javascripts/esp/propiedades_aulas_esp.js"></SCRIPT>
</head>
<body>
<form name="modoadmin" id="modoadmin" method="post" action="../gestores/gestor_pxe_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
   		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
   		<input type="hidden" name="opcion" value="<?php echo $opcion?>">
		<input type="hidden" name="listOfItems" value="">
                <!-- para la zona de administración -->
		<input type="hidden" name="opcioncrear" value="">
	<P align=center class=cabeceras><?php echo $TbMsg[42]; ?><BR>
	<span align=center class=subcabeceras>&nbsp; <?php echo $nombreambito; ?> </span>
<TABLE  align=center border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR valign="bottom"><TD colspan="100%" align="left" nowrap>&nbsp;
</TD></TR>
<tr>
<?php
// Si el modo no está vacio estamos en la parte de administración
// Incluyo un a primera columna con las opciones crear, modificar,...
if (! empty($modo)) include_once("./boot_grub4dos_tabla.php");
?>

<?php /////////////////////////////////////////////////
 if (!empty($_SESSION["widcentro"])){ ?>
	<input type="submit" value=<?php echo $TbMsg[43]; ?> name="saveButton"  onclick="allSelect()"></P>
<?php /////////////////////////////////////////////////
 } ?>


<!-- primer file, nombre de las equipos por pxe hace falta  <td>  </td>-->
<td width="80" id='ogLive'>
 <!-- <a href="./muestramenu.php?labelmenu=pxe">  OGlive </a><br> pxe <br> -->
<?php 
//Leer fichero pxe
$description=exec("awk 'NR==1 {print $2}' ".$dirtemplatesbios."pxe");
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
for($i=0; $i<count($pn); $i++) {
    $nocomun="";
    $description=exec("awk 'NR==1 {print $2}' ".$dirtemplatesbios.$pn[$i]);
    // Si la plantilla no es comun, definimos si es bios o uefi
    if ( ! in_array($pn[$i],$pncomun)) {
        $nocomun= ($description === "") ? "(uefi)" : "(bios)";
    }
    // Si la descripción está vacía consultamos las plantillas uefi
    if ($description == "") $description=exec("awk 'NR==1 {print $2}' ".$dirtemplatesuefi.$pn[$i]);

    if ($pn[$i]==$desconocido)
	{$listadopxe=listadesconocido($cmd,$desconocido,$seleccion);
		// Solo lo mostramos si existen aquipos no asignados.
		if (isset($existe)){
			echo "<td></td>";
			echo "<td><font id='$description' color=red>";
			echo $description;
 			echo " <br>";
 			echo "<input type='button' onClick='move(this.form.L" . $pn[$i] . ",this.form.Lpxe)' value='OUT' style='height: 25px; width: 50px' >";
 			echo "<input type='button' onClick='move(this.form.Lpxe,this.form.L" . $pn[$i] .")' value='IN' style='height: 25px; width: 35px' >";
 			echo " <br>";
			echo "<select multiple size='28' name='L" . $pn[$i] . "' >";
 			$listadopxe="";
  			$desconocido="00unknown";
			$listadopxe=listaequipos($cmd,$pn[$i],$seleccion);
			echo $listadopxe;
			echo "</select>";
			echo "</td>";
		}

    } else {
	echo "<td></td>\n";
	echo "<td id='$description'> ";
	echo $description ." ". $nocomun;
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
		$listadopxe=listaequipos($cmd,$pn[$i],$seleccion);
		echo $listadopxe;
	}
	////////////////////////////////////////////////////////////////
	echo "</select>";
	echo "</td>";
    }//Primer if
}//for
//##agp
?>
</tr>
<tr><th colspan="<?php echo (2*$column) ?>"><?php echo $TbMsg["UEFI"]; ?></th></tr>
</table>
</form>

</body>
</html>

<?php
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
