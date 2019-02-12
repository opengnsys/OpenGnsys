<?php
// Version 1.1.1 - Se utiliza el script setclientmode para crear los archivos PXE (ticket #802)
// Autor: Irina Gomez - ETSII Universidad de Sevilla
// Fecha: 2019/02/12

include_once("../includes/ctrlacc.php");

// Datos para el acceso a mysql
$strcn=explode(";",$cadenaconexion);
$file=tempnam("/tmp",".server.cnf.");

$lista = explode(";",$_POST['listOfItems']);
foreach ($lista as $sublista) {
    if (! empty ($sublista)) {
        // Creo fichero con datos para mysql
        $gestor=fopen($file, "w");
        fwrite($gestor, "USUARIO=".$strcn[1]."\nPASSWORD=".$strcn[2]."\n");
        fwrite($gestor, "datasource=".$strcn[0]."\nCATALOG=".$strcn[3]);
        fclose($gestor);

        $elementos = explode("|",$sublista);
        $hostname=$elementos[1];
        $optboot=$elementos[0];

        // Llamamos al script setclientmode
        shell_exec("/opt/opengnsys/bin/setclientmode $optboot $hostname 1 $file");
	unlink($file);
    }
}
header("Location: ../principal/boot.php?idambito=". $_GET['idaula'] ."&nombreambito=" . $_GET['nombreambito'] . "&litambito=" . $_GET['litambito']);
exit();
