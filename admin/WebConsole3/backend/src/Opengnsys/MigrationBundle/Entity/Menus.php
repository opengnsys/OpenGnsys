<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Menus
 */
class Menus
{
    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var int
     */
    private $idurlimg = '0';

    /**
     * @var string|null
     */
    private $titulo;

    /**
     * @var bool|null
     */
    private $modalidad;

    /**
     * @var bool|null
     */
    private $smodalidad;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int
     */
    private $grupoid = '0';

    /**
     * @var string|null
     */
    private $htmlmenupub;

    /**
     * @var string|null
     */
    private $htmlmenupri;

    /**
     * @var string|null
     */
    private $resolucion;

    /**
     * @var int
     */
    private $idmenu;


    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Menus
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
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Menus
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
     * Set idurlimg.
     *
     * @param int $idurlimg
     *
     * @return Menus
     */
    public function setIdurlimg($idurlimg)
    {
        $this->idurlimg = $idurlimg;

        return $this;
    }

    /**
     * Get idurlimg.
     *
     * @return int
     */
    public function getIdurlimg()
    {
        return $this->idurlimg;
    }

    /**
     * Set titulo.
     *
     * @param string|null $titulo
     *
     * @return Menus
     */
    public function setTitulo($titulo = null)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo.
     *
     * @return string|null
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set modalidad.
     *
     * @param bool|null $modalidad
     *
     * @return Menus
     */
    public function setModalidad($modalidad = null)
    {
        $this->modalidad = $modalidad;

        return $this;
    }

    /**
     * Get modalidad.
     *
     * @return bool|null
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * Set smodalidad.
     *
     * @param bool|null $smodalidad
     *
     * @return Menus
     */
    public function setSmodalidad($smodalidad = null)
    {
        $this->smodalidad = $smodalidad;

        return $this;
    }

    /**
     * Get smodalidad.
     *
     * @return bool|null
     */
    public function getSmodalidad()
    {
        return $this->smodalidad;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Menus
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
     * @param int $grupoid
     *
     * @return Menus
     */
    public function setGrupoid($grupoid)
    {
        $this->grupoid = $grupoid;

        return $this;
    }

    /**
     * Get grupoid.
     *
     * @return int
     */
    public function getGrupoid()
    {
        return $this->grupoid;
    }

    /**
     * Set htmlmenupub.
     *
     * @param string|null $htmlmenupub
     *
     * @return Menus
     */
    public function setHtmlmenupub($htmlmenupub = null)
    {
        $this->htmlmenupub = $htmlmenupub;

        return $this;
    }

    /**
     * Get htmlmenupub.
     *
     * @return string|null
     */
    public function getHtmlmenupub()
    {
        return $this->htmlmenupub;
    }

    /**
     * Set htmlmenupri.
     *
     * @param string|null $htmlmenupri
     *
     * @return Menus
     */
    public function setHtmlmenupri($htmlmenupri = null)
    {
        $this->htmlmenupri = $htmlmenupri;

        return $this;
    }

    /**
     * Get htmlmenupri.
     *
     * @return string|null
     */
    public function getHtmlmenupri()
    {
        return $this->htmlmenupri;
    }

    /**
     * Set resolucion.
     *
     * @param string|null $resolucion
     *
     * @return Menus
     */
    public function setResolucion($resolucion = null)
    {
        $this->resolucion = $resolucion;

        return $this;
    }

    /**
     * Get resolucion.
     *
     * @return string|null
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Get idmenu.
     *
     * @return int
     */
    public function getIdmenu()
    {
        return $this->idmenu;
    }
}
