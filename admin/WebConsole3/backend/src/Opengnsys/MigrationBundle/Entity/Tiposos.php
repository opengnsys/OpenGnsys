<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Tiposos
 */
class Tiposos
{
    /**
     * @var string
     */
    private $tiposo;

    /**
     * @var int
     */
    private $idplataforma;

    /**
     * @var int
     */
    private $idtiposo;


    /**
     * Set tiposo.
     *
     * @param string $tiposo
     *
     * @return Tiposos
     */
    public function setTiposo($tiposo)
    {
        $this->tiposo = $tiposo;

        return $this;
    }

    /**
     * Get tiposo.
     *
     * @return string
     */
    public function getTiposo()
    {
        return $this->tiposo;
    }

    /**
     * Set idplataforma.
     *
     * @param int $idplataforma
     *
     * @return Tiposos
     */
    public function setIdplataforma($idplataforma)
    {
        $this->idplataforma = $idplataforma;

        return $this;
    }

    /**
     * Get idplataforma.
     *
     * @return int
     */
    public function getIdplataforma()
    {
        return $this->idplataforma;
    }

    /**
     * Get idtiposo.
     *
     * @return int
     */
    public function getIdtiposo()
    {
        return $this->idtiposo;
    }
}
