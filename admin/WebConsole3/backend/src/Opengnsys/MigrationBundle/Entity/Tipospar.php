<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Tipospar
 */
class Tipospar
{
    /**
     * @var string
     */
    private $tipopar;

    /**
     * @var bool
     */
    private $clonable;

    /**
     * @var int
     */
    private $codpar;


    /**
     * Set tipopar.
     *
     * @param string $tipopar
     *
     * @return Tipospar
     */
    public function setTipopar($tipopar)
    {
        $this->tipopar = $tipopar;

        return $this;
    }

    /**
     * Get tipopar.
     *
     * @return string
     */
    public function getTipopar()
    {
        return $this->tipopar;
    }

    /**
     * Set clonable.
     *
     * @param bool $clonable
     *
     * @return Tipospar
     */
    public function setClonable($clonable)
    {
        $this->clonable = $clonable;

        return $this;
    }

    /**
     * Get clonable.
     *
     * @return bool
     */
    public function getClonable()
    {
        return $this->clonable;
    }

    /**
     * Get codpar.
     *
     * @return int
     */
    public function getCodpar()
    {
        return $this->codpar;
    }
}
