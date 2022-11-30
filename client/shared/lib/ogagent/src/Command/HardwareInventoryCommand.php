<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Doctrine\ORM\EntityManagerInterface;


class HardwareInventoryCommand extends Command
{
    protected static $defaultName = 'HardwareInventoryCommand';
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
        $this->addArgument('hardwareFilePath', InputArgument::REQUIRED, 'File with all hardware components detected in system');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;

        $filePath = $input->getArgument("hardwareFilePath");

        // leer el fichero
        $fn = fopen($filePath,"r");
        // Saltar la primera linea
        fgets($fn);
        $result = [];
        while(!feof($fn))  {
            $line = fgets($fn);
            // La linea viene en la forma: id= descripcion
            $parts = explode("=", $line);
            if(count($parts) === 2) {
                $component["type"] = $parts[0];
                $component["description"] = $parts[1];
                $result[] = $component;
            }
        }
        fclose($fn);
        // Enviar configuración al servidor OG junto con el token para futuras conexiones
        $serverIp = trim(shell_exec("ogGetServerIp"));
        $ip = trim(shell_exec("ogGetIpAddress"));

        $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/hardwares/client/'.$ip, [
            'json' => $result
        ]);

        return Command::SUCCESS;
    }

}
