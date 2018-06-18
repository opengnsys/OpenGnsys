<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

class ModeP2P  {

    const PEER = "PEER";
    const LEECHER = "LEECHER";
    const SEEDER = "SEEDER";

    protected $name = 'MODE_P2P';
    protected $values = array(self::PEER, self::LEECHER, self::SEEDER);

    public static $options = array(self::PEER, self::LEECHER, self::SEEDER);

    public function getValues()
    {
        return $this->values;
    }
}
