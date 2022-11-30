<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\OgClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Doctrine\ORM\EntityManagerInterface;


class ExecuteTaskCommand extends Command
{
    protected static $defaultName = 'ExecuteTaskCommand';
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
        $repo = $em->getRepository(Task::class);
        $tasks = $repo->findBy(["status" => 1], null,1);

        // Comprobar si hay alguna tarea en ejecución, si la hay no hacer nada, al finalizar se llamara nuevamente a este comando
        if(count($tasks) == 0){
            echo "No Executing task found ";
            $tasks = $repo->findBy(["status" => 0], null,1);
            // Buscar la primera tarea pendiente de ejecución
            if(count($tasks) != 0) {
                $task = $tasks[0];
                echo "Pending task found ".$task->getId();
                // Marcar la tarea como en ejecución
                $task->setStatus(1);
                $em->persist($task);
                $em->flush();
                echo "Executing script ".$task->getScript();
                // ejecutar la tarea
                $result["output"] = OgClient::executeScript($task->getScript());

                echo "finished\n";
                // Marcar la tarea como finalizada
                $task->setStatus(2);
                $em->persist($task);
                $em->flush();
                echo "Task updated\n";

                // Si la tarea marca que tiene que enviar la configuración, se envia
                if($task->getSendConfig()){
                    shell_exec("\$OGAGENTCONSOLE GetConfiguration 1");
                }

                // Informar al servidor que se finalizó la tarea
                if(!empty($task->getRedirectUri())) {
                    // Obtener el cliente
                    $repo = $em->getRepository(OgClient::class);
                    $client = $repo->find(1);
                    $result["status"] = 0;
                    $result["client"] = $client->getId();
                    $result["ip"] = $client->getIp();
                    $result["mac"] = $client->getMac();
                    $result["output"] = base64_encode($result["output"]);

                    $response = $this->httpClient->request('POST', $task->getRedirectUri(), [
                        'json' => $result,
                        'verify_peer' => false,
                        'verify_host' => false
                    ]);
                }
                // volvemos a llamar al comando ExecuteTask por si quedan más tareas
                shell_exec("\$OGAGENTCONSOLE ExecuteTask > /dev/null 2> /dev/null &");
            }
            else{
                echo "No Pending task found ";
            }

        }
        else{
            echo "Executing task found ".$tasks[0]->getId();
        }

        return Command::SUCCESS;
    }

}
