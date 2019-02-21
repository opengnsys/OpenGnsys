<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Asistentes
 */
class Asistentes
{
    /**
     * @var string
     */
    private $pagina;

    /**
     * @var string
     */
    private $gestor;

    /**
     * @var string
     */
    private $funcion;

    /**
     * @var string|null
     */
    private $urlimg;

    /**
     * @var bool|null
     */
    private $aplicambito;

    /**
     * @var string|null
     */
    private $visuparametros;

    /**
     * @var string|null
     */
    private $parametros;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var bool
     */
    private $activo;

    /**
     * @var int
     */
    private $idcomando;

    /**
     * @var string
     */
    private $descripcion;


    /**
     * Set pagina.
     *
     * @param string $pagina
     *
     * @return Asistentes
     */
    public function setPagina($pagina)
    {
        $this->pagina = $pagina;

        return $this;
    }

    /**
     * Get pagina.
     *
     * @return string
     */
    public function getPagina()
    {
        return $this->pagina;
    }

    /**
     * Set gestor.
     *
     * @param string $gestor
     *
     * @return Asistentes
     */
    public function setGestor($gestor)
    {
        $this->gestor = $gestor;

        return $this;
    }

    /**
     * Get gestor.
     *
     * @return string
     */
    public function getGestor()
    {
        return $this->gestor;
    }

    /**
     * Set funcion.
     *
     * @param string $funcion
     *
     * @return Asistentes
     */
    public function setFuncion($funcion)
    {
        $this->funcion = $funcion;

        return $this;
    }

    /**
     * Get funcion.
     *
     * @return string
     */
    public function getFuncion()
    {
        return $this->funcion;
    }

    /**
     * Set urlimg.
     *
     * @param string|null $urlimg
     *
     * @return Asistentes
     */
    public function setUrlimg($urlimg = null)
    {
        $this->urlimg = $urlimg;

        return $this;
    }

    /**
     * Get urlimg.
     *
     * @return string|null
     */
    public function getUrlimg()
    {
        return $this->urlimg;
    }

    /**
     * Set aplicambito.
     *
     * @param bool|null $aplicambito
     *
     * @return Asistentes
     */
    public function setAplicambito($aplicambito = null)
    {
        $this->aplicambito = $aplicambito;

        return $this;
    }

    /**
     * Get aplicambito.
     *
     * @return bool|null
     */
    public function getAplicambito()
    {
        return $this->aplicambito;
    }

    /**
     * Set visuparametros.
     *
     * @param string|null $visuparametros
     *
     * @return Asistentes
     */
    public function setVisuparametros($visuparametros = null)
    {
        $this->visuparametros = $visuparametros;

        return $this;
    }

    /**
     * Get visuparametros.
     *
     * @return string|null
     */
    public function getVisuparametros()
    {
        return $this->visuparametros;
    }

    /**
     * Set parametros.
     *
     * @param string|null $parametros
     *
     * @return Asistentes
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
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Asistentes
     */
    public function setComentarios($comentarios = null)
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    /**
     * Get comentarios.
     *
     * @return string|null
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }

    /**
     * Set activo.
     *
     * @param bool $activo
     *
     * @return Asistentes
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo.
     *
     * @return bool
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set idcomando.
     *
     * @param int $idcomando
     *
     * @return Asistentes
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
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Asistentes
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }
}
