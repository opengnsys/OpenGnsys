<?php

namespace Opengnsys\ServerBundle\Entity;

use Globunet\UserBundle\Entity\BaseUser;
use Globunet\UserBundle\Model\UserInterface;

/**
 * User
 */
class User extends BaseUser implements UserInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $organizationalUnits;

    /**
     * @var json_array
     */
    private $profile;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->organizationalUnits = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add organizationalUnit
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit
     *
     * @return User
     */
    public function addOrganizationalUnit(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit)
    {
        $this->organizationalUnits[] = $organizationalUnit;

        return $this;
    }

    /**
     * Remove organizationalUnit
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit
     */
    public function removeOrganizationalUnit(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit)
    {
        $this->organizationalUnits->removeElement($organizationalUnit);
    }

    /**
     * Get organizationalUnits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrganizationalUnits()
    {
        return $this->organizationalUnits;
    }

    /**
     * Set profile
     *
     * @param \json_array $profile
     *
     * @return User
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \json_array
     */
    public function getProfile()
    {
        return $this->profile;
    }
}
