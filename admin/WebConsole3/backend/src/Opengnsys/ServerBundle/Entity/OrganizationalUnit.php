<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * OrganizationalUnit
 */
class OrganizationalUnit
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $urlphoto;

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
    private $children;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $parent;

    /**
     * @var \Opengnsys\ServerBundle\Entity\ValidationSettings
     */
    private $validationSettings;

    /**
     * @var \Opengnsys\ServerBundle\Entity\NetworkSettings
     */
    private $networkSettings;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return OrganizationalUnit
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
     * Set description
     *
     * @param string $description
     *
     * @return OrganizationalUnit
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set urlphoto
     *
     * @param string $urlphoto
     *
     * @return OrganizationalUnit
     */
    public function setUrlphoto($urlphoto)
    {
        $this->urlphoto = $urlphoto;

        return $this;
    }

    /**
     * Get urlphoto
     *
     * @return string
     */
    public function getUrlphoto()
    {
        return $this->urlphoto;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return OrganizationalUnit
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
     * Add child
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $child
     *
     * @return OrganizationalUnit
     */
    public function addChild(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $child
     */
    public function removeChild(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $parent
     *
     * @return OrganizationalUnit
     */
    public function setParent(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set validationSettings
     *
     * @param \Opengnsys\ServerBundle\Entity\ValidationSettings $validationSettings
     *
     * @return OrganizationalUnit
     */
    public function setValidationSettings(\Opengnsys\ServerBundle\Entity\ValidationSettings $validationSettings = null)
    {
        $this->validationSettings = $validationSettings;

        return $this;
    }

    /**
     * Get validationSettings
     *
     * @return \Opengnsys\ServerBundle\Entity\ValidationSettings
     */
    public function getValidationSettings()
    {
        return $this->validationSettings;
    }

    /**
     * Set networkSettings
     *
     * @param \Opengnsys\ServerBundle\Entity\NetworkSettings $networkSettings
     *
     * @return OrganizationalUnit
     */
    public function setNetworkSettings(\Opengnsys\ServerBundle\Entity\NetworkSettings $networkSettings = null)
    {
        $this->networkSettings = $networkSettings;

        return $this;
    }

    /**
     * Get networkSettings
     *
     * @return \Opengnsys\ServerBundle\Entity\NetworkSettings
     */
    public function getNetworkSettings()
    {
        return $this->networkSettings;
    }


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $clients;


    /**
     * Add client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return OrganizationalUnit
     */
    public function addClient(\Opengnsys\ServerBundle\Entity\Client $client)
    {
        $this->clients[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     */
    public function removeClient(\Opengnsys\ServerBundle\Entity\Client $client)
    {
        $this->clients->removeElement($client);
    }

    /**
     * Get clients
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClients()
    {
        return $this->clients;
    }
}
