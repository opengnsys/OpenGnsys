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

if (isset($_POST["litambito"])) $litambito=$_POST["litambito"]; // Recoge parametros
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["nombreambito"])) $nombreambito=$_POST["nombreambito"]; 
if (isset($_POST["opcion"])) $opcion=$_POST["opcion"];
if (isset($_POST["opcioncrear"])) $opcioncrear=$_POST["opcioncrear"];
$ultimonumero = isset($_POST["ultimonumero"]) ? $_POST["ultimonumero"] : "";
$boton = isset ($_REQUEST["boton"]) ? $_REQUEST["boton"] : "";
$confirmado = ($boton == $TbMsg[13] && ($opcioncrear == 1 || $opcioncrear == 2)) ? "1" : "";
$guarnomb = isset($_POST["nombrenuevoboot"]) ? ucfirst($_POST["nombrenuevoboot"]) : "";
$admin = isset($_POST["modo"]) ? $_POST["modo"] : "";
$selecdescripcion = isset($_POST["selecdescripcion"]) ? $_POST["selecdescripcion"] : "";
$descripcion = "";
$modo = "";
$seleccion = "";

switch($litambito){
	case "aulas":
		$seleccion="and idaula=" .  $idambito ."";
		break;
	case "gruposordenadores":
		$seleccion= "and grupoid=" .  $idambito . "";
	break;
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
<P align=center class=cabeceras><?php echo $TbMsg[42]; ?><BR>
	<SPAN align=center class=subcabeceras> <?php echo $nombreambito; ?> </SPAN></P>
<!--	<input type="submit" value=<?php echo $TbMsg[43]; ?> name="saveButton"  onclick="allSelect()"> </P>   -->
<?php
//##################################################################################################################################
//###########  NUEVO COLUMNA ARRANQUE  #############################################################################################
//##################################################################################################################################

if ($opcioncrear == 1)
	{
	//$confirmado=$_POST["confirmado"];
	if ($confirmado == 1)
		{	
				//$delcar=array(" "," /", "-", "@", "=");
				$descripfich=$guarnomb;$descripfich=preg_replace("/[^A-Za-z0-9]/", "-", $descripfich);//str_replace($delcar, "-", $descripfich);
				$guarnomb=preg_replace("/[^A-Za-z0-9]/", "", $descripfich);//str_replace($delcar, "", $guarnomb);
				$nombrenuevoboot=$ultimonumero.$guarnomb;
				$parametrosnuevoboot=$_POST["parametrosnuevoboot"];
				$nuevoboot = "/var/lib/tftpboot/menu.lst/templates/".$nombrenuevoboot;
			if($guarnomb != "") {
				$fp = fopen($nuevoboot, "w");
				$string = $TbMsg[22].$descripfich."\n".$parametrosnuevoboot;
				$write = fputs($fp, $string);
				fclose($fp);
			?>
						<TABLE width="500" align=center border=1 >
						<TR><TD align="center"><br><?php if ($guarnomb != null) echo $TbMsg[6];?><br><br><SPAN align=center class=subcabeceras><?php echo $descripfich;?></span><br><br><br>	
						<form name="crearranque" method="post" action="./boot_grub4dos.php">
						<input type="hidden" name="confirmado" value="1">
			<?php }else{ ?>

						<TABLE width="500" align=center border=1 >
						<TR><TD align="center"><br><br><br><SPAN align=center class=subcabeceras><?php echo $TbMsg[14];?></span><br><br><br>	
						<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
						<input type="hidden" name="confirmado" value="">
						<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
			<?php }?>

						<input type="hidden" name="litambito" value="<?php echo $litambito?>">
						<input type="hidden" name="idambito" value="<?php echo $idambito?>">
						<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
						<input type="hidden" name="opcioncrear" value="1">
						<input type="submit" value="Continuar" name="nuevoarran">
						</form>
						</TR></TD>
						</TABLE>
<?php }else{
?>

<TABLE width="650" align=CENTER border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >

<TR align=center>
	<TD height="70" colspan="2" valign="middle">
		<SPAN align=center class=cabeceras> <?php echo $TbMsg[3]?> </SPAN>
	</TD>
  </TR>
<TR align=right>
	<TD colspan="2" valign="middle">



	</TD>
  </TR>
<TR>
<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
	<TD width="150" height="10" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[12]?></SPAN>
	</TD>

	<TD width="500" height="10" valign="middle">
		<input type="text" name="nombrenuevoboot" id="textfield" size="25" value="<?php echo $guarnomb ?>">
	</TD>

</TR>
<TR>
	<TD width="150" height="100" valign="middle">

<SPAN align=center class=subcabeceras><?php echo $TbMsg[19]?><br></SPAN>
<?php
if ($boton == $TbMsg[17])
{echo '<input name=boton type=submit value="'.$TbMsg[18].'">';}else{echo '<input name=boton type=submit value=Plantilla>';}
?>
	</TD>

	<TD width="500" height="100" valign="middle">


	<textarea name="parametrosnuevoboot" id="parametrosnuevoboot" cols="60" rows="12">
<?php
if ($boton == $TbMsg[17])
echo "timeout 3
title FirstHardDisk-FirstPartition
keeppxe
root (hd0,0)
chainloader (hd0,0)+1
boot";
?>
	</textarea>		
	</TD>
</TR>
<TR>
	<TD width="150"  valign="middle">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="opcioncrear" value="1">

		<input type="submit" name="boton" value="<?php echo $TbMsg[13]?>">
  </form>
	</TD>

<TD width="500"  valign="middle"><br />
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="modo" value="1">
		<input type="submit" value="<?php echo $TbMsg[16]?>">
		</form>
	</TD>
</TR>
</TABLE>
<?php
//##################################################################################################################################
//###########  NUEVO COLUMNA ARRANQUE  #############################################################################################
//##################################################################################################################################
}}?>


<?php
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################


if ($opcioncrear == 2)
	{
	$confirmado=isset($_POST["confirmado"]) ? $_POST["confirmado"] : "";
	if ($confirmado == 1)
		{
				$modificadescripcion=ucfirst($_POST["modificadescripcion"]);
//				$modificadescripcion=str_replace(" ", "", $modificadescripcion);
				$descripfich=$modificadescripcion;$descripfich=preg_replace("/[^A-Za-z0-9]/", "-", $descripfich);
				$ficherow="/var/lib/tftpboot/menu.lst/templates/".$_POST["nombrefichero"];//echo $ficherow."<br>";
				$parametrosmodifica=$_POST["parametrosmodifica"];

				if(empty($_POST["modificadescripcion"]))
				{?>

		<TABLE width="500" align=center border=1 >
		<TR><TD align="center"><br><br><br><SPAN align=center class=subcabeceras><?php echo $TbMsg[14];?></span><br><br><br>
		<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="0">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="submit" value="Continuar" name="nuevoarran">
		</form>
		</TR></TD>
		</TABLE>

				<?php }else{
				//echo $_POST["nombrefichero"]." -- Descripcion -- ".$descripfich."<br>".$string;
			///*
				$fp = fopen($ficherow, "w");
				$string = $TbMsg[22].$descripfich."\n".$_POST["parametrosmodifica"];
				$write = fputs($fp, $string);//Escribe la primera linea
				fclose($fp);
			//*/


		?>
		<TABLE width="500" align=center border=1 >
		<TR><TD align="center"><br><br><br><SPAN align=center class=subcabeceras><?php echo $TbMsg[7];?></span><br><br><br>
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="0">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="hidden" name="modo" value="1">
		<input type="submit" value="Continuar" name="nuevoarran">
		</form>
		</TR></TD>
		</TABLE>
					<?php }?>
		<?php }else{
?>
<?php
//#########################################################################
// MODO USUARIO
//#########################################################################
if (empty($admin)){
//#########################################################################
// LEYENDO EL DIRECTORIO
// /var/lib/tftboot/menu.lst/templates
//#########################################################################
$dirtemplates="/var/lib/tftpboot/menu.lst/templates/";
$directorio=dir($dirtemplates);
$pn= array();//pila de nombres
//bucle para llenar las pilas :P
while ($archivo = $directorio->read())
{
	//no mostrar ni "." ni ".." ni "pxe"
	if(($archivo!="pxe")&&($archivo!=".")&&($archivo!=".."))
	{
	array_push($pn, $archivo);
	}
}
$directorio->close();

//ordenar las pilas segun la pila de nombres
array_multisort($pn);

//Leyendo la Descripcion de los ficheros mayores que 20
for ($b=0;$b<count($pn);$b++)
{
$numeros=substr($pn[$b],0,2);
if ($numeros > 19)
	{
	$descripcion=exec("cat ".$dirtemplates.$pn[$b]." | awk 'NR==1  {print $2}'");//$text=trim($text);
	//Aqui busco el fichero, parametros y descripcion segun llega de $_POST["modificafichero"]
	if ($descripcion == $selecdescripcion)
		{
		$fichero=$pn[$b];
		$param=$dirtemplates.$fichero;
		$parametros=file($param);
		//echo $fichero." -- Descripcion -- ".$descripcion."<br>";
		}

	}
}

?>
<TABLE width="850" align=CENTER border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR >
	<TD height="70" colspan="2" valign="middle">
		<p align=center><SPAN align=center class=cabeceras> <?php echo $TbMsg[4]?> </SPAN></p><p aling=left></p>
        <form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
        	<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="hidden" name="modo" value="1">
        <?php if ($_SESSION["wadminetboot"] == 1 ){ ?>
                <input type="submit" value=<?php echo $TbMsg[11]?> name="nuevoarran">
        <?php } ?>

        </form>
	</TD>
  </TR>
 <?php if ($numeros > 19){  ?>
<TR>
<form name="actualiza" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="opcioncrear" value="2">

	<TD height="10" colspan="2" valign="middle">
	  <SPAN align=center class=subcabeceras><?php echo $TbMsg[9]." ------> ";?></SPAN>
	  <select name="selecdescripcion" id="selecdescripcion" onChange="document.actualiza.submit()">
	    <option value"" ></option>
	    <?php
		for ($z=0;$z<count($pn);$z++)
		{
		if((substr($pn[$z],0,2)) > 19)
			{
			$descripcion=exec("cat ".$dirtemplates.$pn[$z]." | awk 'NR==1  {print $2}'");//$text=trim($text);
			echo '<option value='.$descripcion.'>'.$descripcion.'</option>';
			}
		}
	?>
	    </select>
	  </TD>
	</form>
</TR>
<?php }else{?>
<TR>
<form name="actualiza" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="modo" value="1">

	<TD height="10" colspan="2" valign="middle">
<SPAN align=center class=subcabeceras><?php echo $TbMsg[23]." -------> ";?><input type="submit" value=<?php echo $TbMsg[16]?> name="nuevoarran"></SPAN>
	  </TD>
	</form>
</TR>
	
<?php	} ?>


<?php if ($selecdescripcion != "") {  ?>

<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
<TR>
	<TD width="300" height="10" valign="middle" colspan="">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[12];?></SPAN>
	</TD>

	<TD width="500" height="10" valign="middle">
	<input type="hidden" name="nombrefichero" id="nombrefichero" value="<?php echo $fichero;?>">
	<input type="text" name="modificadescripcion" id="modificadescripcion" size="25" value="<?php echo $selecdescripcion;?>">
	</TD>
</TR>

<TR>
	<TD width="300" height="100" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[19]?></SPAN>
	</TD>

	<TD width="500" height="100" valign="middle">
	<textarea name="parametrosmodifica" id="parametrosmodifica" cols="80" rows="15"><?php 	//Leyendo las lineas del Array parametros
		for ($k=1;$k<count($parametros);$k++) {
		echo $parametros[$k];
}?></textarea>

	</TD>
</TR>
<TR>
	<TD width="300"  valign="middle">
		<SPAN align=center class=subcabeceras></SPAN>

		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="confirmado" value="1">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="submit" value="<?php echo $TbMsg[13]?>" name="nuevoarran">

	</TD></form>

	<TD width="500"  valign="middle"><br />
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="1">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="hidden" name="modo" value="1">
		<input type="submit" value="<?php echo $TbMsg[16]?>" name="nuevoarran">
		</form>
	</TD>
</TR>
<?php }?>

</TABLE>
<?php
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################
//#### FIN SI USUARIO
}

else{
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################
//#########################################################################
// MODO ADMINISTRADOR
//#########################################################################
//#########################################################################
// LEYENDO EL DIRECTORIO
// /var/lib/tftboot/menu.lst/templates
//#########################################################################
$dirtemplates="/var/lib/tftpboot/menu.lst/templates/";
$directorio=dir($dirtemplates);
$pn= array();//pila de nombres
//bucle para llenar las pilas :P
while ($archivo = $directorio->read())
{
	//no mostrar ni "." ni ".." ni "pxe"
	if(($archivo!=".")&&($archivo!=".."))
	{
	//$description=exec("cat ".$dirtemplates.$pn[$i]." | awk 'NR==1  {print $2}'");//$text=trim($text);
	array_push($pn, $archivo);
	}
}
$directorio->close();
//ordenar las pilas segun la pila de nombres
array_multisort($pn);
	

for ($b=0;$b<count($pn);$b++)
{
	if ($pn[$b] == "pxe")
	{
		$descripcion=exec("cat ".$dirtemplates.$pn[$b]." | awk 'NR==1  {print $2}'");//$text=trim($text);
		//Aqui busco el fichero, parametros y descripcion segun llega de $_POST["modificafichero"]
		if ($descripcion == $selecdescripcion)
			{
			$fichero=$pn[$b];
			$param=$dirtemplates.$fichero;
			$parametros=file($param);
			//echo $fichero." -- Descripcion -- ".$descripcion."<br>";
			}
	}
}
//Leyendo la Descripcion de los ficheros menores que 20
for ($b=0;$b<count($pn);$b++)
{	
	$numeros=substr($pn[$b],0,2);
	if ($numeros > 19)
	break;
	{
	$descripcion=exec("cat ".$dirtemplates.$pn[$b]." | awk 'NR==1  {print $2}'");//$text=trim($text);
	//Aqui busco el fichero, parametros y descripcion segun llega de $_POST["modificafichero"]
	if ($descripcion == $selecdescripcion)
		{
		$fichero=$pn[$b];
		$param=$dirtemplates.$fichero;
		$parametros=file($param);
		//echo $fichero." -- Descripcion -- ".$descripcion."<br>";
		}

	}
}

?>
<TABLE width="850" align=CENTER border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR >
	<TD height="70" colspan="4" valign="middle">
		<p align=center><SPAN align=center class=cabeceras> <?php echo $TbMsg[4]?> </SPAN></p><p align=left></p>
        <form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
        	<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="opcioncrear" value="2">
	<?php	echo $modo;
	if ($modo==1) {
		echo '		<input type="hidden" name="modo" value="1">';
	}else{
		echo '		<input type="hidden" name="modo" value="">';
	}

	?>
   		<input type="submit" value=<?php echo $TbMsg[10]?>  name="nuevoarran">
        </form>
	</TD>
  </TR>
<TR>
<form name="actualiza" method="post" action="./boot_grub4dos_crear.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="hidden" name="modo" value="1">


	<TD height="10" colspan="3" valign="middle"><input type="hidden" name="nombreficheromodifica" id="nombreficheromodifica" value="<?php echo $fichero;?>">
    	  <SPAN align=center class=subcabeceras><?php echo $TbMsg[9]." -------------------- >";?></SPAN>

	  </TD>
	<TD height="10" valign="middle" align="right">
	  <select name="selecdescripcion" id="selecdescripcion" onChange="document.actualiza.submit()">
	    <option value"" ></option>
	    <?php
		for ($z=0;$z<count($pn);$z++)
		{
		if((substr($pn[$z],0,2)) < 20 )
			{
			$descripcion=exec("cat ".$dirtemplates.$pn[$z]." | awk 'NR==1  {print $2}'");//$text=trim($text);
			echo '<option value='.$descripcion.'>'.$descripcion.'</option>';
			}
		}
	?>   
	    </select>

	&nbsp;
	</TD>
	</form>
</TR>
<?php if ($selecdescripcion != ""){  ?>
<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
<TR>
	<TD width="600" height="10" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[21];?></SPAN>
	</TD>

	<TD width="249" height="10" valign="middle">
		&nbsp;<?php echo $fichero;?>
	</TD>

	<TD width="100" valign="middle" align="right">
	<input type="hidden" name="nombrefichero" id="nombrefichero" value="<?php echo $fichero;?>">
	<input type="text" name="modificadescripcion" id="modificadescripcion" size="25" value="<?php echo $selecdescripcion;?>">
	</TD>

	<TD width="500" valign="middle">
	<SPAN align=center class=subcabeceras><?php echo " <- ".$TbMsg[12];?></SPAN>
	</TD>
</TR>
<TR>
	<TD width="500" height="100" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[19]?></SPAN>
	</TD>

	<TD width="500" height="100" colspan="3" valign="middle">
	<textarea name="parametrosmodifica" id="parametrosmodifica" cols="95" rows="17"><?php 	//Leyendo las lineas del Array parametros
		for ($k=1;$k<count($parametros);$k++) {
		echo $parametros[$k];
		}?></textarea>
	</TD>
</TR>
<TR>
	<TD width="500"  valign="middle">
		<SPAN align=center class=subcabeceras></SPAN>

		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
		<input type="hidden" name="confirmado" value="1">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="submit" value="<?php echo $TbMsg[13]?>" name="nuevoarran">
		
	</TD></form>

	<TD width="500"  valign="middle"><br />
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="1">
		<input type="hidden" name="opcioncrear" value="2">
		<input type="hidden" name="modo" value="1">
		<input type="submit" value="<?php echo $TbMsg[16]?>" name="nuevoarran">
		</form>
	</TD>
   	<TD width="500"  valign="middle"><br />
   	</TD>
    <TD width="500"  valign="middle"><br />
   	</TD>

</TR>
<?php }?>
</TABLE>
<?php
//#########################################################################
// 			FIN MODO ADMINISTRADOR
//#########################################################################
//#### FIN SI USUARIO


//#### FIN SINO USUARIO
}
//#### FIN SINO CONFIRMADO
}
//#### FIN SI OPCIONCREAR
}
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################
?>


<?php
//##################################################################################################################################
//###########  ELIMINAR COLUMNA ARRANQUE  ##########################################################################################
//##################################################################################################################################

if ($opcioncrear == 3)
	{
	$confirmado=isset($_POST["confirmado"]) ? $_POST["confirmado"] : "";
	if ($confirmado == 1)
		{
				$eliminafichero=$_POST["eliminafichero"];
// esta funcion genera los elementos de un select(formulario html) donde aparecen los nombres de los ordenadores, según su menu pxe
function listaequipos($cmd,$eliminafichero,$seleccion)
{//Buscando idordenador de los arranque eliminafichero
global $cambia;
$cmd->texto="SELECT * FROM ordenadores where arranque='" . $eliminafichero ."' " . $seleccion; 
$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero(); 
while (!$rs->EOF)
{ 
	//$cmd->texto="UPDATE ordenadores SET arranque=unknown WHERE idordenador=60";
	//$resul=$cmd->Ejecutar();
	//echo $eliminafichero.' - '.$rs->campos["nombreordenador"].'<BR>';
	$cambia[]=$rs->campos["idordenador"];
	$rs->Siguiente();
}
$rs->Cerrar();

for ($u=0;$u<count($cambia);$u++)
{
		$nombrefich="00unknown";
		$cmd->CreaParametro("@arranque","00unknown","");
		$cmd->ParamSetValor("@arranque","00unknown");
$cmd->texto="UPDATE ordenadores SET arranque=@arranque WHERE idordenador=".$cambia[$u]; 

$rs=new Recordset; 
$rs->Comando=&$cmd; 
if (!$rs->Abrir()) echo "error";
$rs->Primero();
$resul=$cmd->Ejecutar();
$rs->Cerrar();
}
}

				
				$listadopxe=listaequipos($cmd,$eliminafichero,$seleccion);
				echo $listadopxe;
				$fichero = "/var/lib/tftpboot/menu.lst/templates/".$eliminafichero;
				unlink($fichero);

		?>
		<TABLE width="500" align=center border=1 >
		<TR><TD align="center"><br><?php if($eliminafichero != null) echo $TbMsg[8];?><br><br><SPAN align=center class=subcabeceras><?php echo substr($eliminafichero,2);?></span><br><br><br>
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="submit" value="Continuar" name="nuevoarran">
		</form>
		</TR></TD>
</TABLE>

		<?php }else{
?>
<?php
//#########################################################################
// LEYENDO EL DIRECTORIO
// /var/lib/tftboot/menu.lst/templates
//#########################################################################
$dirtemplates="/var/lib/tftpboot/menu.lst/templates/";
$directorio=dir($dirtemplates);
$pn= array();//pila de nombres
//bucle para llenar las pilas :P
while ($archivo = $directorio->read())
{
	//no mostrar ni "." ni ".." ni "pxe"
	if(($archivo!="pxe")&&($archivo!=".")&&($archivo!=".."))
	{
	array_push($pn, $archivo);
	}
}
$directorio->close();
//ordenar las pilas segun la pila de nombres
array_multisort($pn);


?>
<TABLE width="650" align=CENTER border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<form name="eliminaarranque" method="post" action="./boot_grub4dos_crear.php">
<TR align=center>
	<TD height="70" colspan="2" valign="middle">
		<SPAN align=center class=cabeceras> <?php echo $TbMsg[5]?> </SPAN>
	</TD>
  </TR>
<TR>
	<TD width="150" height="10" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[12]?></SPAN>
	</TD>

	<TD width="500" height="10" valign="middle">
	  <select name="eliminafichero" id="eliminafichero">
  	<?php
		for ($z=0;$z<count($pn);$z++)
		{
		if((substr($pn[$z],0,2)) > 19)
			{
			$description=exec("cat ".$dirtemplates.$pn[$z]." | awk 'NR==1  {print $2}'");//$text=trim($text);
			echo '<option value='.$pn[$z].'>'.$description.'</option>';
			}
		}
	?>   
      </select></TD>
</TR>

<TR>
	<TD width="150"  valign="middle">
		<SPAN align=center class=subcabeceras></SPAN>

		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="1">
		<input type="hidden" name="opcioncrear" value="3">
		<input type="submit" value="<?php echo $TbMsg[13]?>" name="nuevoarra">
		
	</TD></form>

	<TD width="500"  valign="middle"><br />
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="modo" value="1">
		<input type="submit" value="<?php echo $TbMsg[16]?>" name="nuevoarran">
		</form>
	</TD>
</TR>
</TABLE>
<?php
//##################################################################################################################################
//###########  ELIMINAR COLUMNA ARRANQUE  ##########################################################################################
//##################################################################################################################################
}}?>

</body>
</html>
