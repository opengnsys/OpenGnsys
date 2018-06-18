<?php

namespace Opengnsys\ServerBundle\Entity\Enum;

class Language  {

    const ES = "Español";
    const EN = "English";
    const CAT = "Català";

    protected $name = 'LANGUAGE';
    protected $values = array(self::ES, self::EN, self::CAT);

    public static $options = array(self::ES, self::EN, self::CAT);

    public function getValues()
    {
        return $this->values;
    }
}
