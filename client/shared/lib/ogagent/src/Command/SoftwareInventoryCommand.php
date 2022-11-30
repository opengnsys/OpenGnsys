<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Doctrine\ORM\EntityManagerInterface;


class SoftwareInventoryCommand extends Command
{
    protected static $defaultName = 'SoftwareInventoryCommand';
    protected static $defaultDescription = 'Add a short description for your command';

    private $entityManager;
    private $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('softwareFilePath', InputArgument::REQUIRED, 'File with all software components detected in partition of disk');
        $this->addArgument('diskNumber', InputArgument::REQUIRED, 'Number of disk where software components have been detected');
        $this->addArgument('partitionNumber', InputArgument::REQUIRED, 'Number of partition where software components have been detected');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;

        $filePath = $input->getArgument("softwareFilePath");
        $diskNumber = $input->getArgument("diskNumber");
        $partitionNumber = $input->getArgument("partitionNumber");

        // leer el fichero
        $fn = fopen($filePath,"r");
        // la primera linea es el sistema operativo
        $line = fgets($fn);
        $component["type"] = "os";
        $component["description"] = $line;
        $result = [];
        while(!feof($fn))  {
            $result[] = $component;
            $line = fgets($fn);
            // Si la linea contiene la palabra driver se le asigna ese tipo
            if(!stripos($line, "driver")){
                $component["type"] = "app";
            }
            else{
                $component["type"] = "drv";
            }
            $component["description"] = $line;
        }
        fclose($fn);
        // Enviar configuración al servidor OG junto con el token para futuras conexiones
        $serverIp = trim(shell_exec("ogGetServerIp"));
        $ip = trim(shell_exec("ogGetIpAddress"));

        $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/softwares/client/'.$ip.'/disk/'.$diskNumber.'/partition/'.$partitionNumber, [
            'json' => $result
        ]);

        return Command::SUCCESS;
    }

}
