<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * OrganizationalUnit
 */
class OrganizationalUnit extends BaseEntity
{
    
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $comments;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $clients;

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
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name.
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return OrganizationalUnit
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set comments.
     *
     * @param string|null $comments
     *
     * @return OrganizationalUnit
     */
    public function setComments($comments = null)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add client.
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
     * Remove client.
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeClient(\Opengnsys\ServerBundle\Entity\Client $client)
    {
        return $this->clients->removeElement($client);
    }

    /**
     * Get clients.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Add child.
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
     * Remove child.
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $child
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeChild(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $child)
    {
        return $this->children->removeElement($child);
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent.
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit|null $parent
     *
     * @return OrganizationalUnit
     */
    public function setParent(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Opengnsys\ServerBundle\Entity\OrganizationalUnit|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set validationSettings.
     *
     * @param \Opengnsys\ServerBundle\Entity\ValidationSettings|null $validationSettings
     *
     * @return OrganizationalUnit
     */
    public function setValidationSettings(\Opengnsys\ServerBundle\Entity\ValidationSettings $validationSettings = null)
    {
        $this->validationSettings = $validationSettings;

        return $this;
    }

    /**
     * Get validationSettings.
     *
     * @return \Opengnsys\ServerBundle\Entity\ValidationSettings|null
     */
    public function getValidationSettings()
    {
        return $this->validationSettings;
    }

    /**
     * Set networkSettings.
     *
     * @param \Opengnsys\ServerBundle\Entity\NetworkSettings|null $networkSettings
     *
     * @return OrganizationalUnit
     */
    public function setNetworkSettings(\Opengnsys\ServerBundle\Entity\NetworkSettings $networkSettings = null)
    {
        $this->networkSettings = $networkSettings;

        return $this;
    }

    /**
     * Get networkSettings.
     *
     * @return \Opengnsys\ServerBundle\Entity\NetworkSettings|null
     */
    public function getNetworkSettings()
    {
        return $this->networkSettings;
    }
}
