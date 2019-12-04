<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

abstract class NetbootType {

    const BIOS = "BIOS";
    const UEFI = "UEFI";

    protected $name = 'NETBOOT_TYPE';
    protected $values = array(self::BIOS, self::UEFI);

    public static $options = array(self::BIOS, self::UEFI);

    public function getValues()
    {
        return $this->values;
    }
}
