<?php

namespace Opengnsys\CoreBundle\Entity;

use FOS\UserBundle\Model\User as FOSBaseUser;
use FOS\UserBundle\Model\UserInterface as FOSUserInterface;

/**
 * User
 */
class User extends FOSBaseUser implements FOSUserInterface
{
    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * Fecha creación
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Fecha acutalización
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var
     */
    protected $groups;

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
     * PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
        $this->updatedAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
    }

    /**
     * PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->organizationalUnits = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setPlainPassword($password)
    {
        $this->password = null;
        $this->plainPassword = $password;

        return $this;
    }

    public function setEmail($email){
        $this->email = $email;
        $this->username = ($this->username == null)? $email:$this->username;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     *
     * @return User
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Dealer
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Dealer
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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
