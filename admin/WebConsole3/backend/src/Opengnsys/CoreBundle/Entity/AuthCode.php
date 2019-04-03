<?php
// src/Opengnsys/CoreBundle/Entity/AuthCode.php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Opengnsys on 20/10/15. <info@globunet.com>
 * Copyright (c) 2015 Opengnsys Soluciones Tecnológicas, SL. All rights reserved.
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