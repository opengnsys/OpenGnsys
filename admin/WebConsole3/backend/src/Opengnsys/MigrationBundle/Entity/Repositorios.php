<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Repositorios
 */
class Repositorios
{
    /**
     * @var string
     */
    private $nombrerepositorio;

    /**
     * @var string
     */
    private $ip = '';

    /**
     * @var string
     */
    private $passguor = '';

    /**
     * @var int|null
     */
    private $idcentro;

    /**
     * @var int|null
     */
    private $grupoid;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int
     */
    private $puertorepo;

    /**
     * @var string
     */
    private $apikey = '';

    /**
     * @var int
     */
    private $idrepositorio;


    /**
     * Set nombrerepositorio.
     *
     * @param string $nombrerepositorio
     *
     * @return Repositorios
     */
    public function setNombrerepositorio($nombrerepositorio)
    {
        $this->nombrerepositorio = $nombrerepositorio;

        return $this;
    }

    /**
     * Get nombrerepositorio.
     *
     * @return string
     */
    public function getNombrerepositorio()
    {
        return $this->nombrerepositorio;
    }

    /**
     * Set ip.
     *
     * @param string $ip
     *
     * @return Repositorios
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
     * Set passguor.
     *
     * @param string $passguor
     *
     * @return Repositorios
     */
    public function setPassguor($passguor)
    {
        $this->passguor = $passguor;

        return $this;
    }

    /**
     * Get passguor.
     *
     * @return string
     */
    public function getPassguor()
    {
        return $this->passguor;
    }

    /**
     * Set idcentro.
     *
     * @param int|null $idcentro
     *
     * @return Repositorios
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
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Repositorios
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
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Repositorios
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
     * Set puertorepo.
     *
     * @param int $puertorepo
     *
     * @return Repositorios
     */
    public function setPuertorepo($puertorepo)
    {
        $this->puertorepo = $puertorepo;

        return $this;
    }

    /**
     * Get puertorepo.
     *
     * @return int
     */
    public function getPuertorepo()
    {
        return $this->puertorepo;
    }

    /**
     * Set apikey.
     *
     * @param string $apikey
     *
     * @return Repositorios
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * Get apikey.
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
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
}
