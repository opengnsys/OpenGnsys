<?php

namespace Opengnsys\ServerBundle\Service;
use Opengnsys\ServerBundle\Entity\Netboot;
use Symfony\Component\Filesystem\Filesystem;

/**
 * NetbootService
 */
class NetbootService
{
    private $pxedir;
    private $em;

    public function __construct($pxedir, $em)
    {
        $this->pxedir = $pxedir;
        $this->em = $em;
    }

    /**
     *           createBootMode ($cmd, $bootopt, $hostid, $lang)
     * @brief    Crea un fichero PXE para el ordenador basado en la plantilla indicada y usando
     *           los datos almacenados en la BD.
     * @param    {Object}  cmd       Objeto de conexión a la base de datos.
     * @param    {String}  bootopt   Plantilla de arranque PXE.
     * @param    {Number}  hostid    Id. del ordenador.
     * @param    {String}  lang      Idioma de arranque.
     * @version  1.0.5 - Primera versión, adaptada de NetBoot Avanzado (Antonio J. Doblas Viso - Universidad de Málaga)
     * @author  Ramón Gómez - ETSII Universidad de Sevilla
     * @date     2013-04-25
     * @version  1.1.0 - Se incluye la unidad organizativa como parametro del kernel: ogunit=directorio_unidad (ticket #678).
     * @author   Irina Gómez - ETSII Universidad de Sevilla
     * @date     2015-12-16
     * @version  1.1.0 - La segunda fase de carga del ogLive se define en el SERVER para evitar erores de sincronismo entre versiones (ticket #787).
     * @author   Antonio J. Doblas Viso - Universidad de Malaga
     * @date     2017-06-01
     * @version  1.1.0 - Se incluye el nombre del perfil hardware y se elimina el winboot (ticket #828).
     * @author   Antonio J. Doblas Viso - Universidad de Malaga
     * @date     2018-01-21
     */
    function createBootMode ($client) {
        $fileSystem = new Filesystem();

        $netboot = $client->getNetboot();
        $organizationalUnit = $client->getOrganizationalUnit();
        $networkSettings = ($organizationalUnit != null)?$organizationalUnit->getNetworkSettings():null;
        $repository = $client->getRepository();
        $hardwareProfile = $client->getHardwareProfile();

        if($netboot == null){
            $repositoryNetboot = $this->em->getRepository(Netboot::class);
            $netboot = $repositoryNetboot->find(1); // name =>Sin-designar , filename=>00unknown
            $client->setNetboot($netboot);
            $this->em->flush($client);
        }

        // Plantilla con las opciones por defecto.
        //$bootopt = ($netboot->getFilename())?$netboot->getFilename():"00unknown";
        $template = $netboot->getTemplate();
        $hostname = $client->getName();
        $ip = $client->getIp();
        $mac = $client->getMac();
        $netiface = $client->getNetiface();
        $netmask = ($networkSettings != null)?$networkSettings->getNetmask():"255.255.255.0";
        $router = ($networkSettings != null)?$networkSettings->getRouter():$_SERVER["SERVER_ADDR"];
        $ntp = ($networkSettings != null)?$networkSettings->getNtp():null;
        $dns = ($networkSettings != null)?$networkSettings->getDns():null;
        $proxy = ($networkSettings != null)?$networkSettings->getProxy():"";
        $group = ($organizationalUnit != null)?$organizationalUnit->getName():"default"; // TRIM
        $repo = ($repository != null)?$repository->getIp():$_SERVER["SERVER_ADDR"];
        $server = $_SERVER["SERVER_ADDR"];
        $vga = "788";
        $hardprofile = ($hardwareProfile != null)?$hardwareProfile->getDescription():"default"; // TRIM
        $oglivedir = $client->getOglive();
        $directory = "";

        $lang="es_ES"; // $lang="es_ES"; //$lang="ca_ES";

        // Componer parámetros del kernel.
        $infohost=" LANG=$lang".
            " ip=$ip:$server:$router:$netmask:$hostname:$netiface:none" .
            " group=$group" .
            " ogrepo=$repo" .
            " oglive=$server" .
            " oglog=$server" .
            " ogshare=$server";
        // Añadir parámetros opcionales.
        if (! empty ($ntp))     { $infohost.=" ogntp=$ntp"; }
        if (! empty ($dns))     { $infohost.=" ogdns=$dns"; }
        if (! empty ($proxy))   { $infohost.=" ogproxy=$proxy"; }
        if (! empty ($hardprofile))     { $infohost.=" hardprofile=$hardprofile"; }
        // Comprobar si se usa el parámetro "vga" (número de 3 cifras) o "video" (cadena).
        if (! empty ($vga)) {
            // UHU - Se sustituye la función is_int por is_numeric, ya que al ser un string no funciona bien con is_int
            if (is_numeric($vga) && strlen($vga) == 3) {
                $infohost.=" vga=$vga";
            } else {
                $infohost.=" video=$vga";
            }
        }
        if (! empty ($directory)) { $infohost.=" ogunit=$directory"; }

        //$mac = substr($mac,0,2) . ":" . substr($mac,2,2) . ":" . substr($mac,4,2) . ":" . substr($mac,6,2) . ":" . substr($mac,8,2) . ":" . substr($mac,10,2);
        $macfile = $this->pxedir . "/01-" . str_replace(":", "-", strtoupper($mac));


        $macfile_temp = $macfile."_test";
        $fileSystem->mkdir($this->pxedir);
        $fileSystem->dumpFile($macfile_temp, $template);

        // Crear fichero de arranque a partir de la plantilla y los datos del cliente.
        // UHU - si el parametro vga no existe, no se quita.
        $command = "";
        if (! empty ($vga)) {
            $command = "sed -e 's|vga=...||g; s|INFOHOST|$infohost|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g' " . $macfile_temp . " > " .$macfile;
        }
        else{
            $command = "sed -e 's|INFOHOST|$infohost|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g; s|set ISODIR=.*|set ISODIR=$oglivedir|g' " . $macfile_temp . " > " . $macfile;
        }
        exec ($command);
        $fileSystem->remove($macfile_temp);
        chmod($macfile, 0777);
    }
}
