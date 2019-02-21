<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Plataformas
 */
class Plataformas
{
    /**
     * @var string
     */
    private $plataforma;

    /**
     * @var int
     */
    private $idplataforma;


    /**
     * Set plataforma.
     *
     * @param string $plataforma
     *
     * @return Plataformas
     */
    public function setPlataforma($plataforma)
    {
        $this->plataforma = $plataforma;

        return $this;
    }

    /**
     * Get plataforma.
     *
     * @return string
     */
    public function getPlataforma()
    {
        return $this->plataforma;
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
}
