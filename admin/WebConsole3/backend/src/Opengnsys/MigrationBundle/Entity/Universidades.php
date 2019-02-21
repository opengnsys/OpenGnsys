<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Universidades
 */
class Universidades
{
    /**
     * @var string
     */
    private $nombreuniversidad = '';

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int
     */
    private $iduniversidad;


    /**
     * Set nombreuniversidad.
     *
     * @param string $nombreuniversidad
     *
     * @return Universidades
     */
    public function setNombreuniversidad($nombreuniversidad)
    {
        $this->nombreuniversidad = $nombreuniversidad;

        return $this;
    }

    /**
     * Get nombreuniversidad.
     *
     * @return string
     */
    public function getNombreuniversidad()
    {
        return $this->nombreuniversidad;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Universidades
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
     * Get iduniversidad.
     *
     * @return int
     */
    public function getIduniversidad()
    {
        return $this->iduniversidad;
    }
}
