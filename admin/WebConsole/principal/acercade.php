<?php
//********************************************************************
// Descripción : 
//              Pagina de informacion sobre el proyecto OpenGnsys
//********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/acercade_".$idioma.".php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../estilos.css" />
</head>

<body class="acercade">

<img alt="*" src="../images/acercade.png" align="left" hspace="10em" vspace="10em" />

<h1><img alt="OpenGnsys" src="../images/iconos/logoopengnsys.png" /></h1>

<p>
<?php
// Añadir versión.
$data = json_decode(@file_get_contents(__DIR__ . '/../../doc/VERSION.json'));
if (empty($data->project)) {
    echo "OpenGnsys";
} else {
    echo @$data->project.' '
        .@$data->version.' '
        .(isset($data->codename) ? '(<a href="'.@$data->definition.'" target="_blank">'.$data->codename.'</a>) ' : '')
        .@$data->release;
}
?>
</p>
<?php
// Añadir CHANGELOG.
$buschangelog=exec('ls ../../doc | grep CHANGELOG*', $nombrechange);
$changelogfile="../../doc/".$buschangelog;
?>
<?php
// Añadir Manual.
$usermanual="../../doc/userManual";
$destdir="../api/userManual";
if (file_exists ($usermanual)){
// Copiamos el directorio userManual
system("rm -fr $destdir; cp -a $usermanual $destdir");
// Creamos el Inicio del Manual
// Añadimos instrucciones
$ficheroinicio="$destdir/Inicio.php";
system("touch $ficheroinicio");

$crearficheroinicio=fopen($ficheroinicio,"w");
fwrite($crearficheroinicio,"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
<link rel='stylesheet' type='text/css' href='../estilos.css' />
</head>

<body class='acercade'>
<table width='100%' border='0'>
  <tr>
    <td width='30%'><p><img src='../../images/acercade.png' alt='*' hspace='10em' vspace='10em' align='left' /></p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p><img alt='OpenGnsys' src='../../images/iconos/logoopengnsys.png' /></p></td>
    <td width='61%'>
    
<p>



<?php
\$directorio = opendir('.'); //ruta actual
while (\$archivo = readdir(\$directorio)) //obtenemos un archivo y luego otro sucesivamente
{
    if (is_dir(\$archivo))//verificamos si es o no un directorio
    {
		if (\$archivo == '.' || \$archivo == '..')
		{}else{
			echo '['.\$archivo . ']<br />'; //de ser un directorio lo envolvemos entre corchetes
		}
    }
    else
    {
		if (\$archivo == 'Inicio.php' || \$archivo == '.' || \$archivo == '..')
		{}else{
			\$fichero[] = \$archivo;
			}
    }
}
sort(\$fichero);
foreach (\$fichero as \$ficheros) { 
	echo '<P><a href='.\$ficheros.' target=miframeflotante  >'.\$ficheros.'</a></P>';
}

?>

    </td>
  </tr>

</table>	

<table width='100%' height='100%' border='0'>
  <tr >
    <td align='center' >
    <?php echo '<iframe id=miframeflotante name=miframeflotante src='.\$fichero[0].' width=100% height=700 frameborder=0 scrolling=no marginwidth=0 marginheight=0 align=left>Tu navegador no soporta frames!!</iframe>';
	?>    
    </td>
  </tr>
</table>
");
fclose($crearficheroinicio);


}
?>



<p><strong><?php echo $TbMsg["TITLE"] ?></strong></p>

<p><?php echo $TbMsg["DESCRIPTION"] ?> </p>

<p><?php echo $TbMsg["LICENSE"] ?> <a href="https://www.gnu.org/licenses/gpl.html"  target="_blank" ><img alt="GPL v3"  src="../images/gplv3-88x31.png" height="20em" /></a></p>

</body>
</html>
