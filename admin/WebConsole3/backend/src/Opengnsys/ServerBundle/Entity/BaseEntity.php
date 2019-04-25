<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 06/02/19. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2019 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\ServerBundle\Entity;

class BaseEntity
{
    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var string|null
     */
    protected $notes;

    /**
     * PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
        $this->updatedAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
    }

    /**
     * PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = (new \DateTime())->setTimezone(new \DateTimeZone("UTC"));
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return BaseEntity
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return BaseEntity
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set notes.
     *
     * @param string|null $notes
     *
     * @return Repository
     */
    public function setNotes($notes = null)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes.
     *
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
