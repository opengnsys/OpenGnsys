<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

abstract class CommandType {

    const RUN_SCRIPT = "RUN_SCRIPT"; // Ejecutar script
    const POWER_OFF = "POWER_OFF"; // Apagar
    const POWER_ON = "POWER_ON"; // Encender
    const CREATE_IMAGE = "CREATE_IMAGE"; // Crear imagen
    const DELETE_CACHE_IMAGE = "DELETE_CACHE_IMAGE"; // Borrar imagen de caché
    const LOG_IN = "LOG_IN"; // Iniciar sesión
    const HARDWARE_INVENTORY = "HARDWARE_INVENTORY"; // Inventario hardware
    const SOFTWARE_INVENTORY = "SOFTWARE_INVENTORY"; // Inventario software
    const PARTITION_AND_FORMAT = "PARTITION_AND_FORMAT"; // Particionar y formatear
    const RESTART = "RESTART"; // Reiniciar
    const RESTORE_IMAGE = "RESTORE_IMAGE"; // Restaurar imagen

    protected $name = 'COMMAND';
    protected $values = array(self::POWER_OFF, self::POWER_ON, self::CREATE_IMAGE, self::RUN_SCRIPT, self::DELETE_CACHE_IMAGE, self::HARDWARE_INVENTORY, self::SOFTWARE_INVENTORY, self::PARTITION_AND_FORMAT, self::RESTART, self::RESTORE_IMAGE);

    public static $options = array(self::POWER_OFF, self::POWER_ON, self::CREATE_IMAGE, self::RUN_SCRIPT, self::DELETE_CACHE_IMAGE, self::HARDWARE_INVENTORY, self::SOFTWARE_INVENTORY, self::PARTITION_AND_FORMAT, self::RESTART, self::RESTORE_IMAGE);

    public function getValues()
    {
        return $this->values;
    }
}
