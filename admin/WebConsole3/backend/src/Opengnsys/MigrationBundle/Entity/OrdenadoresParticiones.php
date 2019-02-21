<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * OrdenadoresParticiones
 */
class OrdenadoresParticiones
{
    /**
     * @var int
     */
    private $codpar;

    /**
     * @var int
     */
    private $tamano;

    /**
     * @var int
     */
    private $idsistemafichero;

    /**
     * @var int
     */
    private $idnombreso;

    /**
     * @var int
     */
    private $idimagen;

    /**
     * @var int
     */
    private $revision = '0';

    /**
     * @var int
     */
    private $idperfilsoft;

    /**
     * @var \DateTime|null
     */
    private $fechadespliegue;

    /**
     * @var string|null
     */
    private $cache;

    /**
     * @var bool
     */
    private $uso = '0';

    /**
     * @var int
     */
    private $idordenador;

    /**
     * @var int
     */
    private $numdisk;

    /**
     * @var int
     */
    private $numpar;


    /**
     * Set codpar.
     *
     * @param int $codpar
     *
     * @return OrdenadoresParticiones
     */
    public function setCodpar($codpar)
    {
        $this->codpar = $codpar;

        return $this;
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

    /**
     * Set tamano.
     *
     * @param int $tamano
     *
     * @return OrdenadoresParticiones
     */
    public function setTamano($tamano)
    {
        $this->tamano = $tamano;

        return $this;
    }

    /**
     * Get tamano.
     *
     * @return int
     */
    public function getTamano()
    {
        return $this->tamano;
    }

    /**
     * Set idsistemafichero.
     *
     * @param int $idsistemafichero
     *
     * @return OrdenadoresParticiones
     */
    public function setIdsistemafichero($idsistemafichero)
    {
        $this->idsistemafichero = $idsistemafichero;

        return $this;
    }

    /**
     * Get idsistemafichero.
     *
     * @return int
     */
    public function getIdsistemafichero()
    {
        return $this->idsistemafichero;
    }

    /**
     * Set idnombreso.
     *
     * @param int $idnombreso
     *
     * @return OrdenadoresParticiones
     */
    public function setIdnombreso($idnombreso)
    {
        $this->idnombreso = $idnombreso;

        return $this;
    }

    /**
     * Get idnombreso.
     *
     * @return int
     */
    public function getIdnombreso()
    {
        return $this->idnombreso;
    }

    /**
     * Set idimagen.
     *
     * @param int $idimagen
     *
     * @return OrdenadoresParticiones
     */
    public function setIdimagen($idimagen)
    {
        $this->idimagen = $idimagen;

        return $this;
    }

    /**
     * Get idimagen.
     *
     * @return int
     */
    public function getIdimagen()
    {
        return $this->idimagen;
    }

    /**
     * Set revision.
     *
     * @param int $revision
     *
     * @return OrdenadoresParticiones
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set idperfilsoft.
     *
     * @param int $idperfilsoft
     *
     * @return OrdenadoresParticiones
     */
    public function setIdperfilsoft($idperfilsoft)
    {
        $this->idperfilsoft = $idperfilsoft;

        return $this;
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

    /**
     * Set fechadespliegue.
     *
     * @param \DateTime|null $fechadespliegue
     *
     * @return OrdenadoresParticiones
     */
    public function setFechadespliegue($fechadespliegue = null)
    {
        $this->fechadespliegue = $fechadespliegue;

        return $this;
    }

    /**
     * Get fechadespliegue.
     *
     * @return \DateTime|null
     */
    public function getFechadespliegue()
    {
        return $this->fechadespliegue;
    }

    /**
     * Set cache.
     *
     * @param string|null $cache
     *
     * @return OrdenadoresParticiones
     */
    public function setCache($cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get cache.
     *
     * @return string|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set uso.
     *
     * @param bool $uso
     *
     * @return OrdenadoresParticiones
     */
    public function setUso($uso)
    {
        $this->uso = $uso;

        return $this;
    }

    /**
     * Get uso.
     *
     * @return bool
     */
    public function getUso()
    {
        return $this->uso;
    }

    /**
     * Set idordenador.
     *
     * @param int $idordenador
     *
     * @return OrdenadoresParticiones
     */
    public function setIdordenador($idordenador)
    {
        $this->idordenador = $idordenador;

        return $this;
    }

    /**
     * Get idordenador.
     *
     * @return int
     */
    public function getIdordenador()
    {
        return $this->idordenador;
    }

    /**
     * Set numdisk.
     *
     * @param int $numdisk
     *
     * @return OrdenadoresParticiones
     */
    public function setNumdisk($numdisk)
    {
        $this->numdisk = $numdisk;

        return $this;
    }

    /**
     * Get numdisk.
     *
     * @return int
     */
    public function getNumdisk()
    {
        return $this->numdisk;
    }

    /**
     * Set numpar.
     *
     * @param int $numpar
     *
     * @return OrdenadoresParticiones
     */
    public function setNumpar($numpar)
    {
        $this->numpar = $numpar;

        return $this;
    }

    /**
     * Get numpar.
     *
     * @return int
     */
    public function getNumpar()
    {
        return $this->numpar;
    }
}
