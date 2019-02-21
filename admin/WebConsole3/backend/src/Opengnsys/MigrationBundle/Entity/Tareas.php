<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Tareas
 */
class Tareas
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
     * @var int
     */
    private $ambito = '0';

    /**
     * @var int
     */
    private $idambito = '0';

    /**
     * @var string|null
     */
    private $restrambito;

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
    private $idtarea;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Tareas
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
     * @return Tareas
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
     * @return Tareas
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
     * Set ambito.
     *
     * @param int $ambito
     *
     * @return Tareas
     */
    public function setAmbito($ambito)
    {
        $this->ambito = $ambito;

        return $this;
    }

    /**
     * Get ambito.
     *
     * @return int
     */
    public function getAmbito()
    {
        return $this->ambito;
    }

    /**
     * Set idambito.
     *
     * @param int $idambito
     *
     * @return Tareas
     */
    public function setIdambito($idambito)
    {
        $this->idambito = $idambito;

        return $this;
    }

    /**
     * Get idambito.
     *
     * @return int
     */
    public function getIdambito()
    {
        return $this->idambito;
    }

    /**
     * Set restrambito.
     *
     * @param string|null $restrambito
     *
     * @return Tareas
     */
    public function setRestrambito($restrambito = null)
    {
        $this->restrambito = $restrambito;

        return $this;
    }

    /**
     * Get restrambito.
     *
     * @return string|null
     */
    public function getRestrambito()
    {
        return $this->restrambito;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Tareas
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
     * @return Tareas
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
     * Get idtarea.
     *
     * @return int
     */
    public function getIdtarea()
    {
        return $this->idtarea;
    }
}
