<?php
//**********************************************************************
// Descripción : Muestra la configuración de los clientes en engine.cfg
//**********************************************************************
include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/engine_".$idioma.".php");

$cfgfile="../../client/etc/engine.cfg";
$config=(file_exists ($cfgfile)) ? file_get_contents($cfgfile, TRUE) : "No hay acceso al fichero de configuración";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title> Administración web de aulas </title>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../estilos.css" />
    </head>

    <body>

        <div><p align=center class=cabeceras><img  border=0 nod="aulas-1" value="Sala Virtual" style="cursor:pointer" src="../images/iconos/aula.gif" >&nbsp;&nbsp;<?php echo $TbMsg["ENGINE_TITULO"] ?><br>
        <span id="aulas-1" class=subcabeceras><?php echo $TbMsg["ENGINE_SUBTITULO"] ?></span></p>
        </div>

        <div style="margin: 0 3em 0 3em">
        <pre>
        <?php echo $config; ?>
        <pre>
        </div>
    </body>
</html>

