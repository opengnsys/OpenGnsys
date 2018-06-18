<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Task
 */
class Task
{
    
    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $script;

    /**
     * @var json_array
     */
    private $data;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Client
     */
    private $client;


    /**
     * Set command
     *
     * @param string $command
     *
     * @return Task
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set script
     *
     * @param string $script
     *
     * @return Task
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
     * Set data
     *
     * @param $data
     *
     * @return Task
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return \ json_array
     */
    public function getData()
    {
        return $this->data;
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
     * Set client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return Task
     */
    public function setClient(\Opengnsys\ServerBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Opengnsys\ServerBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
