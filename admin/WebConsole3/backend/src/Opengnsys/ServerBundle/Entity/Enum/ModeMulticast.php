<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

class ModeMulticast  {

    const HALF_DUPLEX = 1;
    const FULL_DUPLEX = 2;

    protected $name = 'MODE_MULTICAST';
    protected $values = array(self::HALF_DUPLEX, self::FULL_DUPLEX);

    public static $options = array(self::HALF_DUPLEX, self::FULL_DUPLEX);

    public function getValues()
    {
        return $this->values;
    }
}
