<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

abstract class ClientStatus {

    const OFF = "off";
    const INITIALIZING = "initializing";
    const OG_LIVE = "oglive";
    const BUSY = "busy";
    const LINUX = "linux";
    const LINUX_SESSION = "linux_session";
    const MACOS = "macos";
    const WINDOWS = "windows";
    const WINDOWS_SESSION = "windows_session";

    protected $name = 'CLIENT_STATUS';

    protected $values = array(
        self::POWER_OFF,
        self::POWER_ON,
        self::CREATE_IMAGE,
        self::RUN_SCRIPT,
        self::DELETE_CACHE_IMAGE
    );

    //public static $options = array(self::POWER_OFF, self::POWER_ON, self::CREATE_IMAGE, self::RUN_SCRIPT, self::DELETE_CACHE_IMAGE, self::HARDWARE_INVENTORY, self::SOFTWARE_INVENTORY, self::PARTITION_AND_FORMAT, self::RESTART, self::RESTORE_IMAGE);

    public function getValues()
    {
        return $this->values;
    }
}
