<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

class ImageType  {

    const MONOLITHIC = 1;
    const BASIC = 2;
    const INCREMENTAL = 3;

    protected $name = 'IMAGE_TYPE';
    protected $values = array(self::MONOLITHIC, self::BASIC, self::INCREMENTAL);

    public static $options = array(self::MONOLITHIC, self::BASIC, self::INCREMENTAL);

    public function getValues()
    {
        return $this->values;
    }
}
