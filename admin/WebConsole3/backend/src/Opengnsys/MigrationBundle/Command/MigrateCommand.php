<?php

namespace Opengnsys\MigrationBundle\Command;

use Opengnsys\MigrationBundle\Entity\Aulas;
use Opengnsys\MigrationBundle\Entity\Centros;
use Opengnsys\MigrationBundle\Entity\Gruposordenadores;
use Opengnsys\MigrationBundle\Entity\Imagenes;
use Opengnsys\MigrationBundle\Entity\Menus;
use Opengnsys\MigrationBundle\Entity\Ordenadores;
use Opengnsys\MigrationBundle\Entity\OrdenadoresParticiones;
use Opengnsys\MigrationBundle\Entity\Perfileshard;
use Opengnsys\MigrationBundle\Entity\Perfilessoft;
use Opengnsys\MigrationBundle\Entity\Repositorios;
use Opengnsys\MigrationBundle\Entity\Sistemasficheros;
use Opengnsys\MigrationBundle\Entity\Tiposos;
use Opengnsys\ServerBundle\Entity\Client;
use Opengnsys\ServerBundle\Entity\HardwareProfile;
use Opengnsys\ServerBundle\Entity\Image;
use Opengnsys\ServerBundle\Entity\Menu;
use Opengnsys\ServerBundle\Entity\NetworkSettings;
use Opengnsys\ServerBundle\Entity\OrganizationalUnit;
use Opengnsys\ServerBundle\Entity\Partition;
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
        //$logger = $this->getContainer()->get('logger');

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
        $perfilessofts = $emSlave->getRepository(Perfilessoft::class)->findAll();
        $imagenes = $emSlave->getRepository(Imagenes::class)->findAll();
        $particiones = $emSlave->getRepository(OrdenadoresParticiones::class)->findAll();

        $sistemasFicherosRepository = $emSlave->getRepository(Sistemasficheros::class);
        $tipososRepository = $emSlave->getRepository(Tiposos::class);

        // og_3
        $organizationalUnitRepository = $em->getRepository(OrganizationalUnit::class);
        $clientRepository = $em->getRepository(Client::class);
        $repositoryRepository = $em->getRepository(Repository::class);
        $menuRepository = $em->getRepository(Menu::class);
        $hardwareProfileRepository = $em->getRepository(HardwareProfile::class);
        $softwareProfileRepository = $em->getRepository(SoftwareProfile::class);
        $imageRepository = $em->getRepository(Image::class);
        $partitionRepository = $em->getRepository(Partition::class);

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
        $logger->info("GRUPOS TOTAL: ". count($grupos));
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
        $logger->info("PERFIL SOFTWARES TOTAL: ". count($perfilessofts));
        foreach ($perfilessofts as $perfilessoft){
            $id = $perfilessoft->getIdperfilsoft();
            $migrateId ="perfilSoftware:".$id;

            $softwareProfile = $softwareProfileRepository->findOneByNotes($migrateId);
            if(!$softwareProfile){
                $softwareProfile = new SoftwareProfile();
                $softwareProfile->setNotes($migrateId);
                $em->persist($softwareProfile);
            }
            $softwareProfile->setDescription($perfilessoft->getDescripcion());
            $softwareProfile->setComments($perfilessoft->getComentarios());

            // OrganizationalUnit
            if($perfilessoft->getGrupoid() == 0){
                $migrateId = "centro:".$perfilessoft->getIdcentro();
            }else{
                $migrateId = "grupo:".$perfilessoft->getGrupoid();
            }
            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            $softwareProfile->setOrganizationalUnit($organizationalUnit);
        }
        $em->flush();

        /** Menus **/
        $logger->info("PERFIL MENUS TOTAL: ". count($menuses));
        foreach ($menuses as $menuse){
            $id = $menuse->getIdmenu();
            $migrateId ="menu:".$id;

            $menu = $menuRepository->findOneByNotes($migrateId);
            if(!$menu){
                $menu = new Menu();
                $menu->setNotes($migrateId);
                $em->persist($menu);
            }
            $menu->setTitle($menuse->getTitulo());
            $menu->setDescription($menuse->getDescripcion());
            $menu->setComments($menuse->getComentarios());
            $menu->setResolution($menuse->getResolucion());

            $menu->setIdurlimg($menuse->getIdurlimg());
            $menu->setPublicmenuhtml($menuse->getHtmlmenupub());

            // OrganizationalUnit
            if($menuse->getGrupoid() == 0){
                $migrateId = "centro:".$menuse->getIdcentro();
            }else{
                $migrateId = "grupo:".$menuse->getGrupoid();
            }
            $organizationalUnit = $organizationalUnitRepository->findOneByNotes($migrateId);
            $menu->setOrganizationalUnit($organizationalUnit);
        }
        $em->flush();

        /** Ordenadores **/
        $logger->info("ORDENADORES TOTAL: ". count($ordenadores));
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

        /** Imagen **/
        $logger->info("PERFIL IMAGENES TOTAL: ". count($imagenes));
        foreach ($imagenes as $imagen){
            //$imagen = new Imagenes();
            $id = $imagen->getIdimagen();
            $migrateId ="imagen:".$id;

            $image = $imageRepository->findOneByNotes($migrateId);
            if(!$image){
                $image = new Image();
                $image->setNotes($migrateId);
                $em->persist($image);
            }
            $image->setCanonicalName($imagen->getNombreca());
            $image->setDescription($imagen->getDescripcion());
            $image->setComments($imagen->getComentarios());
            $image->setPath($imagen->getRuta());
            $image->setRevision($imagen->getRevision());

            $image->setType($imagen->getTipo());
            //$image->setPartitionInfo();
            //$image->setFileSize();

            // SoftwareProfile
            $migrateId = "perfilSoftware:".$imagen->getIdperfilsoft();
            $softwareProfile = $softwareProfileRepository->findOneByNotes($migrateId);
            $image->setSoftwareProfile($softwareProfile);

            // Client
            $migrateId = "ordenador:".$imagen->getIdordenador();
            $client = $clientRepository->findOneByNotes($migrateId);
            $image->setClient($client);

            // Repository
            $migrateId = "repositorio:".$imagen->getIdrepositorio();
            $repository = $repositoryRepository->findOneByNotes($migrateId);
            $image->setRepository($repository);

        }
        $em->flush();

        /** Particiones **/
        $logger->info("PARTICIONES TOTAL: ". count($particiones));
        foreach ($particiones as $particion){
            //$particion = new OrdenadoresParticiones();
            $ordenadorId = $particion->getIdordenador();
            $numDisk = $particion->getNumdisk();
            $numPar = $particion->getNumpar();
            $migrateId ="paricion:".$ordenadorId."_".$numDisk."_".$numPar;

            $partition = $partitionRepository->findOneByNotes($migrateId);
            if(!$partition){
                $partition = new Partition();
                $partition->setNotes($migrateId);
                $em->persist($partition);
            }
            $partition->setNumDisk($particion->getNumdisk());
            $partition->setNumPartition($particion->getNumpar());
            $partition->setPartitionCode($particion->getCodpar());
            $partition->setSize($particion->getTamano());
            $partition->setCacheContent($particion->getCache());
            $partition->setUsage($particion->getUso());

            //$logger->warning("Find FylesSystema ID: ". $particion->getIdsistemafichero());
            $sistemasFicheros = $sistemasFicherosRepository->find($particion->getIdsistemafichero());
            if($sistemasFicheros){
                $partition->setFilesystem($sistemasFicheros->getDescripcion());
            }

            $tiposos = $tipososRepository->find($particion->getIdnombreso());
            if($tiposos){
                $partition->setOsName($tiposos->getTiposo());
            }

            // Image
            $migrateId = "image:".$particion->getIdimagen();
            $image = $imageRepository->findOneByNotes($migrateId);
            $partition->setImage($image);
            //$particion->getIdperfilsoft();

            // Client
            $migrateId ="ordenador:".$ordenadorId;
            $client = $clientRepository->findOneByNotes($migrateId);
            $partition->setClient($client);
        }
        $em->flush();







        $output->writeln(sprintf('End'));
    }
}