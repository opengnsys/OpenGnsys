<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Client
 */
class Client extends BaseEntity
{

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $serialno;

    /**
     * @var string|null
     */
    private $netiface;

    /**
     * @var string
     */
    private $netdriver;

    /**
     * @var string|null
     */
    private $mac;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $cache;

    /**
     * @var int
     */
    private $idproautoexec;

    /**
     * @var string|null
     */
    private $oglive;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $partitions;

    /**
     * @var \Opengnsys\ServerBundle\Entity\ValidationSettings
     */
    private $validationSettings;

    /**
     * @var \Opengnsys\ServerBundle\Entity\HardwareProfile
     */
    private $hardwareProfile;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Menu
     */
    private $menu;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Repository
     */
    private $repository;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Netboot
     */
    private $netboot;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->partitions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return Client
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set serialno.
     *
     * @param string|null $serialno
     *
     * @return Client
     */
    public function setSerialno($serialno = null)
    {
        $this->serialno = $serialno;

        return $this;
    }

    /**
     * Get serialno.
     *
     * @return string|null
     */
    public function getSerialno()
    {
        return $this->serialno;
    }

    /**
     * Set netiface.
     *
     * @param string|null $netiface
     *
     * @return Client
     */
    public function setNetiface($netiface = null)
    {
        $this->netiface = $netiface;

        return $this;
    }

    /**
     * Get netiface.
     *
     * @return string|null
     */
    public function getNetiface()
    {
        return $this->netiface;
    }

    /**
     * Set netdriver.
     *
     * @param string $netdriver
     *
     * @return Client
     */
    public function setNetdriver($netdriver)
    {
        $this->netdriver = $netdriver;

        return $this;
    }

    /**
     * Get netdriver.
     *
     * @return string
     */
    public function getNetdriver()
    {
        return $this->netdriver;
    }

    /**
     * Set mac.
     *
     * @param string|null $mac
     *
     * @return Client
     */
    public function setMac($mac = null)
    {
        $this->mac = $mac;

        return $this;
    }

    /**
     * Get mac.
     *
     * @return string|null
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * Set ip.
     *
     * @param string $ip
     *
     * @return Client
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
     * Set status.
     *
     * @param string|null $status
     *
     * @return Client
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set cache.
     *
     * @param int|null $cache
     *
     * @return Client
     */
    public function setCache($cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get cache.
     *
     * @return int|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set idproautoexec.
     *
     * @param int $idproautoexec
     *
     * @return Client
     */
    public function setIdproautoexec($idproautoexec)
    {
        $this->idproautoexec = $idproautoexec;

        return $this;
    }

    /**
     * Get idproautoexec.
     *
     * @return int
     */
    public function getIdproautoexec()
    {
        return $this->idproautoexec;
    }

    /**
     * Set oglive.
     *
     * @param string|null $oglive
     *
     * @return Client
     */
    public function setOglive($oglive = null)
    {
        $this->oglive = $oglive;

        return $this;
    }

    /**
     * Get oglive.
     *
     * @return string|null
     */
    public function getOglive()
    {
        return $this->oglive;
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
     * Add partition.
     *
     * @param \Opengnsys\ServerBundle\Entity\Partition $partition
     *
     * @return Client
     */
    public function addPartition(\Opengnsys\ServerBundle\Entity\Partition $partition)
    {
        $this->partitions[] = $partition;

        return $this;
    }

    /**
     * Remove partition.
     *
     * @param \Opengnsys\ServerBundle\Entity\Partition $partition
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePartition(\Opengnsys\ServerBundle\Entity\Partition $partition)
    {
        return $this->partitions->removeElement($partition);
    }

    /**
     * Get partitions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartitions()
    {
        return $this->partitions;
    }

    /**
     * Set validationSettings.
     *
     * @param \Opengnsys\ServerBundle\Entity\ValidationSettings|null $validationSettings
     *
     * @return Client
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
     * Set hardwareProfile.
     *
     * @param \Opengnsys\ServerBundle\Entity\HardwareProfile|null $hardwareProfile
     *
     * @return Client
     */
    public function setHardwareProfile(\Opengnsys\ServerBundle\Entity\HardwareProfile $hardwareProfile = null)
    {
        $this->hardwareProfile = $hardwareProfile;

        return $this;
    }

    /**
     * Get hardwareProfile.
     *
     * @return \Opengnsys\ServerBundle\Entity\HardwareProfile|null
     */
    public function getHardwareProfile()
    {
        return $this->hardwareProfile;
    }

    /**
     * Set menu.
     *
     * @param \Opengnsys\ServerBundle\Entity\Menu|null $menu
     *
     * @return Client
     */
    public function setMenu(\Opengnsys\ServerBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu.
     *
     * @return \Opengnsys\ServerBundle\Entity\Menu|null
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set repository.
     *
     * @param \Opengnsys\ServerBundle\Entity\Repository|null $repository
     *
     * @return Client
     */
    public function setRepository(\Opengnsys\ServerBundle\Entity\Repository $repository = null)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository.
     *
     * @return \Opengnsys\ServerBundle\Entity\Repository|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set organizationalUnit.
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit|null $organizationalUnit
     *
     * @return Client
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

    /**
     * Set netboot.
     *
     * @param \Opengnsys\ServerBundle\Entity\Netboot|null $netboot
     *
     * @return Client
     */
    public function setNetboot(\Opengnsys\ServerBundle\Entity\Netboot $netboot = null)
    {
        $this->netboot = $netboot;

        return $this;
    }

    /**
     * Get netboot.
     *
     * @return \Opengnsys\ServerBundle\Entity\Netboot|null
     */
    public function getNetboot()
    {
        return $this->netboot;
    }
}
