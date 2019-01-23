<?php
// ****************************************************************************************
// Aplicacion WEB: ogAdmWebCon
// autor: Irina Gomez, ETSII Universidad de Sevilla
// Fecha: 2018-10-11
// Descripción : Página de indice del manual de usuario
// ****************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/ayuda_".$idioma.".php");

// Obtenemos nombres de los temas del manual
//ruta manual usuario
$nombredir = '../userManual';
$directorio=opendir($nombredir);
//obtenemos un archivo y luego otro sucesivamente
while ($archivo = readdir($directorio))
{
    if (is_dir($archivo)) continue;
    if ($archivo == '.' || $archivo == '..') continue;
    $fichero[] = $archivo;
}

sort($fichero);

$temas = '';
foreach ($fichero as $ficheros) {
        $temas .='        <p><a href="'.$nombredir.'/'.$ficheros.'" target=miframeflotante  >'.$ficheros.'</a></P>'."\n";
}
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<head>
<title> Administración web de aulas </title>
<meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
<link rel='stylesheet' type='text/css' href='../estilos.css' />
</head>

<body class='acercade'>
<p align=center class=cabeceras><img border=0 style="cursor: pointer;" src="../images/iconos/aula.gif" >&nbsp;&nbsp;<?php echo $TbMsg["MANUAL"] ?></p>
<br>

<table width='100%' border='0'>
  <tr>
    <td width='30%'><p><img src='../images/acercade.png' alt='*' hspace='10em' vspace='10em' align='left' /></p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p><img alt='OpenGnsys' src='../images/iconos/logoopengnsys.png' /></p></td>
    <td width='61%'>
        <?php echo $temas ?>
    </td>
  </tr>

</table>

<table width='100%' height='100%' border='0'>
  <tr >
    <td align='center' >
    <?php echo '<iframe id=miframeflotante name=miframeflotante src="'.$nombredir.'/'.$fichero[0].'" width=100% height=700 frameborder=0 scrolling=no marginwidth=0 marginheight=0 align=left>Tu navegador no soporta frames!!</iframe>'; ?>
    </td>
  </tr>
</table>
