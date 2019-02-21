<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Usuarios
 */
class Usuarios
{
    /**
     * @var string
     */
    private $usuario = '';

    /**
     * @var string
     */
    private $pasguor = '';

    /**
     * @var string|null
     */
    private $nombre;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var int|null
     */
    private $ididioma;

    /**
     * @var bool|null
     */
    private $idtipousuario;

    /**
     * @var string
     */
    private $apikey = '';

    /**
     * @var int
     */
    private $idusuario;


    /**
     * Set usuario.
     *
     * @param string $usuario
     *
     * @return Usuarios
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario.
     *
     * @return string
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set pasguor.
     *
     * @param string $pasguor
     *
     * @return Usuarios
     */
    public function setPasguor($pasguor)
    {
        $this->pasguor = $pasguor;

        return $this;
    }

    /**
     * Get pasguor.
     *
     * @return string
     */
    public function getPasguor()
    {
        return $this->pasguor;
    }

    /**
     * Set nombre.
     *
     * @param string|null $nombre
     *
     * @return Usuarios
     */
    public function setNombre($nombre = null)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre.
     *
     * @return string|null
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return Usuarios
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set ididioma.
     *
     * @param int|null $ididioma
     *
     * @return Usuarios
     */
    public function setIdidioma($ididioma = null)
    {
        $this->ididioma = $ididioma;

        return $this;
    }

    /**
     * Get ididioma.
     *
     * @return int|null
     */
    public function getIdidioma()
    {
        return $this->ididioma;
    }

    /**
     * Set idtipousuario.
     *
     * @param bool|null $idtipousuario
     *
     * @return Usuarios
     */
    public function setIdtipousuario($idtipousuario = null)
    {
        $this->idtipousuario = $idtipousuario;

        return $this;
    }

    /**
     * Get idtipousuario.
     *
     * @return bool|null
     */
    public function getIdtipousuario()
    {
        return $this->idtipousuario;
    }

    /**
     * Set apikey.
     *
     * @param string $apikey
     *
     * @return Usuarios
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
     * Get idusuario.
     *
     * @return int
     */
    public function getIdusuario()
    {
        return $this->idusuario;
    }
}
