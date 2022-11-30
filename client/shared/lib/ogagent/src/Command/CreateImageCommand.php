<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Doctrine\ORM\EntityManagerInterface;


class CreateImageCommand extends Command
{
    protected static $defaultName = 'CreateImageCommand';
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
        $this->addArgument('diskNumber', InputArgument::REQUIRED, 'Number of disk where the image have been created');
        $this->addArgument('partitionNumber', InputArgument::REQUIRED, 'Number of partition where the image have been created');
        $this->addArgument('canonicalName', InputArgument::REQUIRED, 'Number of partition where software components have been detected');
        $this->addArgument('imagePath', InputArgument::REQUIRED, 'Path of the image file generated');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;

        print_r($input->getArguments());

        $canonicalName = $input->getArgument("canonicalName");
        $diskNumber = $input->getArgument("diskNumber");
        $partitionNumber = $input->getArgument("partitionNumber");
        $imagePath = $input->getArgument("imagePath")."/".$canonicalName.".img";
        $fileSize = 0;
        if(file_exists($imagePath)){
            $fileSize = filesize($imagePath);
        }


        // Enviar configuración al servidor OG junto con el token para futuras conexiones
        $serverIp = trim(shell_exec("ogGetServerIp"));
        $ip = trim(shell_exec("ogGetIpAddress"));

        $result["canonicalName"] = $canonicalName;
        $result["diskNumber"] = $diskNumber;
        $result["partitionNumber"] = $partitionNumber;
        $result["path"] = $imagePath;
        $result["fileSize"] = $fileSize;

        print_r(json_encode($result));

        $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/images/client/'.$ip, [
            'json' => $result
        ]);

        return Command::SUCCESS;
    }

}
