<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\OgClient;

/**
 * Class OgClientController
 * @package App\Controller
 *
 * @Route(path="/opengnsys")
 */
class OgClientController extends AbstractController implements TokenAuthenticatedController
{
    private $entityManager;
    private $ogClientRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        // Crear el cliente con id 1 y actualizar su configuración
        $this->ogClientRepository = $this->entityManager->getRepository(OgClient::class);
        $this->taskRepository = $this->entityManager->getRepository(Task::class);
    }

    /**
     * @Route("/status", name="get_status", methods={"GET"})
     */
    public function getStatus(): Response
    {
        // El cliente siempre va a tener id 1
        $client = $this->ogClientRepository->find(1);
        if(!$client){
            // Definir si hay que lanzar un error
            $status = -1;
        }
        else {
            $status = $client->getStatus();
        }

        return $this->json([
            'status' => $status
        ]);
    }

    /**
     * @Route("/configuration", name="get_configuration", methods={"GET"})
     */
    public function getConfiguration(): Response
    {
        // El cliente siempre va a tener id 1
        $client = $this->ogClientRepository->find(1);
        if(!$client){
            // Definir si hay que lanzar un error
            $result = "{Error: No client detected}";
        }
        else {
            $result = array();
            $result["ip"] = $client->getIp();
            $result["mac"] = $client->getMac();
            $result["serialNumber"] = $client->getSerialNumber();
            $result["disks"] = json_decode($client->getConfiguration());
        }

        return $this->json($result);
    }

    /**
     * @Route("/refresh", name="refresh", methods={"GET"})
     */
    public function refresh(): Response
    {
        // El cliente siempre va a tener id 1
        $client = $this->ogClientRepository->find(1);
        if(!$client){
            // Definir si hay que lanzar un error
            $result = "{Error: No client detected}";
        }
        else {
            $client->updateConfiguration();

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $result = array();
            $result["ip"] = $client->getIp();
            $result["mac"] = $client->getMac();
            $result["serialNumber"] = $client->getSerialNumber();
            $result["disks"] = json_decode($client->getConfiguration());
        }

        return $this->json($result);
    }

    /**
     * @Route("/script", name="execute", methods={"POST"})
     */
    public function execute(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $script = base64_decode($data["script"]);
        // Crear una tarea en la base de datos y llamar para su ejecución
        $task = new Task();
        $task->setScript($script);
        $task->setStatus(0);
        if(!empty($data["redirectUri"])) {
            $task->setRedirectUri($data["redirectUri"]);
        }
        if(!empty($data["id"])) {
            $task->setServerTaskId($data["id"]);
        }
        if(!empty($data["sendConfig"])) {
            $task->setSendConfig($data["sendConfig"]);
        }
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $result["result"] = "ok";
        shell_exec("\$OGAGENTCONSOLE ExecuteTask > /dev/null 2>/dev/null &");

        return new JsonResponse( $result, Response::HTTP_CREATED);
    }

    /**
     * @Route("/reboot", name="reboot", methods={"GET"})
     */
    public function reboot(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $script = "reboot 1";
        // Crear una tarea en la base de datos y llamar para su ejecución
        $task = new Task();
        $task->setScript($script);
        $task->setStatus(0);
        if(!empty($data["redirectUri"])) {
            $task->setRedirectUri($data["redirectUri"]);
        }
        if(!empty($data["id"])) {
            $task->setServerTaskId($data["id"]);
        }
        if(!empty($data["sendConfig"])) {
            $task->setSendConfig($data["sendConfig"]);
        }
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $result["result"] = "ok";
        shell_exec("\$OGAGENTCONSOLE ExecuteTask > /dev/null 2>/dev/null &");

        return new JsonResponse( $result, Response::HTTP_CREATED);
    }

    /**
     * @Route("/poweroff", name="poweroff", methods={"GET"})
     */
    public function poweroff(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $script = "poweroff";
        // Crear una tarea en la base de datos y llamar para su ejecución
        $task = new Task();
        $task->setScript($script);
        $task->setStatus(0);
        if(!empty($data["redirectUri"])) {
            $task->setRedirectUri($data["redirectUri"]);
        }
        if(!empty($data["id"])) {
            $task->setServerTaskId($data["id"]);
        }
        if(!empty($data["sendConfig"])) {
            $task->setSendConfig($data["sendConfig"]);
        }
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $result["result"] = "ok";
        shell_exec("\$OGAGENTCONSOLE ExecuteTask > /dev/null 2>/dev/null &");

        return new JsonResponse( $result, Response::HTTP_CREATED);
    }

    /**
     * @Route("/tasks", name="get_tasks", methods={"GET"})
     */
    public function getTasks(Request $request): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        $result = [];
        foreach($tasks as $task) {
            $result[] = $task->toJson();
        }
        return new JsonResponse( $result, Response::HTTP_CREATED);
    }
}
