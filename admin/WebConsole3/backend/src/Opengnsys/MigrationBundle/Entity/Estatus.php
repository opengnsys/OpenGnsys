<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Estatus
 */
class Estatus
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int
     */
    private $idestatus;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Estatus
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
     * Get idestatus.
     *
     * @return int
     */
    public function getIdestatus()
    {
        return $this->idestatus;
    }
}
