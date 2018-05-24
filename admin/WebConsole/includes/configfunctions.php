<?php
/**
 * @license GPLv3+
 * @author Ramón M. Gómez <ramongomez@us.es>
 */

// JSON configuration file path
define("ENGINEJSON", __DIR__ . "/../../client/etc/engine.json");

/**
 * @param string $code Partition code, in hexadecimal
 * @return array Partition data (type and clonable indicator)
 */
function getPartitionData($code) {
    /** @var object $json JSON configuration data */
    $json=json_decode(file_get_contents(ENGINEJSON));

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
 * @param string $code Partition code, in hexadecimal
 * @return bool True, if partition is marked as clonable
 */
function isClonable($code) {
    /** @var object $json JSON configuration data */
    $json=json_decode(file_get_contents(ENGINEJSON));

    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (isset($tab->partitions)) {
                foreach ($tab->partitions as $par) {
                    if (hexdec($par->id) == $code) {
                        return $par->clonable;
                    }
                }
            }
        }
    }
    return false;
}

/**
 * @param int $code Partition table code
 * @return string partition table type
 */
function getParttableData($code) {
    /** @var object $json JSON configuration data */
    $json=json_decode(file_get_contents(ENGINEJSON));

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
 * @param string $partTable Partition table type
 * @param string $type Partition type selected by default
 * @param string $exclude Partition type to exclude
 * @return string HTML <select> clause
 */
function htmlOptionPartitions($partTable="MSDOS", $type="", $exclude="") {
    /** @var object $json JSON configuration data */
    $json=json_decode(file_get_contents(ENGINEJSON));
    /** @var string $html HTML code */
    $html='';

    if (isset($json->partitiontables)) {
        foreach ($json->partitiontables as $tab) {
            if (isset($partTable) and $tab->type != $partTable) {
                continue;
            }
            if (isset($tab->partitions)) {
                foreach ($tab->partitions as $par) {
                    if ($par->type != $exclude) {
                        $html.='    <option value="'.$par->id.'"';
                        if ($par->type == $type) {
                            $html.=' selected';
                        }
                        $html.='>'.$par->type.'</option>'."\n";
                    }
                }
            }
        }
    }
    return($html);
}
