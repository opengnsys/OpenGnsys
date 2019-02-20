<?php

namespace Opengnsys\MigrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('OpengnsysMigrationBundle:Default:index.html.twig');
    }
}
