<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Procedimientos
 */
class Procedimientos
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var string|null
     */
    private $urlimg;

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int|null
     */
    private $grupoid = '0';

    /**
     * @var int
     */
    private $idprocedimiento;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Procedimientos
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
     * Set urlimg.
     *
     * @param string|null $urlimg
     *
     * @return Procedimientos
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
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Procedimientos
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
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Procedimientos
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
     * @return Procedimientos
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
     * Get idprocedimiento.
     *
     * @return int
     */
    public function getIdprocedimiento()
    {
        return $this->idprocedimiento;
    }
}
