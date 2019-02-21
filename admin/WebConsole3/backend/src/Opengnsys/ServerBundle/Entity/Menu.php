<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Menu
 */
class Menu extends BaseEntity
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $idurlimg;

    /**
     * @var string
     */
    private $title;

    /**
     * @var integer
     */
    private $publicxcoordinate;

    /**
     * @var integer
     */
    private $publicycoordinate;

    /**
     * @var boolean
     */
    private $publicmode;

    /**
     * @var integer
     */
    private $privatexcoordinate;

    /**
     * @var integer
     */
    private $privateycoordinate;

    /**
     * @var boolean
     */
    private $privatemode;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var string
     */
    private $publicmenuhtml;

    /**
     * @var string
     */
    private $privatemenuhtml;

    /**
     * @var string
     */
    private $resolution;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Opengnsys\ServerBundle\Entity\OrganizationalUnit
     */
    private $organizationalUnit;

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Menu
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
     * Set idurlimg
     *
     * @param integer $idurlimg
     *
     * @return Menu
     */
    public function setIdurlimg($idurlimg)
    {
        $this->idurlimg = $idurlimg;

        return $this;
    }

    /**
     * Get idurlimg
     *
     * @return integer
     */
    public function getIdurlimg()
    {
        return $this->idurlimg;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Menu
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set publicxcoordinate
     *
     * @param integer $publicxcoordinate
     *
     * @return Menu
     */
    public function setPublicxcoordinate($publicxcoordinate)
    {
        $this->publicxcoordinate = $publicxcoordinate;

        return $this;
    }

    /**
     * Get publicxcoordinate
     *
     * @return integer
     */
    public function getPublicxcoordinate()
    {
        return $this->publicxcoordinate;
    }

    /**
     * Set publicycoordinate
     *
     * @param integer $publicycoordinate
     *
     * @return Menu
     */
    public function setPublicycoordinate($publicycoordinate)
    {
        $this->publicycoordinate = $publicycoordinate;

        return $this;
    }

    /**
     * Get publicycoordinate
     *
     * @return integer
     */
    public function getPublicycoordinate()
    {
        return $this->publicycoordinate;
    }

    /**
     * Set publicmode
     *
     * @param boolean $publicmode
     *
     * @return Menu
     */
    public function setPublicmode($publicmode)
    {
        $this->publicmode = $publicmode;

        return $this;
    }

    /**
     * Get publicmode
     *
     * @return boolean
     */
    public function getPublicmode()
    {
        return $this->publicmode;
    }

    /**
     * Set privatexcoordinate
     *
     * @param integer $privatexcoordinate
     *
     * @return Menu
     */
    public function setPrivatexcoordinate($privatexcoordinate)
    {
        $this->privatexcoordinate = $privatexcoordinate;

        return $this;
    }

    /**
     * Get privatexcoordinate
     *
     * @return integer
     */
    public function getPrivatexcoordinate()
    {
        return $this->privatexcoordinate;
    }

    /**
     * Set privateycoordinate
     *
     * @param integer $privateycoordinate
     *
     * @return Menu
     */
    public function setPrivateycoordinate($privateycoordinate)
    {
        $this->privateycoordinate = $privateycoordinate;

        return $this;
    }

    /**
     * Get privateycoordinate
     *
     * @return integer
     */
    public function getPrivateycoordinate()
    {
        return $this->privateycoordinate;
    }

    /**
     * Set privatemode
     *
     * @param boolean $privatemode
     *
     * @return Menu
     */
    public function setPrivatemode($privatemode)
    {
        $this->privatemode = $privatemode;

        return $this;
    }

    /**
     * Get privatemode
     *
     * @return boolean
     */
    public function getPrivatemode()
    {
        return $this->privatemode;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Menu
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
     * Set publicmenuhtml
     *
     * @param string $publicmenuhtml
     *
     * @return Menu
     */
    public function setPublicmenuhtml($publicmenuhtml)
    {
        $this->publicmenuhtml = $publicmenuhtml;

        return $this;
    }

    /**
     * Get publicmenuhtml
     *
     * @return string
     */
    public function getPublicmenuhtml()
    {
        return $this->publicmenuhtml;
    }

    /**
     * Set privatemenuhtml
     *
     * @param string $privatemenuhtml
     *
     * @return Menu
     */
    public function setPrivatemenuhtml($privatemenuhtml)
    {
        $this->privatemenuhtml = $privatemenuhtml;

        return $this;
    }

    /**
     * Get privatemenuhtml
     *
     * @return string
     */
    public function getPrivatemenuhtml()
    {
        return $this->privatemenuhtml;
    }

    /**
     * Set resolution
     *
     * @param string $resolution
     *
     * @return Menu
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
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
     * @return Menu
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
}
