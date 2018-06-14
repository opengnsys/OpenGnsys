<?php

namespace Opengnsys\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 */
class Client
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $serialno;

    /**
     * @var string
     */
    private $netiface;

    /**
     * @var string
     */
    private $netdriver;

    /**
     * @var string
     */
    private $mac;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $status;

    /**
     * @var integer
     */
    private $cache;

    /**
     * @var integer
     */
    private $idproautoexec;

    /**
     * @var string
     */
    private $urlphoto;

    /**
     * @var integer
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
     * @var string
     */
    private $oglive;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Netboot
     */
    private $netboot;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Repository
     */
    private $repository;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->partitions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Client
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
     * Set serialno
     *
     * @param string $serialno
     *
     * @return Client
     */
    public function setSerialno($serialno)
    {
        $this->serialno = $serialno;

        return $this;
    }

    /**
     * Get serialno
     *
     * @return string
     */
    public function getSerialno()
    {
        return $this->serialno;
    }

    /**
     * Set netiface
     *
     * @param string $netiface
     *
     * @return Client
     */
    public function setNetiface($netiface)
    {
        $this->netiface = $netiface;

        return $this;
    }

    /**
     * Get netiface
     *
     * @return string
     */
    public function getNetiface()
    {
        return $this->netiface;
    }

    /**
     * Set netdriver
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
     * Get netdriver
     *
     * @return string
     */
    public function getNetdriver()
    {
        return $this->netdriver;
    }

    /**
     * Set mac
     *
     * @param string $mac
     *
     * @return Client
     */
    public function setMac($mac)
    {
        $this->mac = $mac;

        return $this;
    }

    /**
     * Get mac
     *
     * @return string
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * Set ip
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
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set cache
     *
     * @param integer $cache
     *
     * @return Client
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get cache
     *
     * @return integer
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set idproautoexec
     *
     * @param integer $idproautoexec
     *
     * @return Client
     */
    public function setIdproautoexec($idproautoexec)
    {
        $this->idproautoexec = $idproautoexec;

        return $this;
    }

    /**
     * Get idproautoexec
     *
     * @return integer
     */
    public function getIdproautoexec()
    {
        return $this->idproautoexec;
    }

    /**
     * Set urlphoto
     *
     * @param string $urlphoto
     *
     * @return Client
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add partition
     *
     * @param \Opengnsys\ServerBundle\Entity\Partition $partition
     *
     * @return Client
     */
    public function addPartition(\Opengnsys\ServerBundle\Entity\Partition $partition)
    {
        $this->partitions[] = $partition;
        $partition->setClient($this);

        return $this;
    }

    /**
     * Remove partition
     *
     * @param \Opengnsys\ServerBundle\Entity\Partition $partition
     */
    public function removePartition(\Opengnsys\ServerBundle\Entity\Partition $partition)
    {
        $this->partitions->removeElement($partition);
    }

    /**
     * Get partitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartitions()
    {
        return $this->partitions;
    }

    /**
     * Get partitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartition($key)
    {
        $partition = null;
        if(!$partition = $this->partitions->get($key)){
            $partition = new Partition();
            $partition->setClient($this);
            $this->partitions[$key] = $partition;
        }
        return $partition;
    }

    /**
     * Set validationSettings
     *
     * @param \Opengnsys\ServerBundle\Entity\ValidationSettings $validationSettings
     *
     * @return Client
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
     * Set hardwareProfile
     *
     * @param \Opengnsys\ServerBundle\Entity\HardwareProfile $hardwareProfile
     *
     * @return Client
     */
    public function setHardwareProfile(\Opengnsys\ServerBundle\Entity\HardwareProfile $hardwareProfile = null)
    {
        $this->hardwareProfile = $hardwareProfile;

        return $this;
    }

    /**
     * Get hardwareProfile
     *
     * @return \Opengnsys\ServerBundle\Entity\HardwareProfile
     */
    public function getHardwareProfile()
    {
        return $this->hardwareProfile;
    }

    /**
     * Set menu
     *
     * @param \Opengnsys\ServerBundle\Entity\Menu $menu
     *
     * @return Client
     */
    public function setMenu(\Opengnsys\ServerBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return \Opengnsys\ServerBundle\Entity\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set repository
     *
     * @param \Opengnsys\ServerBundle\Entity\Repository $repository
     *
     * @return Client
     */
    public function setRepository(\Opengnsys\ServerBundle\Entity\Repository $repository = null)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Get repository
     *
     * @return \Opengnsys\ServerBundle\Entity\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set organizationalUnit
     *
     * @param \Opengnsys\ServerBundle\Entity\OrganizationalUnit $organizationalUnit
     *
     * @return Client
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
     * Set status
     *
     * @param string $status
     *
     * @return Client
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set oglive
     *
     * @param string $oglive
     *
     * @return Client
     */
    public function setOglive($oglive)
    {
        $this->oglive = $oglive;

        return $this;
    }

    /**
     * Get oglive
     *
     * @return string
     */
    public function getOglive()
    {
        return $this->oglive;
    }

    /**
     * Set netboot
     *
     * @param \Opengnsys\ServerBundle\Entity\Netboot $netboot
     *
     * @return Client
     */
    public function setNetboot(\Opengnsys\ServerBundle\Entity\Netboot $netboot = null)
    {
        $this->netboot = $netboot;

        return $this;
    }

    /**
     * Get netboot
     *
     * @return \Opengnsys\ServerBundle\Entity\Netboot
     */
    public function getNetboot()
    {
        return $this->netboot;
    }
}
