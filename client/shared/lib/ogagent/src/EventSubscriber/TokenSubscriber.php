<?php
// src/EventSubscriber/TokenSubscriber.php
namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\OgClient;


class TokenSubscriber implements EventSubscriberInterface
{
    private $token;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $em = $this->entityManager;

        // Crear el cliente con id 1 y actualizar su configuración
        $repo = $em->getRepository(OgClient::class);
        $client = $repo->find(1);
        if(!empty($client)) {
            $this->token = $client->getToken();
        }
        else{
            // TODO - Qué hacer?
            $this->token = "NOT-VALID";
        }
    }


    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->headers->get("Authorization");
            if ($token !== $this->token) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}