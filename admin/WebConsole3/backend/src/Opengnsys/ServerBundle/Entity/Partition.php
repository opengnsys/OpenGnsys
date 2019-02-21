<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Partition
 */
class Partition extends BaseEntity
{
    /**
     * @var integer
     */
    private $numDisk;

    /**
     * @var integer
     */
    private $numPartition;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var float
     */
    private $usage;

    /**
     * @var string
     */
    private $cacheContent;

    /**
     * @var string
     */
    private $filesystem;

    /**
     * @var string
     */
    private $partitionCode;

    /**
     * @var string
     */
    private $osName;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Image
     */
    private $image;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Client
     */
    private $client;


    /**
     * Set numDisk
     *
     * @param integer $numDisk
     *
     * @return Partition
     */
    public function setNumDisk($numDisk)
    {
        $this->numDisk = $numDisk;

        return $this;
    }

    /**
     * Get numDisk
     *
     * @return integer
     */
    public function getNumDisk()
    {
        return $this->numDisk;
    }

    /**
     * Set numPartition
     *
     * @param integer $numPartition
     *
     * @return Partition
     */
    public function setNumPartition($numPartition)
    {
        $this->numPartition = $numPartition;

        return $this;
    }

    /**
     * Get numPartition
     *
     * @return integer
     */
    public function getNumPartition()
    {
        return $this->numPartition;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return Partition
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set usage
     *
     * @param float $usage
     *
     * @return Partition
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * Get usage
     *
     * @return float
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Set cacheContent
     *
     * @param string $cacheContent
     *
     * @return Partition
     */
    public function setCacheContent($cacheContent)
    {
        $this->cacheContent = $cacheContent;

        return $this;
    }

    /**
     * Get cacheContent
     *
     * @return string
     */
    public function getCacheContent()
    {
        return $this->cacheContent;
    }

    /**
     * Set filesystem
     *
     * @param string $filesystem
     *
     * @return Partition
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get filesystem
     *
     * @return string
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Set partitionCode
     *
     * @param string $partitionCode
     *
     * @return Partition
     */
    public function setPartitionCode($partitionCode)
    {
        $this->partitionCode = $partitionCode;

        return $this;
    }

    /**
     * Get partitionCode
     *
     * @return string
     */
    public function getPartitionCode()
    {
        return $this->partitionCode;
    }

    /**
     * Set osName
     *
     * @param string $osName
     *
     * @return Partition
     */
    public function setOsName($osName)
    {
        $this->osName = $osName;

        return $this;
    }

    /**
     * Get osName
     *
     * @return string
     */
    public function getOsName()
    {
        return $this->osName;
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
     * Set image
     *
     * @param \Opengnsys\ServerBundle\Entity\Image $image
     *
     * @return Partition
     */
    public function setImage(\Opengnsys\ServerBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Opengnsys\ServerBundle\Entity\Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return Partition
     */
    public function setClient(\Opengnsys\ServerBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Opengnsys\ServerBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
