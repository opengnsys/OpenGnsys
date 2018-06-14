<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * HardwareProfile
 */
class HardwareProfile
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
    private $hardwares;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hardwares = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return HardwareProfile
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
     * @return HardwareProfile
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
     * @return HardwareProfile
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
     * Add hardware
     *
     * @param \Opengnsys\ServerBundle\Entity\Hardware $hardware
     *
     * @return HardwareProfile
     */
    public function addHardware(\Opengnsys\ServerBundle\Entity\Hardware $hardware)
    {
        $this->hardwares[] = $hardware;

        return $this;
    }

    /**
     * Remove hardware
     *
     * @param \Opengnsys\ServerBundle\Entity\Hardware $hardware
     */
    public function removeHardware(\Opengnsys\ServerBundle\Entity\Hardware $hardware)
    {
        $this->hardwares->removeElement($hardware);
    }

    /**
     * Get hardwares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHardwares()
    {
        return $this->hardwares;
    }
}
