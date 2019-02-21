<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Entornos
 */
class Entornos
{
    /**
     * @var string
     */
    private $ipserveradm;

    /**
     * @var int
     */
    private $portserveradm;

    /**
     * @var string
     */
    private $protoclonacion;

    /**
     * @var int
     */
    private $identorno;


    /**
     * Set ipserveradm.
     *
     * @param string $ipserveradm
     *
     * @return Entornos
     */
    public function setIpserveradm($ipserveradm)
    {
        $this->ipserveradm = $ipserveradm;

        return $this;
    }

    /**
     * Get ipserveradm.
     *
     * @return string
     */
    public function getIpserveradm()
    {
        return $this->ipserveradm;
    }

    /**
     * Set portserveradm.
     *
     * @param int $portserveradm
     *
     * @return Entornos
     */
    public function setPortserveradm($portserveradm)
    {
        $this->portserveradm = $portserveradm;

        return $this;
    }

    /**
     * Get portserveradm.
     *
     * @return int
     */
    public function getPortserveradm()
    {
        return $this->portserveradm;
    }

    /**
     * Set protoclonacion.
     *
     * @param string $protoclonacion
     *
     * @return Entornos
     */
    public function setProtoclonacion($protoclonacion)
    {
        $this->protoclonacion = $protoclonacion;

        return $this;
    }

    /**
     * Get protoclonacion.
     *
     * @return string
     */
    public function getProtoclonacion()
    {
        return $this->protoclonacion;
    }

    /**
     * Get identorno.
     *
     * @return int
     */
    public function getIdentorno()
    {
        return $this->identorno;
    }
}
