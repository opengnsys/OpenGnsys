<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Parametros
 */
class Parametros
{
    /**
     * @var string
     */
    private $nemonico;

    /**
     * @var string|null
     */
    private $descripcion;

    /**
     * @var string
     */
    private $nomidentificador;

    /**
     * @var string
     */
    private $nomtabla;

    /**
     * @var string
     */
    private $nomliteral;

    /**
     * @var bool|null
     */
    private $tipopa = '0';

    /**
     * @var bool
     */
    private $visual = '0';

    /**
     * @var int
     */
    private $idparametro;


    /**
     * Set nemonico.
     *
     * @param string $nemonico
     *
     * @return Parametros
     */
    public function setNemonico($nemonico)
    {
        $this->nemonico = $nemonico;

        return $this;
    }

    /**
     * Get nemonico.
     *
     * @return string
     */
    public function getNemonico()
    {
        return $this->nemonico;
    }

    /**
     * Set descripcion.
     *
     * @param string|null $descripcion
     *
     * @return Parametros
     */
    public function setDescripcion($descripcion = null)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string|null
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set nomidentificador.
     *
     * @param string $nomidentificador
     *
     * @return Parametros
     */
    public function setNomidentificador($nomidentificador)
    {
        $this->nomidentificador = $nomidentificador;

        return $this;
    }

    /**
     * Get nomidentificador.
     *
     * @return string
     */
    public function getNomidentificador()
    {
        return $this->nomidentificador;
    }

    /**
     * Set nomtabla.
     *
     * @param string $nomtabla
     *
     * @return Parametros
     */
    public function setNomtabla($nomtabla)
    {
        $this->nomtabla = $nomtabla;

        return $this;
    }

    /**
     * Get nomtabla.
     *
     * @return string
     */
    public function getNomtabla()
    {
        return $this->nomtabla;
    }

    /**
     * Set nomliteral.
     *
     * @param string $nomliteral
     *
     * @return Parametros
     */
    public function setNomliteral($nomliteral)
    {
        $this->nomliteral = $nomliteral;

        return $this;
    }

    /**
     * Get nomliteral.
     *
     * @return string
     */
    public function getNomliteral()
    {
        return $this->nomliteral;
    }

    /**
     * Set tipopa.
     *
     * @param bool|null $tipopa
     *
     * @return Parametros
     */
    public function setTipopa($tipopa = null)
    {
        $this->tipopa = $tipopa;

        return $this;
    }

    /**
     * Get tipopa.
     *
     * @return bool|null
     */
    public function getTipopa()
    {
        return $this->tipopa;
    }

    /**
     * Set visual.
     *
     * @param bool $visual
     *
     * @return Parametros
     */
    public function setVisual($visual)
    {
        $this->visual = $visual;

        return $this;
    }

    /**
     * Get visual.
     *
     * @return bool
     */
    public function getVisual()
    {
        return $this->visual;
    }

    /**
     * Get idparametro.
     *
     * @return int
     */
    public function getIdparametro()
    {
        return $this->idparametro;
    }
}
