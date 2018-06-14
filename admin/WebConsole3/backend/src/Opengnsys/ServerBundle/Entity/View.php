<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * View
 */
class View
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Client;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Client = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return View
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return View
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
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
     * Add client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return View
     */
    public function addClient(\Opengnsys\ServerBundle\Entity\Client $client)
    {
        $this->Client[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     */
    public function removeClient(\Opengnsys\ServerBundle\Entity\Client $client)
    {
        $this->Client->removeElement($client);
    }

    /**
     * Get client
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClient()
    {
        return $this->Client;
    }
}
