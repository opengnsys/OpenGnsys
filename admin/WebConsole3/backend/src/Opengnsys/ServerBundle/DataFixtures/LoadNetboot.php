<?php

namespace Opengnsys\ServerBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Opengnsys\ServerBundle\Entity\Netboot;

class LoadNetboot extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new Netboot();
        $object->setName('Sin-designar');
        $object->setFilename('00unknown');

        $template = "timeout 1
title  MBR
chainloader (hd0)+1
rootnoverify (hd0) 
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $object = new Netboot();
        $object->setName('MBR');
        $object->setFilename('01');

        $template = "timeout 1
title  MBR
chainloader (hd0)+1
rootnoverify (hd0) 
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $object = new Netboot();
        $object->setName('1hd-1partition');
        $object->setFilename('11');

        $template = "timeout 1
title FirstHardDisk-FirstPartition
root (hd0,0)
chainloader (hd0,0)+1
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $object = new Netboot();
        $object->setName('1hd-2partition');
        $object->setFilename('12');

        $template = "timeout 1
title FirstHardDisk-SecondPartition
root (hd0,1)
chainloader (hd0,1)+1
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $object = new Netboot();
        $object->setName('ogLiveAdmin');
        $object->setFilename('19pxeADMIN');

        $template = "default saved
timeout 1
hiddenmenu
fallback 1 2 3
               
set ISODIR=ogLive

title OpenGnsys-NET
kernel (pd)/%ISODIR%/ogvmlinuz  ro boot=oginit quiet splash vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=true ogdebug=true ogupdateinitrd=true ogtmpfs=15 oglivedir=%ISODIR% INFOHOST 
initrd (pd)/%ISODIR%/oginitrd.img
boot

title OpenGnsys-NET default
kernel (pd)/ogLive/ogvmlinuz  ro boot=oginit vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=true ogdebug=true ogupdateinitrd=true ogtmpfs=15 oglivedir=ogLive INFOHOST 
initrd (pd)/ogLive/oginitrd.img
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $object = new Netboot();
        $object->setName('ogLive');
        $object->setFilename('pxe');

        $template = "default saved
timeout 1
hiddenmenu
fallback 1 2 3 4

set ISODIR=ogLive

title firsboot
find --set-root --ignore-floppies --ignore-cd /ogboot.me
cmp /ogboot.me /ogboot.firstboot || ls FALLBACK
write /ogboot.firstboot iniciado
chainloader +1
boot

title secondboot
find --set-root --ignore-floppies --ignore-cd /ogboot.me
cmp /ogboot.me /ogboot.secondboot || ls FALLBACK
write /ogboot.secondboot iniciado
chainloader +1
boot

title OpenGnsys-CACHE
find --set-root --ignore-floppies --ignore-cd /boot/%ISODIR%/ogvmlinuz
kernel /boot/%ISODIR%/ogvmlinuz ro boot=oginit quiet splash vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=false ogdebug=false ogupdateinitrd=true ogtmpfs=15 oglivedir=%ISODIR% INFOHOST 
initrd /boot/%ISODIR%/oginitrd.img
boot

title OpenGnsys-NET
kernel (pd)/%ISODIR%/ogvmlinuz  ro boot=oginit quiet splash vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=false ogdebug=false ogtmpfs=15 oglivedir=%ISODIR% INFOHOST 
initrd (pd)/%ISODIR%/oginitrd.img
boot

title OpenGnsys-NET default
kernel (pd)/ogLive/ogvmlinuz  ro boot=oginit oglivedir=ogLive quiet splash vga=788 irqpoll acpi=on og2nd=sqfs ogprotocol=smb ogactiveadmin=false ogdebug=false ogtmpfs=15 oglivedir=ogLive INFOHOST
initrd (pd)/ogLive/oginitrd.img
boot";

        $object->setTemplate($template);
        $manager->persist($object);

        $manager->flush();
    }
}