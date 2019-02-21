<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Urlimagesitems
 */
class Urlimagesitems
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int
     */
    private $idurlimagesitems;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Urlimagesitems
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Get idurlimagesitems.
     *
     * @return int
     */
    public function getIdurlimagesitems()
    {
        return $this->idurlimagesitems;
    }
}
