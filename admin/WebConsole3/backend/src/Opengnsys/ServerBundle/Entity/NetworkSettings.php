<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * NetworkSettings
 */
class NetworkSettings
{
    
    
    /**
     * @var string
     */
    private $proxy;

    /**
     * @var string
     */
    private $dns;

    /**
     * @var string
     */
    private $netmask;

    /**
     * @var string
     */
    private $router;

    /**
     * @var string
     */
    private $ntp;

    /**
     * @var integer
     */
    private $p2pTime;

    /**
     * @var string
     */
    private $p2pMode;

    /**
     * @var string
     */
    private $mcastIp;

    /**
     * @var integer
     */
    private $mcastSpeed;

    /**
     * @var integer
     */
    private $mcastPort;

    /**
     * @var string
     */
    private $mcastMode;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set proxy
     *
     * @param string $proxy
     *
     * @return NetworkSettings
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * Get proxy
     *
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Set dns
     *
     * @param string $dns
     *
     * @return NetworkSettings
     */
    public function setDns($dns)
    {
        $this->dns = $dns;

        return $this;
    }

    /**
     * Get dns
     *
     * @return string
     */
    public function getDns()
    {
        return $this->dns;
    }

    /**
     * Set netmask
     *
     * @param string $netmask
     *
     * @return NetworkSettings
     */
    public function setNetmask($netmask)
    {
        $this->netmask = $netmask;

        return $this;
    }

    /**
     * Get netmask
     *
     * @return string
     */
    public function getNetmask()
    {
        return $this->netmask;
    }

    /**
     * Set router
     *
     * @param string $router
     *
     * @return NetworkSettings
     */
    public function setRouter($router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Get router
     *
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set p2pTime
     *
     * @param integer $p2pTime
     *
     * @return NetworkSettings
     */
    public function setP2pTime($p2pTime)
    {
        $this->p2pTime = $p2pTime;

        return $this;
    }

    /**
     * Get p2pTime
     *
     * @return integer
     */
    public function getP2pTime()
    {
        return $this->p2pTime;
    }

    /**
     * Set p2pMode
     *
     * @param string $p2pMode
     *
     * @return NetworkSettings
     */
    public function setP2pMode($p2pMode)
    {
        $this->p2pMode = $p2pMode;

        return $this;
    }

    /**
     * Get p2pMode
     *
     * @return string
     */
    public function getP2pMode()
    {
        return $this->p2pMode;
    }

    /**
     * Set mcastIp
     *
     * @param string $mcastIp
     *
     * @return NetworkSettings
     */
    public function setMcastIp($mcastIp)
    {
        $this->mcastIp = $mcastIp;

        return $this;
    }

    /**
     * Get mcastIp
     *
     * @return string
     */
    public function getMcastIp()
    {
        return $this->mcastIp;
    }

    /**
     * Set mcastSpeed
     *
     * @param integer $mcastSpeed
     *
     * @return NetworkSettings
     */
    public function setMcastSpeed($mcastSpeed)
    {
        $this->mcastSpeed = $mcastSpeed;

        return $this;
    }

    /**
     * Get mcastSpeed
     *
     * @return integer
     */
    public function getMcastSpeed()
    {
        return $this->mcastSpeed;
    }

    /**
     * Set mcastPort
     *
     * @param integer $mcastPort
     *
     * @return NetworkSettings
     */
    public function setMcastPort($mcastPort)
    {
        $this->mcastPort = $mcastPort;

        return $this;
    }

    /**
     * Get mcastPort
     *
     * @return integer
     */
    public function getMcastPort()
    {
        return $this->mcastPort;
    }

    /**
     * Set mcastMode
     *
     * @param string $mcastMode
     *
     * @return NetworkSettings
     */
    public function setMcastMode($mcastMode)
    {
        $this->mcastMode = $mcastMode;

        return $this;
    }

    /**
     * Get mcastMode
     *
     * @return string
     */
    public function getMcastMode()
    {
        return $this->mcastMode;
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
     * Set ntp
     *
     * @param string $ntp
     *
     * @return NetworkSettings
     */
    public function setNtp($ntp)
    {
        $this->ntp = $ntp;

        return $this;
    }

    /**
     * Get ntp
     *
     * @return string
     */
    public function getNtp()
    {
        return $this->ntp;
    }
}
