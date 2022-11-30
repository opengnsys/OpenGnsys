<?php

namespace App\Command;

use App\Entity\OgClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Doctrine\ORM\EntityManagerInterface;


class SendStatusCommand extends Command
{
    protected static $defaultName = 'SendStatusCommand';
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
        $this->addArgument('status', InputArgument::REQUIRED, 'What status is the client in?');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;

        // Crear el cliente con id 1 y actualizar su configuración
        $repo = $em->getRepository(OgClient::class);
        $client = $repo->find(1);
        if(!empty($client)) {
            // Enviar el cambio de estado al servidor
            $serverIp = trim(shell_exec("ogGetServerIp"));
            $result["ip"] = $client->getIp();
            // si el estado no es numerico o es mayor que 7 (UNK) se pone como UNK
            $result["status"] = $client->setStatus($input->getArgument("status"))->getStatus();

            $em->persist($client);
            $em->flush();

            $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/clients/status', [
                'json' => $result
            ]);
        }
        else{
            // TODO Lanzar error?
        }

        return Command::SUCCESS;
    }
}
