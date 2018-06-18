<?php

namespace Opengnsys\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Command
 */
class Command
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $script;

    /**
     * @var boolean
     */
    private $parameters;

    /**
     * @var integer
     */
    private $id;

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Command
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set script
     *
     * @param string $script
     *
     * @return Command
     */
    public function setScript($script)
    {
        $this->script = $script;

        return $this;
    }

    /**
     * Get script
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Set parameters
     *
     * @param boolean $parameters
     *
     * @return Command
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return boolean
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $type;


    /**
     * Set type
     *
     * @param string $type
     *
     * @return Command
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
