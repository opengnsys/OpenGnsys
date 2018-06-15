<?php
// ListarOrigenesMaster.php: Devuelve las particiones e imágenes en cache en el equipo master
//    disponibles para CloneRemotePartition

include_once("../../../includes/ctrlacc.php");
include_once("../../../clases/AdoPhp.php");
include_once("../../../includes/CreaComando.php");

// Obtener información de la petición
$ip = $_GET['ip'];

$options  = '<select name="source"> ';
$warnings = '<ul>';

$cmd = CreaComando($cadenaconexion);
$rs = new Recordset;

// Primera consulta: particiones del MASTER potencialmente clonables.
$cmd->texto = 'SELECT ordenadores_particiones.numdisk as DISK,
                      ordenadores_particiones.numpar as PART,
                      nombresos.nombreso as OS
               FROM ordenadores_particiones
               INNER JOIN tipospar ON tipospar.codpar = ordenadores_particiones.codpar
               INNER JOIN nombresos ON ordenadores_particiones.idnombreso = nombresos.idnombreso
               INNER JOIN ordenadores ON ordenadores_particiones.idordenador = ordenadores.idordenador
               WHERE ordenadores.ip = "'.$ip.'"
               AND tipospar.clonable > 0
               AND ordenadores_particiones.idnombreso > 0
               ORDER BY ordenadores_particiones.numdisk, ordenadores_particiones.numpar';
$rs->Comando = &$cmd;

if ($rs->Abrir()) {
    $cantRegistros = $rs->numeroderegistros;
    if ($cantRegistros>0) {
        $rs->Primero();
        while (!$rs->EOF) {
            $options .= '<OPTION value=" '.$rs->campos["DISK"].' '.$rs->campos["PART"].'">';
            $options .= 'DISK '.$rs->campos["DISK"].',PART '.$rs->campos["PART"].': '.$rs->campos["OS"];
            $options .= '</OPTION>';
            $rs->Siguiente();
        }
    } else {
        $warnings .= '<li>No hay particiones clonables.</li>';
    }
    $rs->Cerrar();
}

// Segunda consulta: imágenes en la caché del MASTER
$cmd->texto = 'SELECT cache FROM ordenadores_particiones
               WHERE codpar = 202
               AND idordenador = (SELECT idordenador
                                  FROM ordenadores
                                  WHERE ip = "'.$ip.'")';
$rs->Comando = &$cmd;

if ($rs->Abrir()) {
    $cantRegistros = $rs->numeroderegistros;
    if ($cantRegistros>0) {
        $rs->Primero();
        while (!$rs->EOF) {
            $files = explode(",", $rs->campos["cache"]);
            foreach ($files as $file) {
                if (preg_match("/img$/", $file)) {
                    $imgname = rtrim($file, ".img");
                    $options .= '<OPTION value=" CACHE /'.ltrim($imgname).'"';
                    $options .= '>';
                    $options .= 'IMG-CACHE: '.ltrim($imgname).'</OPTION>';
                }
            }
            $rs->Siguiente();
        }
    } else {
        $warnings .= '<li>No hay imágenes en la caché.</li>';
    }
    $rs->Cerrar();
}

//Tercera consulta: imágenes del REPO que el MASTER se encargara de enviar
$cmd->texto = 'SELECT *, repositorios.ip as iprepositorio
               FROM imagenes
               INNER JOIN repositorios
               ON repositorios.idrepositorio = imagenes.idrepositorio
               WHERE repositorios.idrepositorio = (SELECT idrepositorio
                                                   FROM ordenadores
                                                   WHERE ordenadores.ip = "'.$ip.'")
               ORDER BY imagenes.descripcion';

$rs->Comando = &$cmd;

if ($rs->Abrir()) {
    $cantRegistros = $rs->numeroderegistros;
    if ($cantRegistros>0) {
        $rs->Primero();
        while (!$rs->EOF) {
            $options .= '<OPTION value=" REPO /'.$rs->campos["nombreca"].'">';
            $options .= 'IMG-REPO: '.$rs->campos["descripcion"];
            $options .= '</OPTION>';
            $rs->Siguiente();
        }
    } else {
        $warnings .= '<li>No hay imágenes del repositorio.</li>';
    }
    $rs->Cerrar();
}

$options  .= '</select>';
$warnings .= '</ul>';

// Costruir respuesta
$ajaxResponse = $options . $warnings;

// Devolver respuesta AJAX
echo $ajaxResponse;
