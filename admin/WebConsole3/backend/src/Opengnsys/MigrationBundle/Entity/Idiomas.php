<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Idiomas
 */
class Idiomas
{
    /**
     * @var string|null
     */
    private $descripcion;

    /**
     * @var string|null
     */
    private $nemonico;

    /**
     * @var int
     */
    private $ididioma;


    /**
     * Set descripcion.
     *
     * @param string|null $descripcion
     *
     * @return Idiomas
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
     * Set nemonico.
     *
     * @param string|null $nemonico
     *
     * @return Idiomas
     */
    public function setNemonico($nemonico = null)
    {
        $this->nemonico = $nemonico;

        return $this;
    }

    /**
     * Get nemonico.
     *
     * @return string|null
     */
    public function getNemonico()
    {
        return $this->nemonico;
    }

    /**
     * Get ididioma.
     *
     * @return int
     */
    public function getIdidioma()
    {
        return $this->ididioma;
    }
}
