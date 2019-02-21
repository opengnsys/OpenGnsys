<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Tipohardwares
 */
class Tipohardwares
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
     * @var string
     */
    private $nemonico;

    /**
     * @var int
     */
    private $idtipohardware;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Tipohardwares
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
     * @return Tipohardwares
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
     * Set nemonico.
     *
     * @param string $nemonico
     *
     * @return Tipohardwares
     */
    public function setNemonico($nemonico)
    {
        $this->nemonico = $nemonico;

        return $this;
    }

    /**
     * Get nemonico.
     *
     * @return string
     */
    public function getNemonico()
    {
        return $this->nemonico;
    }

    /**
     * Get idtipohardware.
     *
     * @return int
     */
    public function getIdtipohardware()
    {
        return $this->idtipohardware;
    }
}
