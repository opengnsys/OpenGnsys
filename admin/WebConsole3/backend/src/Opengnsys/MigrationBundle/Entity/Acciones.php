<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Acciones
 */
class Acciones
{
    /**
     * @var int
     */
    private $tipoaccion;

    /**
     * @var int
     */
    private $idtipoaccion;

    /**
     * @var string
     */
    private $descriaccion;

    /**
     * @var int
     */
    private $idordenador;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var int
     */
    private $sesion;

    /**
     * @var int
     */
    private $idcomando;

    /**
     * @var string|null
     */
    private $parametros;

    /**
     * @var \DateTime
     */
    private $fechahorareg = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $fechahorafin = '1970-01-01 00:00:00';

    /**
     * @var bool
     */
    private $estado = '0';

    /**
     * @var bool
     */
    private $resultado = '0';

    /**
     * @var string|null
     */
    private $descrinotificacion;

    /**
     * @var int
     */
    private $ambito = '0';

    /**
     * @var int
     */
    private $idambito = '0';

    /**
     * @var string|null
     */
    private $restrambito;

    /**
     * @var int
     */
    private $idprocedimiento = '0';

    /**
     * @var int
     */
    private $idtarea = '0';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var int
     */
    private $idprogramacion = '0';

    /**
     * @var int
     */
    private $idaccion;


    /**
     * Set tipoaccion.
     *
     * @param int $tipoaccion
     *
     * @return Acciones
     */
    public function setTipoaccion($tipoaccion)
    {
        $this->tipoaccion = $tipoaccion;

        return $this;
    }

    /**
     * Get tipoaccion.
     *
     * @return int
     */
    public function getTipoaccion()
    {
        return $this->tipoaccion;
    }

    /**
     * Set idtipoaccion.
     *
     * @param int $idtipoaccion
     *
     * @return Acciones
     */
    public function setIdtipoaccion($idtipoaccion)
    {
        $this->idtipoaccion = $idtipoaccion;

        return $this;
    }

    /**
     * Get idtipoaccion.
     *
     * @return int
     */
    public function getIdtipoaccion()
    {
        return $this->idtipoaccion;
    }

    /**
     * Set descriaccion.
     *
     * @param string $descriaccion
     *
     * @return Acciones
     */
    public function setDescriaccion($descriaccion)
    {
        $this->descriaccion = $descriaccion;

        return $this;
    }

    /**
     * Get descriaccion.
     *
     * @return string
     */
    public function getDescriaccion()
    {
        return $this->descriaccion;
    }

    /**
     * Set idordenador.
     *
     * @param int $idordenador
     *
     * @return Acciones
     */
    public function setIdordenador($idordenador)
    {
        $this->idordenador = $idordenador;

        return $this;
    }

    /**
     * Get idordenador.
     *
     * @return int
     */
    public function getIdordenador()
    {
        return $this->idordenador;
    }

    /**
     * Set ip.
     *
     * @param string $ip
     *
     * @return Acciones
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set sesion.
     *
     * @param int $sesion
     *
     * @return Acciones
     */
    public function setSesion($sesion)
    {
        $this->sesion = $sesion;

        return $this;
    }

    /**
     * Get sesion.
     *
     * @return int
     */
    public function getSesion()
    {
        return $this->sesion;
    }

    /**
     * Set idcomando.
     *
     * @param int $idcomando
     *
     * @return Acciones
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
     * @return Acciones
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
     * Set fechahorareg.
     *
     * @param \DateTime $fechahorareg
     *
     * @return Acciones
     */
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;

        return $this;
    }

    /**
     * Get fechahorareg.
     *
     * @return \DateTime
     */
    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    /**
     * Set fechahorafin.
     *
     * @param \DateTime $fechahorafin
     *
     * @return Acciones
     */
    public function setFechahorafin($fechahorafin)
    {
        $this->fechahorafin = $fechahorafin;

        return $this;
    }

    /**
     * Get fechahorafin.
     *
     * @return \DateTime
     */
    public function getFechahorafin()
    {
        return $this->fechahorafin;
    }

    /**
     * Set estado.
     *
     * @param bool $estado
     *
     * @return Acciones
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado.
     *
     * @return bool
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set resultado.
     *
     * @param bool $resultado
     *
     * @return Acciones
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;

        return $this;
    }

    /**
     * Get resultado.
     *
     * @return bool
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Set descrinotificacion.
     *
     * @param string|null $descrinotificacion
     *
     * @return Acciones
     */
    public function setDescrinotificacion($descrinotificacion = null)
    {
        $this->descrinotificacion = $descrinotificacion;

        return $this;
    }

    /**
     * Get descrinotificacion.
     *
     * @return string|null
     */
    public function getDescrinotificacion()
    {
        return $this->descrinotificacion;
    }

    /**
     * Set ambito.
     *
     * @param int $ambito
     *
     * @return Acciones
     */
    public function setAmbito($ambito)
    {
        $this->ambito = $ambito;

        return $this;
    }

    /**
     * Get ambito.
     *
     * @return int
     */
    public function getAmbito()
    {
        return $this->ambito;
    }

    /**
     * Set idambito.
     *
     * @param int $idambito
     *
     * @return Acciones
     */
    public function setIdambito($idambito)
    {
        $this->idambito = $idambito;

        return $this;
    }

    /**
     * Get idambito.
     *
     * @return int
     */
    public function getIdambito()
    {
        return $this->idambito;
    }

    /**
     * Set restrambito.
     *
     * @param string|null $restrambito
     *
     * @return Acciones
     */
    public function setRestrambito($restrambito = null)
    {
        $this->restrambito = $restrambito;

        return $this;
    }

    /**
     * Get restrambito.
     *
     * @return string|null
     */
    public function getRestrambito()
    {
        return $this->restrambito;
    }

    /**
     * Set idprocedimiento.
     *
     * @param int $idprocedimiento
     *
     * @return Acciones
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
     * Set idtarea.
     *
     * @param int $idtarea
     *
     * @return Acciones
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
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Acciones
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
     * Set idprogramacion.
     *
     * @param int $idprogramacion
     *
     * @return Acciones
     */
    public function setIdprogramacion($idprogramacion)
    {
        $this->idprogramacion = $idprogramacion;

        return $this;
    }

    /**
     * Get idprogramacion.
     *
     * @return int
     */
    public function getIdprogramacion()
    {
        return $this->idprogramacion;
    }

    /**
     * Get idaccion.
     *
     * @return int
     */
    public function getIdaccion()
    {
        return $this->idaccion;
    }
}
