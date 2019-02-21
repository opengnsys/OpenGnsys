<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Grupos
 */
class Grupos
{
    /**
     * @var string
     */
    private $nombregrupo = '';

    /**
     * @var int
     */
    private $grupoid = '0';

    /**
     * @var bool
     */
    private $tipo = '0';

    /**
     * @var int
     */
    private $idcentro = '0';

    /**
     * @var int|null
     */
    private $iduniversidad;

    /**
     * @var string|null
     */
    private $comentarios;

    /**
     * @var int
     */
    private $idgrupo;


    /**
     * Set nombregrupo.
     *
     * @param string $nombregrupo
     *
     * @return Grupos
     */
    public function setNombregrupo($nombregrupo)
    {
        $this->nombregrupo = $nombregrupo;

        return $this;
    }

    /**
     * Get nombregrupo.
     *
     * @return string
     */
    public function getNombregrupo()
    {
        return $this->nombregrupo;
    }

    /**
     * Set grupoid.
     *
     * @param int $grupoid
     *
     * @return Grupos
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
     * Set tipo.
     *
     * @param bool $tipo
     *
     * @return Grupos
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo.
     *
     * @return bool
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set idcentro.
     *
     * @param int $idcentro
     *
     * @return Grupos
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
     * Set iduniversidad.
     *
     * @param int|null $iduniversidad
     *
     * @return Grupos
     */
    public function setIduniversidad($iduniversidad = null)
    {
        $this->iduniversidad = $iduniversidad;

        return $this;
    }

    /**
     * Get iduniversidad.
     *
     * @return int|null
     */
    public function getIduniversidad()
    {
        return $this->iduniversidad;
    }

    /**
     * Set comentarios.
     *
     * @param string|null $comentarios
     *
     * @return Grupos
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
     * Get idgrupo.
     *
     * @return int
     */
    public function getIdgrupo()
    {
        return $this->idgrupo;
    }
}
