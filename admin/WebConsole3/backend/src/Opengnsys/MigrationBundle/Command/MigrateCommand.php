<?php

namespace Opengnsys\MigrationBundle\Command;

use Opengnsys\MigrationBundle\Entity\Aulas;
use Opengnsys\MigrationBundle\Entity\Centros;
use Opengnsys\MigrationBundle\Entity\Gruposordenadores;
use Opengnsys\MigrationBundle\Entity\Menus;
use Opengnsys\MigrationBundle\Entity\Ordenadores;
use Opengnsys\MigrationBundle\Entity\Perfileshard;
use Opengnsys\MigrationBundle\Entity\Repositorios;
use Opengnsys\ServerBundle\Entity\Client;
use Opengnsys\ServerBundle\Entity\HardwareProfile;
use Opengnsys\ServerBundle\Entity\Menu;
use Opengnsys\ServerBundle\Entity\NetworkSettings;
use Opengnsys\ServerBundle\Entity\OrganizationalUnit;
use Opengnsys\ServerBundle\Entity\Repository;
use Opengnsys\ServerBundle\Entity\SoftwareProfile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('opengnsys:migration:execute')
            ->setDescription('Execute migration Og 1.1 to 3.0')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.og_migration');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $emSlave = $this->getContainer()->get('doctrine')->getManager('og_1');

        // og_1
        $centros = $emSlave->getRepository(Centros::class)->findAll();
        $aulas = $emSlave->getRepository(Aulas::class)->findAll();
        $grupos = $emSlave->getRepository(Gruposordenadores::class)->findAll();
        $ordenadores = $emSlave->getRepository(Ordenadores::class)->findAll();
        $repositorios = $emSlave->getRepository(Repositorios::class)->findAll();
        $menuses = $emSlave->getRepository(Menus::class)->findAll();
        $perfileshards = $emSlave->getRepository(Perfileshard::class)->findAll();
        $perfilessofts = $emSlave->getRepository(Perfileshard::class)->findAll();

        // og_3
        $organizationalUnitRepository = $em->getRepository(OrganizationalUnit::class);
        $clientRepository = $em->getRepository(Client::class);
        $repositoryRepository = $em->getRepository(Repository::class);
        $menuRepository = $em->getRepository(Menu::class);
        $hardwareProfileRepository = $em->getRepository(HardwareProfile::class);
        $softwareProfileRepository = $em->getRepository(SoftwareProfile::class);


        /** Centros **/
        $logger->info("CENTROS TOTAL: ". count($centros));
        foreach ($centros as $centro){
            $id = $centro->getIdcentro();
            $migrateId ="centro:".$id;

            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            if(!$organizationalUnit){
                $organizationalUnit = new OrganizationalUnit();
                $organizationalUnit->setNotes($migrateId);
                $em->persist($organizationalUnit);
            }
            $organizationalUnit->setName($centro->getNombrecentro());
        }
        $em->flush();

        /** Aulas **/
        $logger->info("AULAS TOTAL: ". count($aulas));
        foreach ($aulas as $aula){
            $id = $aula->getIdaula();
            $migrateId ="aula:".$id;

            $migrateParentId = "centro:".$aula->getIdcentro();

            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            if(!$organizationalUnit){
                $organizationalUnit = new OrganizationalUnit();
                $organizationalUnit->setNotes($migrateId);
                $em->persist($organizationalUnit);
            }
            $organizationalUnit->setName($aula->getNombreaula());

            $organizationalUnitParent = $organizationalUnitRepository->findOneByNotes($migrateParentId);
            $organizationalUnit->setParent($organizationalUnitParent);

            $networkSettings = $organizationalUnit->getNetworkSettings();
            if(!$networkSettings){
                $networkSettings = new NetworkSettings();
                $organizationalUnit->setNetworkSettings($networkSettings);
                //$em->persist($networkSettings);
            }
            $networkSettings->setProxy($aula->getProxy());
            $networkSettings->setDns($aula->getDns());
            $networkSettings->setNetmask($aula->getNetmask());
            $networkSettings->setRouter($aula->getRouter());
            $networkSettings->setNtp($aula->getNtp());

            $networkSettings->setP2pTime($aula->getTimep2p());
            $networkSettings->setP2pMode($aula->getModp2p());

            $networkSettings->setMcastIp($aula->getIpmul());
            $networkSettings->setMcastSpeed($aula->getVelmul());
            $networkSettings->setMcastPort($aula->getPormul());
            $networkSettings->setMcastMode($aula->getModomul());
        }

        /** Grupo Ordenador **/
        $logger->info("Grupos TOTAL: ". count($grupos));
        foreach ($grupos as $grupo){
            $id = $grupo->getIdgrupo();
            $migrateId ="grupo:".$id;

            if($grupo->getGrupoid() == 0){
                $migrateParentId = "aula:".$grupo->getIdaula();
            }else{
                $migrateParentId = "grupo:".$grupo->getGrupoid();
            }

            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            if(!$organizationalUnit){
                $organizationalUnit = new OrganizationalUnit();
                $organizationalUnit->setNotes($migrateId);
                $em->persist($organizationalUnit);
            }
            $organizationalUnit->setName($grupo->getNombregrupoordenador());
            $organizationalUnit->setComments($grupo->getComentarios());

            $organizationalUnitParent = $organizationalUnitRepository->findOneByNotes($migrateParentId);
            $organizationalUnit->setParent($organizationalUnitParent);

            $em->flush();

        }
        $em->flush();

        /** Repositorios **/
        $logger->info("REPOSITORIOS TOTAL: ". count($repositorios));
        foreach ($repositorios as $repositorio){
            $id = $repositorio->getIdrepositorio();
            $migrateId ="repositorio:".$id;

            $repository = $repositoryRepository->findOneByNotes($migrateId);
            if(!$repository){
                $repository = new Repository();
                $repository->setNotes($migrateId);
                $em->persist($repository);
            }
            $repository->setName($repositorio->getNombrerepositorio());
            $repository->setIp($repositorio->getIp());
            $repository->setPassword($repositorio->getPassguor());
            $repository->setConfigurationpath("-");
            $repository->setAdminpath("-");
            $repository->setPxepath("-");
            $repository->setPort($repositorio->getPuertorepo());

            // OrganizationalUnit
            if($repositorio->getGrupoid() == 0){
                $migrateId = "centro:".$repositorio->getIdcentro();
            }else{
                $migrateId = "grupo:".$repositorio->getGrupoid();
            }
            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            $repository->setOrganizationalUnit($organizationalUnit);
        }
        $em->flush();

        /** Perfil Hardware **/
        $logger->info("PERFIL HARDWARES TOTAL: ". count($perfileshards));
        foreach ($perfileshards as $perfileshard){
            $perfileshard = new Perfileshard();
            $id = $perfileshard->getIdperfilhard();
            $migrateId ="perfilHardware:".$id;

            $hardwareProfile = $hardwareProfileRepository->findOneByNotes($migrateId);
            if(!$hardwareProfile){
                $hardwareProfile = new HardwareProfile();
                $hardwareProfile->setNotes($migrateId);
                $em->persist($hardwareProfile);
            }
            $hardwareProfile->setDescription($perfileshard->getDescripcion());
            $hardwareProfile->setComments($perfileshard->getComentarios());


            // OrganizationalUnit
            if($perfileshard->getGrupoid() == 0){
                $migrateId = "centro:".$perfileshard->getIdcentro();
            }else{
                $migrateId = "grupo:".$perfileshard->getGrupoid();
            }
            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            $hardwareProfile->setOrganizationalUnit($organizationalUnit);
        }
        $em->flush();

        /** Perfil Softwares **/

        /** Menus **/

        /** Ordenadores **/
        $logger->info("Ordenadores TOTAL: ". count($ordenadores));
        foreach ($ordenadores as $ordenador){
            //$ordenador = new Ordenadores();
            $id = $ordenador->getIdordenador();
            $migrateId ="ordenador:".$id;

            $client = $clientRepository->findOneByNotes($migrateId);
            if(!$client){
                $client = new Client();
                $client->setNotes($migrateId);
                $em->persist($client);
            }
            $client->setName($ordenador->getNombreordenador());
            $client->setSerialno($ordenador->getNumserie());
            $client->setNetiface($ordenador->getNetiface());
            $client->setNetdriver($ordenador->getNetdriver());
            $client->setMac($ordenador->getMac());
            $client->setIp($ordenador->getIp());
            //$client->setStatus();
            $client->setCache($ordenador->getCache());
            $client->setIdproautoexec($ordenador->getIdproautoexec());
            $client->setOglive($ordenador->getOglivedir());

            // Netboot

            // ValidationSettings
            //$migrateId = ""
            //$validationSettings = $validationSettingsRepository->findOneByNotes($migrateId);
            //$client->setValidationSettings($validationSettings);

            // HardwareProfile
            $migrateId = "perfilHardware:".$ordenador->getIdperfilhard();
            $hardwareProfile = $hardwareProfileRepository->findOneByNotes($migrateId);
            $client->setHardwareProfile($hardwareProfile);

            // Menu
            $migrateId = "menu:".$ordenador->getIdmenu();
            $menu = $menuRepository->findOneByNotes($migrateId);
            $client->setMenu($menu);

            // Repository
            $migrateId = "repositorio:".$ordenador->getIdrepositorio();
            $repository = $repositoryRepository->findOneByNotes($migrateId);
            $client->setRepository($repository);

            // OrganizationalUnit
            if($ordenador->getGrupoid() == 0){
                $migrateId = "aula:".$ordenador->getIdaula();
            }else{
                $migrateId = "grupo:".$ordenador->getGrupoid();
            }
            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            $client->setOrganizationalUnit($organizationalUnit);

        }
        $em->flush();







        $output->writeln(sprintf('End'));
    }
}