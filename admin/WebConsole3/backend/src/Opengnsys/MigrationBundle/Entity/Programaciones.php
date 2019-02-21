<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Programaciones
 */
class Programaciones
{
    /**
     * @var int|null
     */
    private $tipoaccion;

    /**
     * @var int|null
     */
    private $identificador;

    /**
     * @var string|null
     */
    private $nombrebloque;

    /**
     * @var int|null
     */
    private $annos;

    /**
     * @var int|null
     */
    private $meses;

    /**
     * @var int|null
     */
    private $diario;

    /**
     * @var bool|null
     */
    private $dias;

    /**
     * @var bool|null
     */
    private $semanas;

    /**
     * @var int|null
     */
    private $horas;

    /**
     * @var bool|null
     */
    private $ampm;

    /**
     * @var bool|null
     */
    private $minutos;

    /**
     * @var bool|null
     */
    private $segundos;

    /**
     * @var int|null
     */
    private $horasini;

    /**
     * @var bool|null
     */
    private $ampmini;

    /**
     * @var bool|null
     */
    private $minutosini;

    /**
     * @var int|null
     */
    private $horasfin;

    /**
     * @var bool|null
     */
    private $ampmfin;

    /**
     * @var bool|null
     */
    private $minutosfin;

    /**
     * @var bool|null
     */
    private $suspendida;

    /**
     * @var int
     */
    private $sesion;

    /**
     * @var int
     */
    private $idprogramacion;


    /**
     * Set tipoaccion.
     *
     * @param int|null $tipoaccion
     *
     * @return Programaciones
     */
    public function setTipoaccion($tipoaccion = null)
    {
        $this->tipoaccion = $tipoaccion;

        return $this;
    }

    /**
     * Get tipoaccion.
     *
     * @return int|null
     */
    public function getTipoaccion()
    {
        return $this->tipoaccion;
    }

    /**
     * Set identificador.
     *
     * @param int|null $identificador
     *
     * @return Programaciones
     */
    public function setIdentificador($identificador = null)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador.
     *
     * @return int|null
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set nombrebloque.
     *
     * @param string|null $nombrebloque
     *
     * @return Programaciones
     */
    public function setNombrebloque($nombrebloque = null)
    {
        $this->nombrebloque = $nombrebloque;

        return $this;
    }

    /**
     * Get nombrebloque.
     *
     * @return string|null
     */
    public function getNombrebloque()
    {
        return $this->nombrebloque;
    }

    /**
     * Set annos.
     *
     * @param int|null $annos
     *
     * @return Programaciones
     */
    public function setAnnos($annos = null)
    {
        $this->annos = $annos;

        return $this;
    }

    /**
     * Get annos.
     *
     * @return int|null
     */
    public function getAnnos()
    {
        return $this->annos;
    }

    /**
     * Set meses.
     *
     * @param int|null $meses
     *
     * @return Programaciones
     */
    public function setMeses($meses = null)
    {
        $this->meses = $meses;

        return $this;
    }

    /**
     * Get meses.
     *
     * @return int|null
     */
    public function getMeses()
    {
        return $this->meses;
    }

    /**
     * Set diario.
     *
     * @param int|null $diario
     *
     * @return Programaciones
     */
    public function setDiario($diario = null)
    {
        $this->diario = $diario;

        return $this;
    }

    /**
     * Get diario.
     *
     * @return int|null
     */
    public function getDiario()
    {
        return $this->diario;
    }

    /**
     * Set dias.
     *
     * @param bool|null $dias
     *
     * @return Programaciones
     */
    public function setDias($dias = null)
    {
        $this->dias = $dias;

        return $this;
    }

    /**
     * Get dias.
     *
     * @return bool|null
     */
    public function getDias()
    {
        return $this->dias;
    }

    /**
     * Set semanas.
     *
     * @param bool|null $semanas
     *
     * @return Programaciones
     */
    public function setSemanas($semanas = null)
    {
        $this->semanas = $semanas;

        return $this;
    }

    /**
     * Get semanas.
     *
     * @return bool|null
     */
    public function getSemanas()
    {
        return $this->semanas;
    }

