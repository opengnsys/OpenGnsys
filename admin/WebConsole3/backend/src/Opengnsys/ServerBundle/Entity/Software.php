<?php

namespace Opengnsys\ServerBundle\Entity;

/**
 * Software
 */
class Software extends BaseEntity
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $osType;

    /**
     * @var string
     */
    private $type;

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
     * @return Software
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
     * Set osType
     *
     * @param string $osType
     *
     * @return Software
     */
    public function setOsType($osType)
    {
        $this->osType = $osType;

        return $this;
    }

    /**
     * Get osType
     *
     * @return string
     */
    public function getOsType()
    {
        return $this->osType;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Software
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return Software
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
