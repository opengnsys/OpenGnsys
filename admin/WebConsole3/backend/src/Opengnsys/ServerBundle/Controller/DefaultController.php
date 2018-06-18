<?php

namespace Opengnsys\ServerBundle\Controller;

use Opengnsys\ServerBundle\Entity\Client;
use Opengnsys\ServerBundle\Entity\Hardware;
use Opengnsys\ServerBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lsw\ApiCallerBundle\Call\HttpPostJsonBody;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('OpengnsysServerBundle:Default:index.html.twig', array());
    }
}
