<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Image
 */
class Image extends BaseEntity
{
    /**
     * @var string
     */
    private $canonicalName;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var string
     */
    private $path;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var integer
     */
    private $revision;

    /**
     * @var string
     */
    private $partitionInfo;

    /**
     * @var string
     */
    private $fileSize;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\SoftwareProfile
     */
    private $softwareProfile;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Client
     */
    private $client;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Image
     */
    private $parent;

    /**
     * @var \Opengnsys\ServerBundle\Entity\Repository
     */
    private $repository;


    /**
     * Set canonicalName
     *
     * @param string $canonicalName
     *
     * @return Image
     */
    public function setCanonicalName($canonicalName)
    {
        $this->canonicalName = $canonicalName;

        return $this;
    }

    /**
     * Get canonicalName
     *
     * @return string
     */
    public function getCanonicalName()
    {
        return $this->canonicalName;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Image
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
     * @return Image
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
     * Set path
     *
     * @param string $path
     *
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Image
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set revision
     *
     * @param integer $revision
     *
     * @return Image
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision
     *
     * @return integer
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set partitionInfo
     *
     * @param string $partitionInfo
     *
     * @return Image
     */
    public function setPartitionInfo($partitionInfo)
    {
        $this->partitionInfo = $partitionInfo;

        return $this;
    }

    /**
     * Get partitionInfo
     *
     * @return string
     */
    public function getPartitionInfo()
    {
        return $this->partitionInfo;
    }

    /**
     * Set fileSize
     *
     * @param string $fileSize
     *
     * @return Image
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return string
     */
    public function getFileSize()
    {
        return $this->fileSize;
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
     * Set softwareProfile
     *
     * @param \Opengnsys\ServerBundle\Entity\SoftwareProfile $softwareProfile
     *
     * @return Image
     */
    public function setSoftwareProfile(\Opengnsys\ServerBundle\Entity\SoftwareProfile $softwareProfile = null)
    {
        $this->softwareProfile = $softwareProfile;

        return $this;
    }

    /**
     * Get softwareProfile
     *
     * @return \Opengnsys\ServerBundle\Entity\SoftwareProfile
     */
    public function getSoftwareProfile()
    {
        return $this->softwareProfile;
    }

    /**
     * Set client
     *
     * @param \Opengnsys\ServerBundle\Entity\Client $client
     *
     * @return Image
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

    /**
     * Set parent
     *
     * @param \Opengnsys\ServerBundle\Entity\Image $parent
     *
     * @return Image
     */
    public function setParent(\Opengnsys\ServerBundle\Entity\Image $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Opengnsys\ServerBundle\Entity\Image
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set repository
     *
     * @param \Opengnsys\ServerBundle\Entity\Repository $repository
     *
     * @return Image
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
}
