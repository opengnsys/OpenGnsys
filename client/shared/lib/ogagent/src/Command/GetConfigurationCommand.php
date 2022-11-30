<?php

namespace App\Command;

use App\Entity\OgClient;
use App\Entity\Task;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


use Doctrine\ORM\EntityManagerInterface;


class GetConfigurationCommand extends Command
{
    protected static $defaultName = 'GetConfigurationCommand';
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
        $this->addArgument('background', InputArgument::OPTIONAL, 'Execute in background (default or 1) not showing browser, 0 to show browser', true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $background = boolval($input->getArgument("background"));

        // Enviar configuración al servidor OG junto con el token para futuras conexiones
        $serverIp = trim(shell_exec("ogGetServerIp"));

        $em = $this->entityManager;

        // Crear el cliente con id 1 y actualizar su configuración
        $repo = $em->getRepository(OgClient::class);
        $client = $repo->find(1);
        if(empty($client)) {
            // Crear cliente y token
            $client = new OgClient();
            // Crear el token de autenticacion
            $client->setToken(bin2hex(openssl_random_pseudo_bytes(16)));
        }

        // TODO - Definir los estados
        // Obtener la ip y poner en estado "inicializando"
        $result["ip"] = $client->updateIp();
        $result["status"] = $client->setStatus(ogClient::STATUS_INI)->getStatus();

        $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/clients/status', [
            'json' => $result
        ]);

        // Actualizamos en background (sin mostrar browser ya que estamos arrancando)
        $client->updateConfiguration($background);

        $em->persist($client);
        $em->flush();

        $result = array();
        $result["token"] = $client->getToken();
        $result["ip"] = $client->getIp();
        $result["mac"] = $client->getMac();
        $result["serialNumber"] = $client->getSerialNumber();
        $result["disks"] = json_decode($client->getConfiguration());
        $result["status"] = $client->setStatus(ogClient::STATUS_OPG)->getStatus();

        $response = $this->httpClient->request('POST', 'http://'.$serverIp.'/opengnsys3/index.php/api/clients/config', [
            'json' => $result
        ]);

        // Obtener la lista de tareas pendientes
        $ip["ip"] = $client->updateIp();
        $response = $this->httpClient->request('GET', 'http://'.$serverIp.'/opengnsys3/index.php/api/procedures/pending', [
            'query' => $ip
        ]);
        // Analizar la respuesta para ver las tareas pendientes
        /*
         *  [script] => ls /opt
         *  [id] => 1
         *  [redirectUri] => https://172.16.53.200/opengnsys3/index.php/api/procedures/1/finish
         */
        $repo = $em->getRepository(Task::class);
        foreach ($response->toArray() as $serverTask) {
            // Comprobar si la tarea está en nuestra base de datos
            $tasks = $repo->findBy(["serverTaskId" => $serverTask["id"]], null,1);

            if(count($tasks) == 0) {
                // Guardar la tarea en la base de datos
                $task = new Task();
                $task->setServerTaskId($serverTask["id"]);
                $task->setRedirectUri($serverTask["redirectUri"]);
                $task->setScript($serverTask["script"]);
                $task->setStatus(0);

                $em->persist($task);
                $em->flush();
            }
        }
        // Unaz vez obtenidas, comenzamos la ejecución....
        // volvemos a llamar al comando ExecuteTask por si quedan más tareas
        shell_exec("\$OGAGENTCONSOLE ExecuteTask > /dev/null 2> /dev/null &");

        return Command::SUCCESS;
    }
}
