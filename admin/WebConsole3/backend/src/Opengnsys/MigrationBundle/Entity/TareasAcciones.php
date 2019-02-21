<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * TareasAcciones
 */
class TareasAcciones
{
    /**
     * @var int
     */
    private $idtarea = '0';

    /**
     * @var int
     */
    private $orden = '0';

    /**
     * @var int
     */
    private $idprocedimiento = '0';

    /**
     * @var int|null
     */
    private $tareaid = '0';

    /**
     * @var int
     */
    private $idtareaaccion;


    /**
     * Set idtarea.
     *
     * @param int $idtarea
     *
     * @return TareasAcciones
     */
    public function setIdtarea($idtarea)
    {
        $this->idtarea = $idtarea;

        return $this;
    }

    /**
     * Get idtarea.
     *
     * @return int
     */
    public function getIdtarea()
    {
        return $this->idtarea;
    }

    /**
     * Set orden.
     *
     * @param int $orden
     *
     * @return TareasAcciones
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden.
     *
     * @return int
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set idprocedimiento.
     *
     * @param int $idprocedimiento
     *
     * @return TareasAcciones
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
     * Set tareaid.
     *
     * @param int|null $tareaid
     *
     * @return TareasAcciones
     */
    public function setTareaid($tareaid = null)
    {
        $this->tareaid = $tareaid;

        return $this;
    }

    /**
     * Get tareaid.
     *
     * @return int|null
     */
    public function getTareaid()
    {
        return $this->tareaid;
    }

    /**
     * Get idtareaaccion.
     *
     * @return int
     */
    public function getIdtareaaccion()
    {
        return $this->idtareaaccion;
    }
}
