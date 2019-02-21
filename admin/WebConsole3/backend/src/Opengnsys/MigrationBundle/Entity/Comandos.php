<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Class Comandos
 * @package Opengnsys\MigrationBundle\Entity
 */
class Comandos
{

    /**
     * @var string
     */
    private $descripcion = '';

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
     * @var string
     */
    private $submenu = '';

    /**
     * @var int
     */
    private $idcomando;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Comandos
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

    /**
     * Set pagina.
     *
     * @param string $pagina
     *
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * @return Comandos
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
     * Set submenu.
     *
     * @param string $submenu
     *
     * @return Comandos
     */
    public function setSubmenu($submenu)
    {
        $this->submenu = $submenu;

        return $this;
    }

    /**
     * Get submenu.
     *
     * @return string
     */
    public function getSubmenu()
    {
        return $this->submenu;
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
}
