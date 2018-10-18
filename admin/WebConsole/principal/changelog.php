<?php
//**********************************************************************
// Descripción : Muestra la configuración de los clientes en engine.cfg
//**********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/ayuda_".$idioma.".php");

// Añadir versión.
$versionfile="../../doc/VERSION.txt";
if (file_exists ($versionfile))
    $version=@file_get_contents($versionfile);

$changelogfile="../../doc/CHANGELOG.es.txt";
$changelog=(file_exists ($changelogfile)) ? file_get_contents($changelogfile, TRUE) : "";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title> Administración web de aulas </title>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../estilos.css" />
    </head>

    <body>

        <div><p align=center class=cabeceras><img  border=0 nod="aulas-1" value="Sala Virtual" style="cursor:pointer" src="../images/iconos/aula.gif" >&nbsp;&nbsp;<?php echo $TbMsg["CHANGELOG_TITLE"] ?><br>
        <span id="aulas-1" class=subcabeceras><?php echo $version ?></span></p>
        </div>

        <div style="margin: 0 3em 0 3em">
        <pre>
        <?php echo $changelog; ?>
        <pre>
        </div>
    </body>
</html>

