<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * ProcedimientosAcciones
 */
class ProcedimientosAcciones
{
    /**
     * @var int
     */
    private $idprocedimiento = '0';

    /**
     * @var int|null
     */
    private $orden;

    /**
     * @var int
     */
    private $idcomando = '0';

    /**
     * @var string|null
     */
    private $parametros;

    /**
     * @var int
     */
    private $procedimientoid;

    /**
     * @var int
     */
    private $idprocedimientoaccion;


    /**
     * Set idprocedimiento.
     *
     * @param int $idprocedimiento
     *
     * @return ProcedimientosAcciones
     */
    public function setIdprocedimiento($idprocedimiento)
    {
        $this->idprocedimiento = $idprocedimiento;

        return $this;
    }

    /**
     * Get idprocedimiento.
     *
     * @return int
     */
    public function getIdprocedimiento()
    {
        return $this->idprocedimiento;
    }

    /**
     * Set orden.
     *
     * @param int|null $orden
     *
     * @return ProcedimientosAcciones
     */
    public function setOrden($orden = null)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden.
     *
     * @return int|null
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set idcomando.
     *
     * @param int $idcomando
     *
     * @return ProcedimientosAcciones
     */
    public function setIdcomando($idcomando)
    {
        $this->idcomando = $idcomando;

        return $this;
    }

    /**
     * Get idcomando.
     *
     * @return int
     */
    public function getIdcomando()
    {
        return $this->idcomando;
    }

    /**
     * Set parametros.
     *
     * @param string|null $parametros
     *
     * @return ProcedimientosAcciones
     */
    public function setParametros($parametros = null)
    {
        $this->parametros = $parametros;

        return $this;
    }

    /**
     * Get parametros.
     *
     * @return string|null
     */
    public function getParametros()
    {
        return $this->parametros;
    }

    /**
     * Set procedimientoid.
     *
     * @param int $procedimientoid
     *
     * @return ProcedimientosAcciones
     */
    public function setProcedimientoid($procedimientoid)
    {
        $this->procedimientoid = $procedimientoid;

        return $this;
    }

    /**
     * Get procedimientoid.
     *
     * @return int
     */
    public function getProcedimientoid()
    {
        return $this->procedimientoid;
    }

    /**
     * Get idprocedimientoaccion.
     *
     * @return int
     */
    public function getIdprocedimientoaccion()
    {
        return $this->idprocedimientoaccion;
    }
}