    /**
     * Set horas.
     *
     * @param int|null $horas
     *
     * @return Programaciones
     */
    public function setHoras($horas = null)
    {
        $this->horas = $horas;

        return $this;
    }

    /**
     * Get horas.
     *
     * @return int|null
     */
    public function getHoras()
    {
        return $this->horas;
    }

    /**
     * Set ampm.
     *
     * @param bool|null $ampm
     *
     * @return Programaciones
     */
    public function setAmpm($ampm = null)
    {
        $this->ampm = $ampm;

        return $this;
    }

    /**
     * Get ampm.
     *
     * @return bool|null
     */
    public function getAmpm()
    {
        return $this->ampm;
    }

    /**
     * Set minutos.
     *
     * @param bool|null $minutos
     *
     * @return Programaciones
     */
    public function setMinutos($minutos = null)
    {
        $this->minutos = $minutos;

        return $this;
    }

    /**
     * Get minutos.
     *
     * @return bool|null
     */
    public function getMinutos()
    {
        return $this->minutos;
    }

    /**
     * Set segundos.
     *
     * @param bool|null $segundos
     *
     * @return Programaciones
     */
    public function setSegundos($segundos = null)
    {
        $this->segundos = $segundos;

        return $this;
    }

    /**
     * Get segundos.
     *
     * @return bool|null
     */
    public function getSegundos()
    {
        return $this->segundos;
    }

    /**
     * Set horasini.
     *
     * @param int|null $horasini
     *
     * @return Programaciones
     */
    public function setHorasini($horasini = null)
    {
        $this->horasini = $horasini;

        return $this;
    }

    /**
     * Get horasini.
     *
     * @return int|null
     */
    public function getHorasini()
    {
        return $this->horasini;
    }

    /**
     * Set ampmini.
     *
     * @param bool|null $ampmini
     *
     * @return Programaciones
     */
    public function setAmpmini($ampmini = null)
    {
        $this->ampmini = $ampmini;

        return $this;
    }

    /**
     * Get ampmini.
     *
     * @return bool|null
     */
    public function getAmpmini()
    {
        return $this->ampmini;
    }

    /**
     * Set minutosini.
     *
     * @param bool|null $minutosini
     *
     * @return Programaciones
     */
    public function setMinutosini($minutosini = null)
    {
        $this->minutosini = $minutosini;

        return $this;
    }

    /**
     * Get minutosini.
     *
     * @return bool|null
     */
    public function getMinutosini()
    {
        return $this->minutosini;
    }

    /**
     * Set horasfin.
     *
     * @param int|null $horasfin
     *
     * @return Programaciones
     */
    public function setHorasfin($horasfin = null)
    {
        $this->horasfin = $horasfin;

        return $this;
    }

    /**
     * Get horasfin.
     *
     * @return int|null
     */
    public function getHorasfin()
    {
        return $this->horasfin;
    }

    /**
     * Set ampmfin.
     *
     * @param bool|null $ampmfin
     *
     * @return Programaciones
     */
    public function setAmpmfin($ampmfin = null)
    {
        $this->ampmfin = $ampmfin;

        return $this;
    }

    /**
     * Get ampmfin.
     *
     * @return bool|null
     */
    public function getAmpmfin()
    {
        return $this->ampmfin;
    }

    /**
     * Set minutosfin.
     *
     * @param bool|null $minutosfin
     *
     * @return Programaciones
     */
    public function setMinutosfin($minutosfin = null)
    {
        $this->minutosfin = $minutosfin;

        return $this;
    }

    /**
     * Get minutosfin.
     *
     * @return bool|null
     */
    public function getMinutosfin()
    {
        return $this->minutosfin;
    }

    /**
     * Set suspendida.
     *
     * @param bool|null $suspendida
     *
     * @return Programaciones
     */
    public function setSuspendida($suspendida = null)
    {
        $this->suspendida = $suspendida;

        return $this;
    }

    /**
     * Get suspendida.
     *
     * @return bool|null
     */
    public function getSuspendida()
    {
        return $this->suspendida;
    }

    /**
     * Set sesion.
     *
     * @param int $sesion
     *
     * @return Programaciones
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
     * Get idprogramacion.
     *
     * @return int
     */
    public function getIdprogramacion()
    {
        return $this->idprogramacion;
    }
}
