<?php
// Version 1.1.1 - Se utiliza createBootMode para crear los archivos PXE (ticket #802 #888)
// Autor: Irina Gomez - ETSII Universidad de Sevilla
// Fecha: 2019/02/12

include_once("../includes/ctrlacc.php");
include_once("../includes/CreaComando.php");
include_once("../includes/tftputils.php");

// Recogemos los parametros
$litambito=(isset($_REQUEST["litambito"])) ? $_REQUEST["litambito"] : "";
$idambito=(isset($_REQUEST["idambito"])) ? $_REQUEST["idambito"] : "";
$nombreambito=(isset($_REQUEST["nombreambito"])) ? $_REQUEST["nombreambito"] : "";
$lista=(isset($_POST['listOfItems'])) ? explode(";",$_POST['listOfItems']) : "";

// Crea objeto comando
$cmd=CreaComando($cadenaconexion);

foreach ($lista as $sublista) {
    if (! empty ($sublista)) {
        $elementos = explode("|",$sublista);
        $ip = $elementos[1];
        $optboot=$elementos[0];

        createBootMode ($cmd, $optboot, $ip, $idioma);
    }
}

header("Location: ../principal/boot.php?idambito=". $idambito ."&nombreambito=" . $nombreambito . "&litambito=" . $litambito);
exit();
