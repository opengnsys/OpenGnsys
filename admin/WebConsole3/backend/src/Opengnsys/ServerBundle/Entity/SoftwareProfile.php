<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * SoftwareProfile
 */
class SoftwareProfile extends BaseEntity
{

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $softwares;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->softwares = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return SoftwareProfile
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
     * Set comments
     *
     * @param string $comments
     *
     * @return SoftwareProfile
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
     * Set organizationalUnit
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit
     *
     * @return SoftwareProfile
     */
    public function setOrganizationalUnit(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit = null)
    {
        $this->organizationalUnit = $organizationalUnit;

        return $this;
    }

    /**
     * Get organizationalUnit
     *
     * @return \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    public function getOrganizationalUnit()
    {
        return $this->organizationalUnit;
    }

    /**
     * Add software
     *
     * @param \Opengnsys\ServerBundle\Entity\Software $software
     *
     * @return SoftwareProfile
     */
    public function addSoftware(\Opengnsys\ServerBundle\Entity\Software $software)
    {
        $this->softwares[] = $software;

        return $this;
    }

    /**
     * Remove software
     *
     * @param \Opengnsys\ServerBundle\Entity\Software $software
     */
    public function removeSoftware(\Opengnsys\ServerBundle\Entity\Software $software)
    {
        $this->softwares->removeElement($software);
    }

    /**
     * Get softwares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSoftwares()
    {
        return $this->softwares;
    }
}
