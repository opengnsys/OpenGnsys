<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * AdministradoresCentros
 */
class AdministradoresCentros
{
    /**
     * @var int
     */
    private $idusuario = '0';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var int
     */
    private $idadministradorcentro;


    /**
     * Set idusuario.
     *
     * @param int $idusuario
     *
     * @return AdministradoresCentros
     */
    public function setIdusuario($idusuario)
    {
        $this->idusuario = $idusuario;

        return $this;
    }

    /**
     * Get idusuario.
     *
     * @return int
     */
    public function getIdusuario()
    {
        return $this->idusuario;
    }

    /**
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return AdministradoresCentros
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
     * Get idadministradorcentro.
     *
     * @return int
     */
    public function getIdadministradorcentro()
    {
        return $this->idadministradorcentro;
    }
}
