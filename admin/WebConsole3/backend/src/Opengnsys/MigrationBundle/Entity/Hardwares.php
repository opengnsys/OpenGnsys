<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Hardwares
 */
class Hardwares
{
    /**
     * @var int
     */
    private $idtipohardware = '0';

    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var int
     */
    private $idhardware;


    /**
     * Set idtipohardware.
     *
     * @param int $idtipohardware
     *
     * @return Hardwares
     */
    public function setIdtipohardware($idtipohardware)
    {
        $this->idtipohardware = $idtipohardware;

        return $this;
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

    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Hardwares
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
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Hardwares
     */
    public function setIdcentro($idcentro)
    {
        $this->idcentro = $idcentro;

        return $this;
    }

    /**
     * Get idcentro.
     *
     * @return int
     */
    public function getIdcentro()
    {
        return $this->idcentro;
    }

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Hardwares
     */
    public function setGrupoid($grupoid = null)
    {
        $this->grupoid = $grupoid;

        return $this;
    }

    /**
     * Get grupoid.
     *
     * @return int|null
     */
    public function getGrupoid()
    {
        return $this->grupoid;
    }

    /**
     * Get idhardware.
     *
     * @return int
     */
    public function getIdhardware()
    {
        return $this->idhardware;
    }
}
