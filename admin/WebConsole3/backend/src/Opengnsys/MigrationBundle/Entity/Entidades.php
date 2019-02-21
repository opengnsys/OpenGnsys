<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Entidades
 */
class Entidades
{
    /**
     * @var string
     */
    private $nombreentidad = '';

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int|null
     */
    private $iduniversidad;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var bool
     */
    private $ogunit = '0';

    /**
     * @var int
     */
    private $identidad;


    /**
     * Set nombreentidad.
     *
     * @param string $nombreentidad
     *
     * @return Entidades
     */
    public function setNombreentidad($nombreentidad)
    {
        $this->nombreentidad = $nombreentidad;

        return $this;
    }

    /**
     * Get nombreentidad.
     *
     * @return string
     */
    public function getNombreentidad()
    {
        return $this->nombreentidad;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Entidades
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
     * Set iduniversidad.
     *
     * @param int|null $iduniversidad
     *
     * @return Entidades
     */
    public function setIduniversidad($iduniversidad = null)
    {
        $this->iduniversidad = $iduniversidad;

        return $this;
    }

    /**
     * Get iduniversidad.
     *
     * @return int|null
     */
    public function getIduniversidad()
    {
        return $this->iduniversidad;
    }

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Entidades
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
     * Set ogunit.
     *
     * @param bool $ogunit
     *
     * @return Entidades
     */
    public function setOgunit($ogunit)
    {
        $this->ogunit = $ogunit;

        return $this;
    }

    /**
     * Get ogunit.
     *
     * @return bool
     */
    public function getOgunit()
    {
        return $this->ogunit;
    }

    /**
     * Get identidad.
     *
     * @return int
     */
    public function getIdentidad()
    {
        return $this->identidad;
    }
}
