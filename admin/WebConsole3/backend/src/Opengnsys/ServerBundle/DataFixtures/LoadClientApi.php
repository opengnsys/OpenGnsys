<?php

namespace Opengnsys\ServerBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Opengnsys\CoreBundle\Entity\Client;
use Opengnsys\ServerBundle\Entity\Netboot;

class LoadClientApi extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new Client();
        $object->setName("Opengnsys Api");
        $object->setDescription("Opengnsys Api");
        $object->setSecret("46rttt2trwo4gocgoc4w80k4s8ok48sg8s84kk0cw48csks8o8");
        $object->setRandomId("23amzbdp4kskg80444oscko4w0w8wokocs88k0g8w88o4oggs4");

        $grantTypes = [];
        $grantTypes[] = "password";
        $grantTypes[] = "refresh_token";
        $grantTypes[] = "token";
        $object->setAllowedGrantTypes($grantTypes);
        $manager->persist($object);
        $manager->flush();
    }
}