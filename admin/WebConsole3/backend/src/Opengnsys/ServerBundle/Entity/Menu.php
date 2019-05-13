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
    private $title;

    /**
     * @var string|null
     */
    private $resolution;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $comments;

    /**
     * @var string|null
     */
    private $publicUrl;

    /**
     * @var string|null
     */
    private $privateUrl;

    /**
     * @var int
     */
    private $id;


    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set resolution.
     *
     * @param string|null $resolution
     *
     * @return Menu
     */
    public function setResolution($resolution = null)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution.
     *
     * @return string|null
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Menu
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
     * Set comments.
     *
     * @param string|null $comments
     *
     * @return Menu
     */
    public function setComments($comments = null)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return string|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set publicUrl.
     *
     * @param string|null $publicUrl
     *
     * @return Menu
     */
    public function setPublicUrl($publicUrl = null)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * Get publicUrl.
     *
     * @return string|null
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    /**
     * Set privateUrl.
     *
     * @param string|null $privateUrl
     *
     * @return Menu
     */
    public function setPrivateUrl($privateUrl = null)
    {
        $this->privateUrl = $privateUrl;

        return $this;
    }

    /**
     * Get privateUrl.
     *
     * @return string|null
     */
    public function getPrivateUrl()
    {
        return $this->privateUrl;
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
}
