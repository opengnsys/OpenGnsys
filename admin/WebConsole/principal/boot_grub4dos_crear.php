<?php
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
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
$boton = isset ($_REQUEST["boton"]) ? $_REQUEST["boton"] : "";
$confirmado = ($boton == $TbMsg[13] && ($opcioncrear == "crear" || $opcioncrear == "modificar")) ? "1" : "";
$guarnomb = isset($_POST["nombrenuevoboot"]) ? ucfirst($_POST["nombrenuevoboot"]) : "";
$admin = isset($_POST["modo"]) ? $_POST["modo"] : "";
$selectfile = isset($_POST["selectfile"]) ? $_POST["selectfile"] : "";
$boottype = isset($_POST["boottype"]) ? $_POST["boottype"] : "";
$dirtemplates= ( $boottype === "uefi" ) ? "/var/lib/tftpboot/grub/templates/"  : "/var/lib/tftpboot/menu.lst/templates/";
$otrodirtemplates= ( $boottype === "uefi" ) ? "/var/lib/tftpboot/menu.lst/templates/" : "/var/lib/tftpboot/grub/templates/";
$descripcion = "";
$modo = "";
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
<P align=center class=cabeceras><?php echo $TbMsg[42]; ?><BR>
	<SPAN align=center class=subcabeceras> <?php echo $nombreambito; ?> </SPAN></P>
<?php
//##################################################################################################################################
//###########  NUEVO COLUMNA ARRANQUE  #############################################################################################
//##################################################################################################################################

if ($opcioncrear == "crear")
	{
	if ($confirmado == 1)
		{	
			$descripfich=preg_replace("/[^A-Za-z0-9]/", "-", $guarnomb);
			$guarnomb=preg_replace("/[^A-Za-z0-9]/", "", $descripfich);
			$action="./boot_grub4dos.php";

			if($guarnomb === "") {
				// Mensaje de error si no ha incluido descripción
				$action="./boot_grub4dos_crear.php";
				$mensaje="<br><br><SPAN align=center class=subcabeceras>".$TbMsg[14]."</span>";

			} else {
				// Nombre archivo: Si para el otro tipo de plantillas existe un fichero con igual descripción uso el nombre.
				$nombrenuevoboot=exec("grep -i -m 1 \"^##NO-TOCAR-ESTA-LINEA[[:blank:]]*$descripfich$\" $otrodirtemplates* |awk -F: '{print $1}'");
				if (isset($nombrenuevoboot) && $nombrenuevoboot != "") {
					$nombrenuevoboot=basename($nombrenuevoboot);
				} else {
					// Nombre archivo: numDescripción
					// número: a todos los números posibles le quito los ya usados y me quedo con el primero
					chdir($dirtemplates);
					$pn=array_map("principio",glob("*"));
					$todos=range(21,99);
					$ultimonumero=current(array_diff($todos,$pn));

					$nombrenuevoboot=$ultimonumero.$guarnomb;
				}

				$nuevoboot = $dirtemplates.$nombrenuevoboot;

				// Comprobamos que no exista
				if ( file_exists($nuevoboot)) {
					$mensaje=$TbMsg["ERR_DUPLICADO"]."<br><br><SPAN align=center class=subcabeceras>".$nombrenuevoboot." - '".$guarnomb."' ($boottype)</span>";
				} else {
					// Creo plantilla
					$parametrosnuevoboot=$_POST["parametrosnuevoboot"];

					$fp = fopen($nuevoboot, "w");
					$string = $TbMsg[22].$descripfich."\n".$parametrosnuevoboot;
					$write = fputs($fp, $string);
					fclose($fp);

					$mensaje=$TbMsg[6]."<br><br><SPAN align=center class=subcabeceras>".$descripfich."</span>";
				}
			}
			?>
						<form name="crearranque" method="post" action="<?php echo $action ?>">
						<input type="hidden" name="confirmado" value="">
						<input type="hidden" name="ultimonumero" value="<?php echo $ultimonumero?>">
						<input type="hidden" name="litambito" value="<?php echo $litambito?>">
						<input type="hidden" name="idambito" value="<?php echo $idambito?>">
						<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
						<input type="hidden" name="opcioncrear" value="crear">
						<input type="hidden" name="boottype" value="<?php echo $boottype ?>">
						<TABLE width="500" align=center border=1 >
						<TR><TD align="center"><br><?php echo $mensaje;?></span><br><br><br>	
						<input type="submit" value="Continuar" name="nuevoarran">
						</TD></TR>
						</TABLE>
						</form>
<?php }else{
?>

<form name="crearranque" method="post" action="./boot_grub4dos_crear.php">
<input type="hidden" name="litambito" value="<?php echo $litambito?>">
<input type="hidden" name="idambito" value="<?php echo $idambito?>">
<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
<input type="hidden" name="boottype" value="<?php echo $boottype ?>">
<input type="hidden" name="opcioncrear" value="crear">
<input type="hidden" name="modo" value="1">

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
	<TD width="150" height="10" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[12]." ($boottype)"?></SPAN>
	</TD>

	<TD width="500" height="10" valign="middle">
		<input type="text" name="nombrenuevoboot" id="textfield" size="25" value="<?php echo $guarnomb ?>">
	</TD>

</TR>
<TR>
	<TD width="150" height="100" valign="middle">

<SPAN align=center class=subcabeceras><?php echo $TbMsg[19]?><br></SPAN>
<?php
// Boton utilizar plantilla o no.
if ($boton == $TbMsg[17]) {
	echo '<input name=boton type=submit value="'.$TbMsg[18].'">';
}else{
	echo '<input name=boton type=submit value="'.$TbMsg[17].'">';
}
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

		<input type="submit" name="boton" value="<?php echo $TbMsg[13]?>">
	</TD>

<TD width="500"  valign="middle">
		<!-- Cancelar: vuelvo a página de netbootavanzado -->
		<input type="submit" value="<?php echo $TbMsg[16]?>" onclick='document.forms[0].action="./boot_grub4dos.php";'>
	</TD>
</TR>
</TABLE>
</form>
<?php
//##################################################################################################################################
//###########  NUEVO COLUMNA ARRANQUE  #############################################################################################
//##################################################################################################################################
}}?>


