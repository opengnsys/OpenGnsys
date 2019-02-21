<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Softwares
 */
class Softwares
{
    /**
     * @var int
     */
    private $idtiposoftware = '0';

    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var string|null
     */
    private $urlimg;

    /**
     * @var int|null
     */
    private $idtiposo;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var int
     */
    private $idsoftware;


    /**
     * Set idtiposoftware.
     *
     * @param int $idtiposoftware
     *
     * @return Softwares
     */
    public function setIdtiposoftware($idtiposoftware)
    {
        $this->idtiposoftware = $idtiposoftware;

        return $this;
    }

    /**
     * Get idtiposoftware.
     *
     * @return int
     */
    public function getIdtiposoftware()
    {
        return $this->idtiposoftware;
    }

    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Softwares
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
     * @return Softwares
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
     * Set urlimg.
     *
     * @param string|null $urlimg
     *
     * @return Softwares
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
     * Set idtiposo.
     *
     * @param int|null $idtiposo
     *
     * @return Softwares
     */
    public function setIdtiposo($idtiposo = null)
    {
        $this->idtiposo = $idtiposo;

        return $this;
    }

    /**
     * Get idtiposo.
     *
     * @return int|null
     */
    public function getIdtiposo()
    {
        return $this->idtiposo;
    }

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Softwares
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
     * Get idsoftware.
     *
     * @return int
     */
    public function getIdsoftware()
    {
        return $this->idsoftware;
    }
}
