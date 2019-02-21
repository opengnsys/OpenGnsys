<?php

namespace Opengnsys\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trace
 */
class Trace extends BaseEntity
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
     * @var integer
     */
    private $status;

    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $error;

    /**
     * @var \DateTime
     */
    private $executedAt;

    /**
     * @var \DateTime
     */
    private $finishedAt;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Client
     */
    private $client;

    /**
     * @var \Opengnsys\ServerBundle\Entity\User
     */
    private $doneBy;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Trace
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
     * @return Trace
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
     * Set status
     *
     * @param integer $status
     *
     * @return Trace
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set executedAt
     *
     * @param \DateTime $executedAt
     *
     * @return Trace
     */
    public function setExecutedAt($executedAt)
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    /**
     * Get executedAt
     *
     * @return \DateTime
     */
    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    /**
     * Set finishedAt
     *
     * @param \DateTime $finishedAt
     *
     * @return Trace
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * Get finishedAt
     *
     * @return \DateTime
     */
    public function getFinishedAt()
    {
        return $this->finishedAt;
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
     * @return Trace
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

    /**
     * Set doneBy
     *
     * @param \Opengnsys\ServerBundle\Entity\User $doneBy
     *
     * @return Trace
     */
    public function setDoneBy(\Opengnsys\ServerBundle\Entity\User $doneBy = null)
    {
        $this->doneBy = $doneBy;

        return $this;
    }

    /**
     * Get doneBy
     *
     * @return \Opengnsys\ServerBundle\Entity\User
     */
    public function getDoneBy()
    {
        return $this->doneBy;
    }

    /**
     * Set output
     *
     * @param string $output
     *
     * @return Trace
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
    /**
     * @var string
     */
    private $commandType;


    /**
     * Set commandType
     *
     * @param string $commandType
     *
     * @return Trace
     */
    public function setCommandType($commandType)
    {
        $this->commandType = $commandType;

        return $this;
    }

    /**
     * Get commandType
     *
     * @return string
     */
    public function getCommandType()
    {
        return $this->commandType;
    }

    /**
     * Set error
     *
     * @param string $error
     *
     * @return Trace
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
