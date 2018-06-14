<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Repository
 */
class Repository
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $configurationpath;

    /**
     * @var string
     */
    private $adminpath;

    /**
     * @var string
     */
    private $pxepath;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Group
     */
    private $group;


    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set ip
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
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Repository
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set configurationpath
     *
     * @param string $configurationpath
     *
     * @return Repository
     */
    public function setConfigurationpath($configurationpath)
    {
        $this->configurationpath = $configurationpath;

        return $this;
    }

    /**
     * Get configurationpath
     *
     * @return string
     */
    public function getConfigurationpath()
    {
        return $this->configurationpath;
    }

    /**
     * Set adminpath
     *
     * @param string $adminpath
     *
     * @return Repository
     */
    public function setAdminpath($adminpath)
    {
        $this->adminpath = $adminpath;

        return $this;
    }

    /**
     * Get adminpath
     *
     * @return string
     */
    public function getAdminpath()
    {
        return $this->adminpath;
    }

    /**
     * Set pxepath
     *
     * @param string $pxepath
     *
     * @return Repository
     */
    public function setPxepath($pxepath)
    {
        $this->pxepath = $pxepath;

        return $this;
    }

    /**
     * Get pxepath
     *
     * @return string
     */
    public function getPxepath()
    {
        return $this->pxepath;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Repository
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
     * Set port
     *
     * @param integer $port
     *
     * @return Repository
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
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
     * @return Repository
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
     * Set group
     *
     * @param \Opengnsys\ServerBundle\Entity\Group $group
     *
     * @return Repository
     */
    public function setGroup(\Opengnsys\ServerBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \Opengnsys\ServerBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
