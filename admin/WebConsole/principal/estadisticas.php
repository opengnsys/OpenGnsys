<?php
/*
 *
 */

include_once(__DIR__ . "/../includes/ctrlacc.php");
include_once(__DIR__ . "/../includes/CreaComando.php");
include_once(__DIR__ . "/../clases/AdoPhp.php");
include_once(__DIR__ . "/../idiomas/php/$idioma/estadisticas_$idioma.php");

$cmd=CreaComando($cadenaconexion);
if (!$cmd) {
    header('Location: '.$pagerror.'?herror=2'); // Database connection error
}
// Retrieving statistics from the database.
$data = get_statistics($cmd, $idcentro);

?>
<html lang="es">
<head>
    <title>Administración web de aulas</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="../estilos.css">
</head>
<body>
<div align="center" class="cabeceras"><?php echo $TbMsg['TITLE_STATS'] ?></div>
<?php if ($data) { ?>
    <div align="center" class="subcabeceras"><?php echo $data["ou"] ?></div>
    <p>
    <table align="center" class="tabla_listados_sin">
        <?php
        print_data_row($TbMsg['LABEL_LABS'], $data['labs']);
        if ($data['labs'] > 0) {
            print_data_row($TbMsg['LABEL_HASPROF'], $data['hasprof'], $data['labs']);
            print_data_row($TbMsg['LABEL_REMOTELAB'], $data['remotelab'], $data['labs']);
            print_data_row($TbMsg['LABEL_CLIENTS'], $data['clients']);
            print_data_row($TbMsg['LABEL_HASCONFIG'], $data['hasconfig'], $data['clients']);
            print_data_row($TbMsg['LABEL_HASREPO'], $data['hasrepo'], $data['clients']);
            print_data_row($TbMsg['LABEL_HASHARD'], $data['hashard'], $data['clients']);
            print_data_row($TbMsg['LABEL_HASSERIAL'], $data['hasserial'], $data['clients']);
        }
        print_data_row($TbMsg['LABEL_REPOS'], $data['repos']);
        if ($data['repos'] > 0) {
            print_data_row($TbMsg['LABEL_IMAGES'], $data['images']);
            if ($data['images'] > 0) {
                print_data_row($TbMsg['LABEL_MONOIMG'], $data['monoimg'], $data['images']);
                print_data_row($TbMsg['LABEL_HASSOFT'], $data['hassoft'], $data['images']);
                print_data_row($TbMsg['LABEL_REMOTEIMG'], $data['remoteimg'], $data['images']);
                print_data_row($TbMsg['LABEL_OSES'], $data['oses']);
            }
        }
        print_data_row($TbMsg['LABEL_MENUS'], $data['menus']);
        print_data_row($TbMsg['LABEL_PROCS'], $data['procs']);
        print_data_row($TbMsg['LABEL_TASKS'], $data['tasks']);
        ?>
    </table>
<?php } else {?>
    <p align="center"><?php echo $TbMsg['MSG_UNAVAILABLE'] ?></p>
<?php } ?>
<div align="center" class="pie"><a href="javascript:history.go(-1)">Volver</a></div>
</body>
</html>
<?php
/**
 * @function get_statistics
 * @param $cmd
 * @param $id
 * @return array
 */
function get_statistics($cmd, $id) {
    $rs=new Recordset;
    $cmd->texto = <<<EOT
SELECT *
  FROM (SELECT nombrecentro AS ou,
               COUNT(DISTINCT idaula) AS labs,
               COUNT(DISTINCT IF(idordprofesor > 0, idaula, NULL)) AS hasprof,
               COUNT(DISTINCT IF(inremotepc = 1, idaula, NULL)) AS remotelab,
               COUNT(idordenador) AS clients,
               COUNT(numserie) AS hasconfig,
               COUNT(IF(idrepositorio > 0, 1, NULL)) AS hasrepo,
               COUNT(IF(idperfilhard > 0, 1, NULL)) AS hashard,
               COUNT(IF(numserie = '', NULL, 1)) AS hasserial
          FROM centros
          LEFT JOIN aulas USING(idcentro)
          LEFT JOIN ordenadores USING(idaula)
         WHERE idcentro = $id) AS t1,
       (SELECT COUNT(*) AS repos
          FROM repositorios
         WHERE idcentro = $id) AS t2,
       (SELECT COUNT(*) AS images,
               COUNT(IF(tipo = 1, 1, NULL)) AS monoimg,
               COUNT(IF(idperfilsoft > 0, 1, NULL)) AS hassoft,
               COUNT(IF(inremotepc = 1, 1, NULL)) AS remoteimg,
               COUNT(DISTINCT idnombreso) AS oses
          FROM imagenes
          LEFT JOIN perfilessoft USING(idperfilsoft)
          LEFT JOIN nombresos USING(idnombreso)
         WHERE imagenes.idcentro = $id) AS t3,
       (SELECT COUNT(*) AS menus
          FROM menus
         WHERE idcentro = $id) AS t4,
       (SELECT COUNT(*) as procs
          FROM procedimientos
         WHERE idcentro = $id) AS t5,
       (SELECT COUNT(*) as tasks
          FROM tareas 
         WHERE idcentro = $id) AS t6;
EOT;
    $rs->Comando=&$cmd;
    if (!$rs->Abrir()) {
        return(null); // Error opening the record set
    }
    $rs->Primero();
    $data = $rs->campos;
    $rs->Cerrar();
    return($data);
}

/**
 * @param string $label
 * @param int $value
 * @param int $total
 */
function print_data_row($label, $value, $total=0) {
    echo "\t\t<tr><th>&nbsp;$label:&nbsp;</th><td>&nbsp;$value" .
        ($total>0 ? " (".intval($value*100/$total)."%)" : "") . "&nbsp;</td></tr>\n";
}