<?php
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################
if ($opcioncrear == "modificar")
	{
	$action="./boot_grub4dos_crear.php";
	$confirmado=isset($_POST["confirmado"]) ? $_POST["confirmado"] : "";
        // Realizamos los cambios en el fichero
	if ($confirmado == 1)
		{
				$modificadescripcion=ucfirst($_POST["modificadescripcion"]);
				$descripfich=$modificadescripcion;$descripfich=preg_replace("/[^A-Za-z0-9]/", "-", $descripfich);
				$ficherow=$dirtemplates.$_POST["nombrefichero"];//echo $ficherow."<br>";
				$parametrosmodifica=$_POST["parametrosmodifica"];

				if(empty($modificadescripcion)) {
					$mensaje=$TbMsg[14];
				}else{

				$fp = fopen($ficherow, "w");
				$string = $TbMsg[22].$descripfich."\n".$parametrosmodifica;
				$write = fputs($fp, $string);//Escribe la primera linea
				fclose($fp);

					$action="./boot_grub4dos.php";
					$mensaje=$TbMsg[7];
				}
		?>
		<TABLE width="500" align=center border=1 >
		<TR><TD align="center"><br><?php echo $mensaje;?><br><br><SPAN align=center class=subcabeceras><?php echo $modificadescripcion;?></span><br><br><br>
		<form name="crearranque" method="post" action="<?php echo $action ?>">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="hidden" name="confirmado" value="0">
		<input type="hidden" name="opcioncrear" value="modificar">
		<input type="hidden" name="modo" value="0">
		<input type="submit" value="Continuar" name="nuevoarran">
		</form>
		</TD></TR>
		</TABLE>
<?php
//#########################################################################
// MODO USUARIO
//#########################################################################
    // Mostramos las plantillas a modificar
    } else {
	$select="";
	$input="";
        $textoboton="";
        // No hay plantilla elegida
        if ($selectfile === "") {
            // LEYENDO EL DIRECTORIO
            // /var/lib/tftboot/menu.lst/templates o /var/lib/tftboot/grub/templates
            chdir($dirtemplates);
            $pn=glob("*");
            // ordenamos
            sort($pn);

            if (empty($admin)) {
                // Si el modo es usuario eliminamos las plantillas de la instalación
                unset ($pn[array_search("pxe", $pn)]);
                foreach ($pn as $key => $valor) {
                    if (strnatcmp ( $valor , "20" ) > 0) break;
                    unset($pn[$key]);

                }

                // Botón cambio de modo
                $textoboton = '         <input type="submit" value='.$TbMsg[11].'  name="nuevoarran" onclick=\'document.forms[0].modo.value=1;\' >'."\n";
            } else {
                $textoboton = '         <input type="submit" value='.$TbMsg[10].'  name="nuevoarran" onclick=\'document.forms[0].modo.value=0;\'>'."\n";
            }

            // Opciones del select
            $select = '  <select name="selectfile" id="selectfile" onChange="document.actualiza.submit()">'."\n".
                      '              <option value=""></option>'."\n";
            foreach ($pn as $valor) {
                $descripcion=exec("awk 'NR==1 {print $2}' ".$dirtemplates.$valor);
                $select.= '              <option value='.$valor.'>'.$descripcion.'</option>'."\n";
            }
            $select.= '          </select>'."\n";

        // Hay una plantilla seleccionada para modificar
        } else {
            $file=$dirtemplates.$selectfile;
            $descripcion=exec("awk 'NR==1 {print $2}' ".$dirtemplates.$selectfile);
            $parametros=file_get_contents ($file);
            // Elimino cabecera anterior
            $parametros=preg_replace ("/$TbMsg[22].*\n/",'', $parametros);

            // Campos de formulario especificos de esta opción
            $input .= '<input type="hidden" name="nombrefichero" id="nombrefichero" value="'.$selectfile.'">'."\n".
                      '<input type="hidden" name="confirmado" value="1" >'."\n";
        }

// Parte del formulario comun
?>
<form name="actualiza" method="post" action="<?php echo $action ?>">
    <input type="hidden" name="litambito" value="<?php echo $litambito ?>">
    <input type="hidden" name="idambito" value="<?php echo $idambito ?>">
    <input type="hidden" name="nombreambito" value="<?php echo $nombreambito ?>">
    <input type="hidden" name="opcioncrear" value="modificar">
    <input type="hidden" name="boottype" value="<?php echo $boottype ?>">
    <input type="hidden" name="modo" value="<?php echo $modo ?>">
    <?php echo  $input; 

    // Cabecera de la tabla ?>
    <table width="850" align="center" border="1" cellPadding="1" cellSpacing="1" class="tabla_datos" >
      <tr>
        <td height="70" colspan="3" valign="middle"><p align=center class=cabeceras><?php echo $TbMsg[4] ?></p>
        <?php echo $textoboton ?>
      </tr>
    
      <?php  // Lista de selección de plantillas
        if ($selectfile === "") { ?>

      <tr>
        <td height="10" colspan="2" valign="middle"><span align=center class=subcabeceras><?php echo $TbMsg[9]." (".$boottype.")" ?></span></td>
        <td height="10" valign="middle" align="right">
        <?php echo $select ?>
        </td>
      </tr>
    
      <?php // Formulario con datos de la plantilla a cambiar
        } else { ?>

      <tr>
        <td height="10" valign="middle">
            <SPAN align=center class=subcabeceras><?php echo $TbMsg[21]." (".$boottype.")" ?></SPAN>
        </td>
        <td width="249" height="10" valign="middle"><?php echo $selectfile ?></td>
        <td width="100" valign="middle" align="right">
            <span align=center class=subcabeceras><?php echo $TbMsg[12] ?></span>
            <input type="text" name="modificadescripcion" id="modificadescripcion" size="25" value="<?php echo $descripcion ?>">
        </td>
      </tr>
      <tr>
        <td width="500" height="100" valign="middle"> <span align=center class=subcabeceras><?php echo $TbMsg[19] ?></span></td>
        <td width="500" height="100" colspan="2" valign="middle">
            <textarea name="parametrosmodifica" id="parametrosmodifica" cols="95" rows="17"><?php echo $parametros ?></textarea>
        </td>
      </tr>
      <tr>
        <td width="500"  valign="middle"><input type="submit" value="<?php echo $TbMsg[13] ?>" name="nuevoarran"></td>
        <td width="500" colspan="2"  valign="middle"><input type="submit" value="<?php echo $TbMsg[16] ?>" name="nuevoarran"  onclick='document.forms[0].action="./boot_grub4dos.php";'>
      <tr>
    
       <?php  }
        // Final pagina ?>
    </table>
</form>

    <?php
    }
//##################################################################################################################################
//###########  MODIFICAR COLUMNA ARRANQUE  #########################################################################################
//##################################################################################################################################
}


