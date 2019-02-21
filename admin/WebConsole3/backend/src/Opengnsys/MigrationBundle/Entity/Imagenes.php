<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Imagenes
 */
class Imagenes
{
    /**
     * @var string
     */
    private $nombreca;

    /**
     * @var int
     */
    private $revision = '0';

    /**
     * @var string
     */
    private $descripcion = '';

    /**
     * @var int|null
     */
    private $idperfilsoft;

    /**
     * @var int|null
     */
    private $idcentro;

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
    private $idrepositorio = '0';

    /**
     * @var int
     */
    private $idordenador = '0';

    /**
     * @var int
     */
    private $numdisk = '0';

    /**
     * @var int
     */
    private $numpar = '0';

    /**
     * @var int
     */
    private $codpar = '0';

    /**
     * @var bool|null
     */
    private $tipo;

    /**
     * @var int
     */
    private $imagenid = '0';

    /**
     * @var string|null
     */
    private $ruta;

    /**
     * @var \DateTime|null
     */
    private $fechacreacion;

    /**
     * @var bool|null
     */
    private $inremotepc = '0';

    /**
     * @var int
     */
    private $idimagen;


    /**
     * Set nombreca.
     *
     * @param string $nombreca
     *
     * @return Imagenes
     */
    public function setNombreca($nombreca)
    {
        $this->nombreca = $nombreca;

        return $this;
    }

    /**
     * Get nombreca.
     *
     * @return string
     */
    public function getNombreca()
    {
        return $this->nombreca;
    }

    /**
     * Set revision.
     *
     * @param int $revision
     *
     * @return Imagenes
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
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return Imagenes
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
     * Set idperfilsoft.
     *
     * @param int|null $idperfilsoft
     *
     * @return Imagenes
     */
    public function setIdperfilsoft($idperfilsoft = null)
    {
        $this->idperfilsoft = $idperfilsoft;

        return $this;
    }

    /**
     * Get idperfilsoft.
     *
     * @return int|null
     */
    public function getIdperfilsoft()
    {
        return $this->idperfilsoft;
    }

    /**
     * Set idcentro.
     *
     * @param int|null $idcentro
     *
     * @return Imagenes
     */
    public function setIdcentro($idcentro = null)
    {
        $this->idcentro = $idcentro;

        return $this;
    }

    /**
     * Get idcentro.
     *
     * @return int|null
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
     * @return Imagenes
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
     * @return Imagenes
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
     * Set idrepositorio.
     *
     * @param int $idrepositorio
     *
     * @return Imagenes
     */
    public function setIdrepositorio($idrepositorio)
    {
        $this->idrepositorio = $idrepositorio;

        return $this;
    }

    /**
     * Get idrepositorio.
     *
     * @return int
     */
    public function getIdrepositorio()
    {
        return $this->idrepositorio;
    }

    /**
     * Set idordenador.
     *
     * @param int $idordenador
     *
     * @return Imagenes
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
     * @return Imagenes
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
     * @return Imagenes
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

    /**
     * Set codpar.
     *
     * @param int $codpar
     *
     * @return Imagenes
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
     * Set tipo.
     *
     * @param bool|null $tipo
     *
     * @return Imagenes
     */
    public function setTipo($tipo = null)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo.
     *
     * @return bool|null
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set imagenid.
     *
     * @param int $imagenid
     *
     * @return Imagenes
     */
    public function setImagenid($imagenid)
    {
        $this->imagenid = $imagenid;

        return $this;
    }

    /**
     * Get imagenid.
     *
     * @return int
     */
    public function getImagenid()
    {
        return $this->imagenid;
    }

    /**
     * Set ruta.
     *
     * @param string|null $ruta
     *
     * @return Imagenes
     */
    public function setRuta($ruta = null)
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * Get ruta.
     *
     * @return string|null
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set fechacreacion.
     *
     * @param \DateTime|null $fechacreacion
     *
     * @return Imagenes
     */
    public function setFechacreacion($fechacreacion = null)
    {
        $this->fechacreacion = $fechacreacion;

        return $this;
    }

    /**
     * Get fechacreacion.
     *
     * @return \DateTime|null
     */
    public function getFechacreacion()
    {
        return $this->fechacreacion;
    }

    /**
     * Set inremotepc.
     *
     * @param bool|null $inremotepc
     *
     * @return Imagenes
     */
    public function setInremotepc($inremotepc = null)
    {
        $this->inremotepc = $inremotepc;

        return $this;
    }

    /**
     * Get inremotepc.
     *
     * @return bool|null
     */
    public function getInremotepc()
    {
        return $this->inremotepc;
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
}
