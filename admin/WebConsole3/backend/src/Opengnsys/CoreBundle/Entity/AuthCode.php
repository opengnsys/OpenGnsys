<?php
// src/Opengnsys/CoreBundle/Entity/AuthCode.php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega Alcantara on 20/10/15. <miguelangel.devega@sic.uhu.es>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */

namespace Opengnsys\CoreBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;

class AuthCode extends BaseAuthCode
{
    protected $id;
    
    protected $client;
    
    protected $user;
}