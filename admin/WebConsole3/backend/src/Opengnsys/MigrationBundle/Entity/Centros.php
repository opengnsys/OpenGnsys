<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Centros
 */
class Centros
{
    /**
     * @var string
     */
    private $nombrecentro = '';

    /**
     * @var int|null
     */
    private $identidad;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var string|null
     */
    private $directorio = '';

    /**
     * @var int
     */
    private $idcentro;


    /**
     * Set nombrecentro.
     *
     * @param string $nombrecentro
     *
     * @return Centros
     */
    public function setNombrecentro($nombrecentro)
    {
        $this->nombrecentro = $nombrecentro;

        return $this;
    }

    /**
     * Get nombrecentro.
     *
     * @return string
     */
    public function getNombrecentro()
    {
        return $this->nombrecentro;
    }

    /**
     * Set identidad.
     *
     * @param int|null $identidad
     *
     * @return Centros
     */
    public function setIdentidad($identidad = null)
    {
        $this->identidad = $identidad;

        return $this;
    }

    /**
     * Get identidad.
     *
     * @return int|null
     */
    public function getIdentidad()
    {
        return $this->identidad;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Centros
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
     * Set directorio.
     *
     * @param string|null $directorio
     *
     * @return Centros
     */
    public function setDirectorio($directorio = null)
    {
        $this->directorio = $directorio;

        return $this;
    }

    /**
     * Get directorio.
     *
     * @return string|null
     */
    public function getDirectorio()
    {
        return $this->directorio;
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
}
