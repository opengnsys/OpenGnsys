<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Perfilessoft
 */
class Perfilessoft
{
    /**
     * @var int|null
     */
    private $idnombreso;

    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var int
     */
    private $idcentro;

    /**
     * @var int
     */
    private $idperfilsoft;


    /**
     * Set idnombreso.
     *
     * @param int|null $idnombreso
     *
     * @return Perfilessoft
     */
    public function setIdnombreso($idnombreso = null)
    {
        $this->idnombreso = $idnombreso;

        return $this;
    }

    /**
     * Get idnombreso.
     *
     * @return int|null
     */
    public function getIdnombreso()
    {
        return $this->idnombreso;
    }

    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Perfilessoft
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
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Perfilessoft
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
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Perfilessoft
     */
    public function setGrupoid($grupoid = null)
    {
        $this->grupoid = $grupoid;

        return $this;
    }

    /**
     * Get grupoid.
     *
     * @return int|null
     */
    public function getGrupoid()
    {
        return $this->grupoid;
    }

    /**
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Perfilessoft
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
     * Get idperfilsoft.
     *
     * @return int
     */
    public function getIdperfilsoft()
    {
        return $this->idperfilsoft;
    }
}
