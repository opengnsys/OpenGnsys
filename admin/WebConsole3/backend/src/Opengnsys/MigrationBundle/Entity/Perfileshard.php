<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Perfileshard
 */
class Perfileshard
{
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
     * @var string
     */
    private $winboot = 'reboot';

    /**
     * @var int
     */
    private $idperfilhard;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Perfileshard
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
     * @return Perfileshard
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
     * @return Perfileshard
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
     * @return Perfileshard
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
     * Set winboot.
     *
     * @param string $winboot
     *
     * @return Perfileshard
     */
    public function setWinboot($winboot)
    {
        $this->winboot = $winboot;

        return $this;
    }

    /**
     * Get winboot.
     *
     * @return string
     */
    public function getWinboot()
    {
        return $this->winboot;
    }

    /**
     * Get idperfilhard.
     *
     * @return int
     */
    public function getIdperfilhard()
    {
        return $this->idperfilhard;
    }
}
