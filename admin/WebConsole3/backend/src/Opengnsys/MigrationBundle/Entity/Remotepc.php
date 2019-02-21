<?php

namespace Opengnsys\MigrationBundle\Entity;

/**
 * Remotepc
 */
class Remotepc
{
    /**
     * @var \DateTime|null
     */
    private $reserved;

    /**
     * @var string|null
     */
    private $urllogin;

    /**
     * @var string|null
     */
    private $urllogout;

    /**
     * @var int
     */
    private $id;


    /**
     * Set reserved.
     *
     * @param \DateTime|null $reserved
     *
     * @return Remotepc
     */
    public function setReserved($reserved = null)
    {
        $this->reserved = $reserved;

        return $this;
    }

    /**
     * Get reserved.
     *
     * @return \DateTime|null
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * Set urllogin.
     *
     * @param string|null $urllogin
     *
     * @return Remotepc
     */
    public function setUrllogin($urllogin = null)
    {
        $this->urllogin = $urllogin;

        return $this;
    }

    /**
     * Get urllogin.
     *
     * @return string|null
     */
    public function getUrllogin()
    {
        return $this->urllogin;
    }

    /**
     * Set urllogout.
     *
     * @param string|null $urllogout
     *
     * @return Remotepc
     */
    public function setUrllogout($urllogout = null)
    {
        $this->urllogout = $urllogout;

        return $this;
    }

    /**
     * Get urllogout.
     *
     * @return string|null
     */
    public function getUrllogout()
    {
        return $this->urllogout;
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
