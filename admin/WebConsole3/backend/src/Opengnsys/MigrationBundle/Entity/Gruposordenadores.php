<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Gruposordenadores
 */
class Gruposordenadores
{
    /**
     * @var string
     */
    private $nombregrupoordenador = '';

    /**
     * @var int
     */
    private $idaula = '0';

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
    private $idgrupo;


    /**
     * Set nombregrupoordenador.
     *
     * @param string $nombregrupoordenador
     *
     * @return Gruposordenadores
     */
    public function setNombregrupoordenador($nombregrupoordenador)
    {
        $this->nombregrupoordenador = $nombregrupoordenador;

        return $this;
    }

    /**
     * Get nombregrupoordenador.
     *
     * @return string
     */
    public function getNombregrupoordenador()
    {
        return $this->nombregrupoordenador;
    }

    /**
     * Set idaula.
     *
     * @param int $idaula
     *
     * @return Gruposordenadores
     */
    public function setIdaula($idaula)
    {
        $this->idaula = $idaula;

        return $this;
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

    /**
     * Set grupoid.
     *
     * @param int|null $grupoid
     *
     * @return Gruposordenadores
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
     * @return Gruposordenadores
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
