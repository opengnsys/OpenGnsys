<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Tiposoftwares
 */
class Tiposoftwares
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var string
     */
    private $urlimg = '';

    /**
     * @var int
     */
    private $idtiposoftware;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Tiposoftwares
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
     * Set urlimg.
     *
     * @param string $urlimg
     *
     * @return Tiposoftwares
     */
    public function setUrlimg($urlimg)
    {
        $this->urlimg = $urlimg;

        return $this;
    }

    /**
     * Get urlimg.
     *
     * @return string
     */
    public function getUrlimg()
    {
        return $this->urlimg;
    }

    /**
     * Get idtiposoftware.
     *
     * @return int
     */
    public function getIdtiposoftware()
    {
        return $this->idtiposoftware;
    }
}
