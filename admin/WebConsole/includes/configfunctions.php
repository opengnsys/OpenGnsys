<?php
// Fichero de configuración JSON.
define("ENGINEJSON", __DIR__ . "/../../client/etc/engine.json");

/**
 * @function getPartitionData
 * @brief Busca en la configuración JSON los datos de partición para el código hexadecimal correspondiente
 * @param object $json  datos JSON de configuración
 * @param string $code  código hexadecimal de partición
 * @return array        tipo de partición (string) e indicador de clonable (bool)
 * @date 2018-17-05
 */
function getPartitionData($json, $code) {
    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (isset($tab->partitions)) {
                foreach ($tab->partitions as $par) {
                    if (hexdec($par->id) == $code) {
                        return [$par->type, $par->clonable];
                    }
                }
            }
        }
    }
    return [$code, true];
}

/**
 * @function getParttableData
 * @brief Busca en la configuración JSON los datos de tabla de particiones para el código correspondiente.
 * @param object $json  datos JSON de configuración
 * @param string $code  código de tabla de particiones
 * @return string       tipo de tabla de particiones
 * @date 2018-17-05
 */
function getParttableData($json, $code) {
    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if ($tab->id == $code) {
                return $tab->type;
            }
        }
    }
    return "";
}

/**
 * @function htmlSelectPartitions
 * @brief Devuelve la cláusula <select> de HTML con datos de todas las particiones válidas para una tabla determinada.
 * @param object $json       datos JSON de configuración
 * @param string $type       tipo de partición seleccionada por defecto
 * @param string $name       nombre del elemento HTML
 * @param integer $width     ancho del elemento
 * @param string $eventChg   evento JavaScript cuando cambia la selección
 * @param string $class      clase del elemento
 * @param string $partTable  tipo de tabla de particiones
 * @return string            código HTML
 * @date 2018-17-23
 */
function htmlSelectPartitions($json, $type, $name="", $width, $eventChg="", $class="", $partTable) {
    if (!empty($eventhg))	$eventChg='onchange="'.$eventChg.'(this);"';
    if (empty($class))	$class='formulariodatos';
    if (empty($name))	$name='id';

    /** @var string $html */
    $html ='<select '.$eventChg.' class="'.$class.'" name="'.$name.'" style="width: '.$width.'px">'.chr(13);
	$html.='    <option value="0"></option>'.chr(13);

    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (isset($partTable) and $tab->type != $partTable) {
                continue;
            }
            if (isset($tab->partitions)) {
                foreach ($tab->partitions as $par) {
                    $html.='    <option value="'.$par->id.'"';
                    if ($par->type == $type) {
                        $html.=' selected';
                    }
                    $html.='>'.$par->type.'</option>'.chr(13);
                }
            }
        }
    }
    $html.='</select>'.chr(13);
    return($html);
}
