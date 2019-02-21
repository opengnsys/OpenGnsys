<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Aulas
 */
class Aulas
{
    /**
     * @var string
     */
    private $nombreaula = '';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var string|null
     */
    private $urlfoto;

    /**
     * @var bool|null
     */
    private $cagnon;

    /**
     * @var bool|null
     */
    private $pizarra;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var string|null
     */
    private $ubicacion;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int|null
     */
    private $puestos;

    /**
     * @var bool|null
     */
    private $horaresevini;

    /**
     * @var bool|null
     */
    private $horaresevfin;

    /**
     * @var bool
     */
    private $modomul;

    /**
     * @var string
     */
    private $ipmul;

    /**
     * @var int
     */
    private $pormul;

    /**
     * @var int
     */
    private $velmul = '70';

    /**
     * @var string|null
     */
    private $router;

    /**
     * @var string|null
     */
    private $netmask;

    /**
     * @var string|null
     */
    private $dns;

    /**
     * @var string|null
     */
    private $proxy;

    /**
     * @var string|null
     */
    private $ntp;

    /**
     * @var string|null
     */
    private $modp2p = 'peer';

    /**
     * @var int
     */
    private $timep2p = '60';

    /**
     * @var bool|null
     */
    private $validacion = '0';

    /**
     * @var string|null
     */
    private $paginalogin;

    /**
     * @var string|null
     */
    private $paginavalidacion;

    /**
     * @var bool|null
     */
    private $inremotepc = '0';

    /**
     * @var string
     */
    private $oglivedir = 'ogLive';

    /**
     * @var int
     */
    private $idaula;


    /**
     * Set nombreaula.
     *
     * @param string $nombreaula
     *
     * @return Aulas
     */
    public function setNombreaula($nombreaula)
    {
        $this->nombreaula = $nombreaula;

        return $this;
    }

    /**
     * Get nombreaula.
     *
     * @return string
     */
    public function getNombreaula()
    {
        return $this->nombreaula;
    }

    /**
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Aulas
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
     * Set urlfoto.
     *
     * @param string|null $urlfoto
     *
     * @return Aulas
     */
    public function setUrlfoto($urlfoto = null)
    {
        $this->urlfoto = $urlfoto;

        return $this;
    }

    /**
     * Get urlfoto.
     *
     * @return string|null
     */
    public function getUrlfoto()
    {
        return $this->urlfoto;
    }

    /**
     * Set cagnon.
     *
     * @param bool|null $cagnon
     *
     * @return Aulas
     */
    public function setCagnon($cagnon = null)
    {
        $this->cagnon = $cagnon;

        return $this;
    }

    /**
     * Get cagnon.
     *
     * @return bool|null
     */
    public function getCagnon()
    {
        return $this->cagnon;
    }

    /**
     * Set pizarra.
     *
     * @param bool|null $pizarra
     *
     * @return Aulas
     */
    public function setPizarra($pizarra = null)
    {
        $this->pizarra = $pizarra;

        return $this;
    }

    /**
     * Get pizarra.
     *
     * @return bool|null
     */
    public function getPizarra()
    {
        return $this->pizarra;
    }

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Aulas
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
     * Set ubicacion.
     *
     * @param string|null $ubicacion
     *
     * @return Aulas
     */
    public function setUbicacion($ubicacion = null)
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion.
     *
     * @return string|null
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Aulas
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
     * Set puestos.
     *
     * @param int|null $puestos
     *
     * @return Aulas
     */
    public function setPuestos($puestos = null)
    {
        $this->puestos = $puestos;

        return $this;
    }

    /**
     * Get puestos.
     *
     * @return int|null
     */
    public function getPuestos()
    {
        return $this->puestos;
    }

    /**
     * Set horaresevini.
     *
     * @param bool|null $horaresevini
     *
     * @return Aulas
     */
    public function setHoraresevini($horaresevini = null)
    {
        $this->horaresevini = $horaresevini;

        return $this;
    }

    /**
     * Get horaresevini.
     *
     * @return bool|null
     */
    public function getHoraresevini()
    {
        return $this->horaresevini;
    }

    /**
     * Set horaresevfin.
     *
     * @param bool|null $horaresevfin
     *
     * @return Aulas
     */
    public function setHoraresevfin($horaresevfin = null)
    {
        $this->horaresevfin = $horaresevfin;

        return $this;
    }

    /**
     * Get horaresevfin.
     *
     * @return bool|null
     */
    public function getHoraresevfin()
    {
        return $this->horaresevfin;
    }

    /**
     * Set modomul.
     *
     * @param bool $modomul
     *
     * @return Aulas
     */
    public function setModomul($modomul)
    {
        $this->modomul = $modomul;

        return $this;
    }

    /**
     * Get modomul.
     *
     * @return bool
     */
    public function getModomul()
    {
        return $this->modomul;
    }

