<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Ordenadores
 */
class Ordenadores
{
    /**
     * @var string|null
     */
    private $nombreordenador;

    /**
     * @var string|null
     */
    private $numserie;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string|null
     */
    private $mac;

    /**
     * @var int|null
     */
    private $idaula;

    /**
     * @var int|null
     */
    private $idperfilhard;

    /**
     * @var int|null
     */
    private $idrepositorio;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var int|null
     */
    private $idmenu;

    /**
     * @var int|null
     */
    private $cache;

    /**
     * @var string
     */
    private $router;

    /**
     * @var string
     */
    private $mascara;

    /**
     * @var int
     */
    private $idproautoexec = '0';

    /**
     * @var string
     */
    private $arranque = '00unknown';

    /**
     * @var string|null
     */
    private $netiface = 'eth0';

    /**
     * @var string
     */
    private $netdriver = 'generic';

    /**
     * @var string
     */
    private $fotoord = 'fotoordenador.gif';

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
     * @var string|null
     */
    private $agentkey;

    /**
     * @var string
     */
    private $oglivedir = 'ogLive';

    /**
     * @var int
     */
    private $idordenador;


    /**
     * Set nombreordenador.
     *
     * @param string|null $nombreordenador
     *
     * @return Ordenadores
     */
    public function setNombreordenador($nombreordenador = null)
    {
        $this->nombreordenador = $nombreordenador;

        return $this;
    }

    /**
     * Get nombreordenador.
     *
     * @return string|null
     */
    public function getNombreordenador()
    {
        return $this->nombreordenador;
    }

    /**
     * Set numserie.
     *
     * @param string|null $numserie
     *
     * @return Ordenadores
     */
    public function setNumserie($numserie = null)
    {
        $this->numserie = $numserie;

        return $this;
    }

    /**
     * Get numserie.
     *
     * @return string|null
     */
    public function getNumserie()
    {
        return $this->numserie;
    }

    /**
     * Set ip.
     *
     * @param string $ip
     *
     * @return Ordenadores
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set mac.
     *
     * @param string|null $mac
     *
     * @return Ordenadores
     */
    public function setMac($mac = null)
    {
        $this->mac = $mac;

        return $this;
    }

    /**
     * Get mac.
     *
     * @return string|null
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * Set idaula.
     *
     * @param int|null $idaula
     *
     * @return Ordenadores
     */
    public function setIdaula($idaula = null)
    {
        $this->idaula = $idaula;

        return $this;
    }

    /**
     * Get idaula.
     *
     * @return int|null
     */
    public function getIdaula()
    {
        return $this->idaula;
    }

    /**
     * Set idperfilhard.
     *
     * @param int|null $idperfilhard
     *
     * @return Ordenadores
     */
    public function setIdperfilhard($idperfilhard = null)
    {
        $this->idperfilhard = $idperfilhard;

        return $this;
    }

    /**
     * Get idperfilhard.
     *
     * @return int|null
     */
    public function getIdperfilhard()
    {
        return $this->idperfilhard;
    }

    /**
     * Set idrepositorio.
     *
     * @param int|null $idrepositorio
     *
     * @return Ordenadores
     */
    public function setIdrepositorio($idrepositorio = null)
    {
        $this->idrepositorio = $idrepositorio;

        return $this;
    }

    /**
     * Get idrepositorio.
     *
     * @return int|null
     */
    public function getIdrepositorio()
    {
        return $this->idrepositorio;
    }

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Ordenadores
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
     * Set idmenu.
     *
     * @param int|null $idmenu
     *
     * @return Ordenadores
     */
    public function setIdmenu($idmenu = null)
    {
        $this->idmenu = $idmenu;

        return $this;
    }

    /**
     * Get idmenu.
     *
     * @return int|null
     */
    public function getIdmenu()
    {
        return $this->idmenu;
    }

    /**
     * Set cache.
     *
     * @param int|null $cache
     *
     * @return Ordenadores
     */
    public function setCache($cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get cache.
     *
     * @return int|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set router.
     *
     * @param string $router
     *
     * @return Ordenadores
     */
    public function setRouter($router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get router.
     *
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set mascara.
     *
     * @param string $mascara
     *
     * @return Ordenadores
     */
    public function setMascara($mascara)
    {
        $this->mascara = $mascara;

        return $this;
    }

    /**
     * Get mascara.
     *
     * @return string
     */
    public function getMascara()
    {
        return $this->mascara;
    }

    /**
     * Set idproautoexec.
     *
     * @param int $idproautoexec
     *
     * @return Ordenadores
     */
    public function setIdproautoexec($idproautoexec)
    {
        $this->idproautoexec = $idproautoexec;

        return $this;
    }

    /**
     * Get idproautoexec.
     *
     * @return int
     */
    public function getIdproautoexec()
    {
        return $this->idproautoexec;
    }

    /**
     * Set arranque.
     *
     * @param string $arranque
     *
     * @return Ordenadores
     */
    public function setArranque($arranque)
    {
        $this->arranque = $arranque;

        return $this;
    }

    /**
     * Get arranque.
     *
     * @return string
     */
    public function getArranque()
    {
        return $this->arranque;
    }

    /**
     * Set netiface.
     *
     * @param string|null $netiface
     *
     * @return Ordenadores
     */
    public function setNetiface($netiface = null)
    {
        $this->netiface = $netiface;

        return $this;
    }

    /**
     * Get netiface.
     *
     * @return string|null
     */
    public function getNetiface()
    {
        return $this->netiface;
    }

    /**
     * Set netdriver.
     *
     * @param string $netdriver
     *
     * @return Ordenadores
     */
    public function setNetdriver($netdriver)
    {
        $this->netdriver = $netdriver;

        return $this;
    }

    /**
     * Get netdriver.
     *
     * @return string
     */
    public function getNetdriver()
    {
        return $this->netdriver;
    }

    /**
     * Set fotoord.
     *
     * @param string $fotoord
     *
     * @return Ordenadores
     */
    public function setFotoord($fotoord)
    {
        $this->fotoord = $fotoord;

        return $this;
    }

    /**
     * Get fotoord.
     *
     * @return string
     */
    public function getFotoord()
    {
        return $this->fotoord;
    }

    /**
     * Set validacion.
     *
     * @param bool|null $validacion
     *
     * @return Ordenadores
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
     * @return Ordenadores
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
     * @return Ordenadores
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
     * Set agentkey.
     *
     * @param string|null $agentkey
     *
     * @return Ordenadores
     */
    public function setAgentkey($agentkey = null)
    {
        $this->agentkey = $agentkey;

        return $this;
    }

    /**
     * Get agentkey.
     *
     * @return string|null
     */
    public function getAgentkey()
    {
        return $this->agentkey;
    }

    /**
     * Set oglivedir.
     *
     * @param string $oglivedir
     *
     * @return Ordenadores
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
     * Get idordenador.
     *
     * @return int
     */
    public function getIdordenador()
    {
        return $this->idordenador;
    }
}
