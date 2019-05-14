<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Repository
 */
class Repository extends BaseEntity
{
    /**
     * @var string
     */
    private $ip;

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
    private $randomId;

    /**
     * @var string|null
     */
    private $secret;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;


    /**
     * Set ip.
     *
     * @param string $ip
     *
     * @return Repository
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Repository
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
     * @return Repository
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
     * Set randomId.
     *
     * @param string|null $randomId
     *
     * @return Repository
     */
    public function setRandomId($randomId = null)
    {
        $this->randomId = $randomId;

        return $this;
    }

    /**
     * Get randomId.
     *
     * @return string|null
     */
    public function getRandomId()
    {
        return $this->randomId;
    }

    /**
     * Set secret.
     *
     * @param string|null $secret
     *
     * @return Repository
     */
    public function setSecret($secret = null)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret.
     *
     * @return string|null
     */
    public function getSecret()
    {
        return $this->secret;
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
     * Set organizationalUnit.
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit|null $organizationalUnit
     *
     * @return Repository
     */
    public function setOrganizationalUnit(\Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit = null)
    {
        $this->organizationalUnit = $organizationalUnit;

        return $this;
    }

    /**
     * Get organizationalUnit.
     *
     * @return \Opengnsys\ServerBundle\Entity\OrganizationalUnit|null
     */
    public function getOrganizationalUnit()
    {
        return $this->organizationalUnit;
    }
}
