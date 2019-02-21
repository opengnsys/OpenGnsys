<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Nombresos
 */
class Nombresos
{
    /**
     * @var string
     */
    private $nombreso;

    /**
     * @var int|null
     */
    private $idtiposo = '0';

    /**
     * @var int
     */
    private $idnombreso;


    /**
     * Set nombreso.
     *
     * @param string $nombreso
     *
     * @return Nombresos
     */
    public function setNombreso($nombreso)
    {
        $this->nombreso = $nombreso;

        return $this;
    }

    /**
     * Get nombreso.
     *
     * @return string
     */
    public function getNombreso()
    {
        return $this->nombreso;
    }

    /**
     * Set idtiposo.
     *
     * @param int|null $idtiposo
     *
     * @return Nombresos
     */
    public function setIdtiposo($idtiposo = null)
    {
        $this->idtiposo = $idtiposo;

        return $this;
    }

    /**
     * Get idtiposo.
     *
     * @return int|null
     */
    public function getIdtiposo()
    {
        return $this->idtiposo;
    }

    /**
     * Get idnombreso.
     *
     * @return int
     */
    public function getIdnombreso()
    {
        return $this->idnombreso;
    }
}
