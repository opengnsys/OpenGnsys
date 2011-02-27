<?php
//importando las librerias XAJAX
require ("/opt/opengnsys/www/xajax/xajax_core/xajax.inc.php");
$xajax = new xajax("xajax.server.php");
//asociamos la función creada en index.server.php al objeto XAJAX
$xajax->registerFunction("ListarParticionesXip");
?>