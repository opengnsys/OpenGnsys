<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Iconos
 */
class Iconos
{
    /**
     * @var string|null
     */
    private $urlicono;

    /**
     * @var int|null
     */
    private $idtipoicono;

    /**
     * @var string|null
     */
    private $descripcion;

    /**
     * @var int
     */
    private $idicono;


    /**
     * Set urlicono.
     *
     * @param string|null $urlicono
     *
     * @return Iconos
     */
    public function setUrlicono($urlicono = null)
    {
        $this->urlicono = $urlicono;

        return $this;
    }

    /**
     * Get urlicono.
     *
     * @return string|null
     */
    public function getUrlicono()
    {
        return $this->urlicono;
    }

    /**
     * Set idtipoicono.
     *
     * @param int|null $idtipoicono
     *
     * @return Iconos
     */
    public function setIdtipoicono($idtipoicono = null)
    {
        $this->idtipoicono = $idtipoicono;

        return $this;
    }

    /**
     * Get idtipoicono.
     *
     * @return int|null
     */
    public function getIdtipoicono()
    {
        return $this->idtipoicono;
    }

    /**
     * Set descripcion.
     *
     * @param string|null $descripcion
     *
     * @return Iconos
     */
    public function setDescripcion($descripcion = null)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string|null
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Get idicono.
     *
     * @return int
     */
    public function getIdicono()
    {
        return $this->idicono;
    }
}
