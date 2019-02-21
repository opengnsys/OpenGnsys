<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Sistemasficheros
 */
class Sistemasficheros
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var string|null
     */
    private $nemonico;

    /**
     * @var int
     */
    private $codpar;

    /**
     * @var int
     */
    private $idsistemafichero;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Sistemasficheros
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
     * Set nemonico.
     *
     * @param string|null $nemonico
     *
     * @return Sistemasficheros
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
     * Set codpar.
     *
     * @param int $codpar
     *
     * @return Sistemasficheros
     */
    public function setCodpar($codpar)
    {
        $this->codpar = $codpar;

        return $this;
    }

    /**
     * Get codpar.
     *
     * @return int
     */
    public function getCodpar()
    {
        return $this->codpar;
    }

    /**
     * Get idsistemafichero.
     *
     * @return int
     */
    public function getIdsistemafichero()
    {
        return $this->idsistemafichero;
    }
}
