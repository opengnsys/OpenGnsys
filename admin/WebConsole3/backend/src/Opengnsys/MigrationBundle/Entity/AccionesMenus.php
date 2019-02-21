<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * AccionesMenus
 */
class AccionesMenus
{
    /**
     * @var bool
     */
    private $tipoaccion = '0';

    /**
     * @var int
     */
    private $idtipoaccion = '0';

    /**
     * @var int
     */
    private $idmenu = '0';

    /**
     * @var bool|null
     */
    private $tipoitem;

    /**
     * @var int|null
     */
    private $idurlimg;

    /**
     * @var string|null
     */
    private $descripitem;

    /**
     * @var bool|null
     */
    private $orden;

    /**
     * @var int
     */
    private $idaccionmenu;


    /**
     * Set tipoaccion.
     *
     * @param bool $tipoaccion
     *
     * @return AccionesMenus
     */
    public function setTipoaccion($tipoaccion)
    {
        $this->tipoaccion = $tipoaccion;

        return $this;
    }

    /**
     * Get tipoaccion.
     *
     * @return bool
     */
    public function getTipoaccion()
    {
        return $this->tipoaccion;
    }

    /**
     * Set idtipoaccion.
     *
     * @param int $idtipoaccion
     *
     * @return AccionesMenus
     */
    public function setIdtipoaccion($idtipoaccion)
    {
        $this->idtipoaccion = $idtipoaccion;

        return $this;
    }

    /**
     * Get idtipoaccion.
     *
     * @return int
     */
    public function getIdtipoaccion()
    {
        return $this->idtipoaccion;
    }

    /**
     * Set idmenu.
     *
     * @param int $idmenu
     *
     * @return AccionesMenus
     */
    public function setIdmenu($idmenu)
    {
        $this->idmenu = $idmenu;

        return $this;
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

    /**
     * Set tipoitem.
     *
     * @param bool|null $tipoitem
     *
     * @return AccionesMenus
     */
    public function setTipoitem($tipoitem = null)
    {
        $this->tipoitem = $tipoitem;

        return $this;
    }

    /**
     * Get tipoitem.
     *
     * @return bool|null
     */
    public function getTipoitem()
    {
        return $this->tipoitem;
    }

    /**
     * Set idurlimg.
     *
     * @param int|null $idurlimg
     *
     * @return AccionesMenus
     */
    public function setIdurlimg($idurlimg = null)
    {
        $this->idurlimg = $idurlimg;

        return $this;
    }

    /**
     * Get idurlimg.
     *
     * @return int|null
     */
    public function getIdurlimg()
    {
        return $this->idurlimg;
    }

    /**
     * Set descripitem.
     *
     * @param string|null $descripitem
     *
     * @return AccionesMenus
     */
    public function setDescripitem($descripitem = null)
    {
        $this->descripitem = $descripitem;

        return $this;
    }

    /**
     * Get descripitem.
     *
     * @return string|null
     */
    public function getDescripitem()
    {
        return $this->descripitem;
    }

    /**
     * Set orden.
     *
     * @param bool|null $orden
     *
     * @return AccionesMenus
     */
    public function setOrden($orden = null)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden.
     *
     * @return bool|null
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Get idaccionmenu.
     *
     * @return int
     */
    public function getIdaccionmenu()
    {
        return $this->idaccionmenu;
    }
}
