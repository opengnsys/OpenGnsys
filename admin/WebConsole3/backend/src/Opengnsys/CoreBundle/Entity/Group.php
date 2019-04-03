<?php

namespace Opengnsys\CoreBundle\Entity;

use FOS\UserBundle\Model\Group as FOSBaseGroup;
use FOS\UserBundle\Model\GroupInterface as FOSGroupInterface;

/**
 * Group
 */
class Group extends FOSBaseGroup implements FOSGroupInterface
{
    public function __construct()
    {
        parent::__construct("", array());
    }

    /**
     * Represents a string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: '';
    }
}
