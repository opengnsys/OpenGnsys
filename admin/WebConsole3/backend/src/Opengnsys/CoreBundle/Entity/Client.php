<?php
// src/Opengnsys/CoreBundle/Entity/Client.php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 20/10/15. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\CoreBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;

class Client extends BaseClient
{

    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }
}