//##################################################################################################################################
//###########  ELIMINAR COLUMNA ARRANQUE  ##########################################################################################
//##################################################################################################################################

if ($opcioncrear == "eliminar" )
	{
	$confirmado=isset($_POST["confirmado"]) ? $_POST["confirmado"] : "";
	if ($confirmado == 1)
		{
			$eliminafichero=$_POST["eliminafichero"];
			$resul=actualizaequipos($cmd,$eliminafichero);
			$fichero = $dirtemplates.$eliminafichero;
			unlink($fichero);

		?>
		<TABLE width="500" align=center border=1 >
		<TR><TD align="center"><br><?php if($eliminafichero != null) echo $TbMsg[8];?><br><br><SPAN align=center class=subcabeceras><?php echo substr($eliminafichero,2)." (".$boottype.")"; ?></span><br><br><br>
		<form name="crearranque" method="post" action="./boot_grub4dos.php">
		<input type="hidden" name="litambito" value="<?php echo $litambito?>">
		<input type="hidden" name="idambito" value="<?php echo $idambito?>">
		<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
		<input type="submit" value="Continuar" name="nuevoarran">
		</form>
		</TD></TR>
</TABLE>

		<?php }else{
?>
<?php
//#########################################################################
// LEYENDO EL DIRECTORIO
// /var/lib/tftboot/menu.lst/templates o /var/lib/tftpboot/grub/templates/
//#########################################################################
//$dirtemplates= "/var/lib/tftpboot/menu.lst/templates/";
chdir($dirtemplates);

$pn=glob("*");//pila de nombres
// No mostramos archivo pxe
unset($pn[array_search("pxe", $pn)]);
//ordenar las pilas segun la pila de nombres
sort($pn);
?>

<form name="eliminaarranque" method="post" action="./boot_grub4dos_crear.php">
<input type="hidden" name="litambito" value="<?php echo $litambito?>">
<input type="hidden" name="idambito" value="<?php echo $idambito?>">
<input type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
<input type="hidden" name="confirmado" value="1">
<input type="hidden" name="opcioncrear" value="eliminar">
<input type="hidden" name="boottype" value="<?php echo $boottype ?>">
<TABLE width="650" align=CENTER border=1 cellPadding=1 cellSpacing=1 class=tabla_datos >
<TR align=center>
	<TD height="70" colspan="2" valign="middle">
		<SPAN align=center class=cabeceras> <?php echo $TbMsg[5]?> </SPAN>
	</TD>
  </TR>
<TR>
	<TD width="150" height="10" valign="middle">
		<SPAN align=center class=subcabeceras><?php echo $TbMsg[12]." (".$boottype.")" ?></SPAN>
	</TD>

	<TD width="500" height="10" valign="middle">
	  <select name="eliminafichero" id="eliminafichero">
  	<?php
		for ($z=0;$z<count($pn);$z++)
		{
		// Sólo se pueden borrar plantillas que empiecen >19
		if((substr($pn[$z],0,2)) > 19)
			{
			$description=exec("awk 'NR==1 {print $2}' ".$dirtemplates.$pn[$z]);
			echo '<option value='.$pn[$z].'>'.$description.'</option>';
			}
		}
	?>   
      </select>
	</TD>
</TR>

<TR>
	<TD width="150"  valign="middle">
		<input type="submit" value="<?php echo $TbMsg[13]?>" name="nuevoarra">
		
	</TD>

	<TD width="500"  valign="middle">
		<input type="submit" value="<?php echo $TbMsg[16]?>" name="nuevoarran" onclick='document.forms[0].action="./boot_grub4dos.php";'>
	</TD>
</TR>
</TABLE>
</form>
<?php
//##################################################################################################################################
//###########  ELIMINAR COLUMNA ARRANQUE  ##########################################################################################
//##################################################################################################################################
}}?>

</body>
</html>

<?php
// Los equipos que tienen asignada la plantilla a eliminar se actualizan con el valor desconocido.
// cmd: manejador de la base de datos
// eliminafichero: plantilla a eliminar
function actualizaequipos($cmd,$eliminafichero) {
	$nombrefich="00unknown";
	$cmd->texto="UPDATE ordenadores SET arranque='".$nombrefich."' WHERE arranque='".$eliminafichero."';";
	$resul=$cmd->Ejecutar();
	return $resul;
}

// Extrae los dos primeros caracteres de una cadena
function principio($valor) {
    return substr($valor,0,2);
}
?>