    /**
     * Set ipmul.
     *
     * @param string $ipmul
     *
     * @return Aulas
     */
    public function setIpmul($ipmul)
    {
        $this->ipmul = $ipmul;

        return $this;
    }

    /**
     * Get ipmul.
     *
     * @return string
     */
    public function getIpmul()
    {
        return $this->ipmul;
    }

    /**
     * Set pormul.
     *
     * @param int $pormul
     *
     * @return Aulas
     */
    public function setPormul($pormul)
    {
        $this->pormul = $pormul;

        return $this;
    }

    /**
     * Get pormul.
     *
     * @return int
     */
    public function getPormul()
    {
        return $this->pormul;
    }

    /**
     * Set velmul.
     *
     * @param int $velmul
     *
     * @return Aulas
     */
    public function setVelmul($velmul)
    {
        $this->velmul = $velmul;

        return $this;
    }

    /**
     * Get velmul.
     *
     * @return int
     */
    public function getVelmul()
    {
        return $this->velmul;
    }

    /**
     * Set router.
     *
     * @param string|null $router
     *
     * @return Aulas
     */
    public function setRouter($router = null)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get router.
     *
     * @return string|null
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set netmask.
     *
     * @param string|null $netmask
     *
     * @return Aulas
     */
    public function setNetmask($netmask = null)
    {
        $this->netmask = $netmask;

        return $this;
    }

    /**
     * Get netmask.
     *
     * @return string|null
     */
    public function getNetmask()
    {
        return $this->netmask;
    }

    /**
     * Set dns.
     *
     * @param string|null $dns
     *
     * @return Aulas
     */
    public function setDns($dns = null)
    {
        $this->dns = $dns;

        return $this;
    }

    /**
     * Get dns.
     *
     * @return string|null
     */
    public function getDns()
    {
        return $this->dns;
    }

    /**
     * Set proxy.
     *
     * @param string|null $proxy
     *
     * @return Aulas
     */
    public function setProxy($proxy = null)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy.
     *
     * @return string|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Set ntp.
     *
     * @param string|null $ntp
     *
     * @return Aulas
     */
    public function setNtp($ntp = null)
    {
        $this->ntp = $ntp;

        return $this;
    }

    /**
     * Get ntp.
     *
     * @return string|null
     */
    public function getNtp()
    {
        return $this->ntp;
    }

    /**
     * Set modp2p.
     *
     * @param string|null $modp2p
     *
     * @return Aulas
     */
    public function setModp2p($modp2p = null)
    {
        $this->modp2p = $modp2p;

        return $this;
    }

    /**
     * Get modp2p.
     *
     * @return string|null
     */
    public function getModp2p()
    {
        return $this->modp2p;
    }

    /**
     * Set timep2p.
     *
     * @param int $timep2p
     *
     * @return Aulas
     */
    public function setTimep2p($timep2p)
    {
        $this->timep2p = $timep2p;

        return $this;
    }

    /**
     * Get timep2p.
     *
     * @return int
     */
    public function getTimep2p()
    {
        return $this->timep2p;
    }

    /**
     * Set validacion.
     *
     * @param bool|null $validacion
     *
     * @return Aulas
     */
    public function setValidacion($validacion = null)
    {
        $this->validacion = $validacion;

        return $this;
    }

    /**
     * Get validacion.
     *
     * @return bool|null
     */
    public function getValidacion()
    {
        return $this->validacion;
    }

    /**
     * Set paginalogin.
     *
     * @param string|null $paginalogin
     *
     * @return Aulas
     */
    public function setPaginalogin($paginalogin = null)
    {
        $this->paginalogin = $paginalogin;

        return $this;
    }

    /**
     * Get paginalogin.
     *
     * @return string|null
     */
    public function getPaginalogin()
    {
        return $this->paginalogin;
    }

    /**
     * Set paginavalidacion.
     *
     * @param string|null $paginavalidacion
     *
     * @return Aulas
     */
    public function setPaginavalidacion($paginavalidacion = null)
    {
        $this->paginavalidacion = $paginavalidacion;

        return $this;
    }

    /**
     * Get paginavalidacion.
     *
     * @return string|null
     */
    public function getPaginavalidacion()
    {
        return $this->paginavalidacion;
    }

    /**
     * Set inremotepc.
     *
     * @param bool|null $inremotepc
     *
     * @return Aulas
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
     * Set oglivedir.
     *
     * @param string $oglivedir
     *
     * @return Aulas
     */
    public function setOglivedir($oglivedir)
    {
        $this->oglivedir = $oglivedir;

        return $this;
    }

    /**
     * Get oglivedir.
     *
     * @return string
     */
    public function getOglivedir()
    {
        return $this->oglivedir;
    }

    /**
     * Get idaula.
     *
     * @return int
     */
    public function getIdaula()
    {
        return $this->idaula;
    }
}
