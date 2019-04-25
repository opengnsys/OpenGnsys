<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\ServerBundle\Controller\Api;

use Opengnsys\ServerBundle\Entity\Client;
use Opengnsys\ServerBundle\Form\Type\Api\ClientType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Context\Context;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Opengnsys\CoreBundle\Exception\InvalidFormException;
use Opengnsys\CoreBundle\Controller\ApiController;

/**
 * @RouteResource("Client")
 */
class ClientController extends ApiController
{	
	
	/**
	 * Options a Client from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options Client",
	 * )
	 *
	 * @param Request $request the request object
	 *
	 * @return Response
	 */
	public function optionsAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

		$array = array();
		$array['class'] = ClientType::class;
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all Client.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   statusCodes = {
	 *     200 = "Returned when successful"
	 *   }
	 * )
	 *
	 * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing objects.")
	 * @Annotations\QueryParam(name="limit", requirements="\d+", nullable=true, description="How many objects to return.")
	 *
	 * @Annotations\View(templateVar="client", serializerGroups={"opengnsys_server__client_cget"})
	 *
	 * @param Request               $request      the request object
	 * @param ParamFetcherInterface $paramFetcher param fetcher service
	 *
	 * @return array
	 */
	public function cgetAction(Request $request, ParamFetcherInterface $paramFetcher)
	{
        $request->setRequestFormat($request->get('_format'));

		$offset = $paramFetcher->get('offset');
		$offset = null == $offset ? 0 : $offset;
		$limit = $paramFetcher->get('limit');
		
		$matching = $this->filterCriteria($paramFetcher);

        $clientManager = $this->container->get('opengnsys_server.client_manager');

        //$objects = $clientManager->getRepository()->findAll();
		$objects = $clientManager->searchBy($limit, $offset, $matching);

        $groups = array();
        $groups[] = 'opengnsys_server__client_get';
        $groups[] = 'opengnsys_server__repository_cget';
        $groups[] = 'opengnsys_server__hardware_profile_cget';
        $groups[] = 'opengnsys_server__partition_get';
        $groups[] = 'opengnsys_server__netboot_get';

        $response = $this->view($objects);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
	}
	
	/**
	 * Get single Client.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a Client for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Client",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the client is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="client", serializerGroups={"opengnsys_server__client_get"})
	 *
	 * @param int     $slug      the client id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when client not exist
	 */
	public function getAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));

		$object = $this->getOr404($slug);

        $groups = array();
        $groups[] = 'opengnsys_server__client_get';
        $groups[] = 'opengnsys_server__repository_cget';
        $groups[] = 'opengnsys_server__hardware_profile_cget';
        $groups[] = 'opengnsys_server__partition_get';
        $groups[] = 'opengnsys_server__netboot_get';

        $response = $this->view($object);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
	}
	
	/**
	 * Create a Client from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\ClientType", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__client_get"},
	 *  statusCode = Response::HTTP_CREATED
	 * )
	 *
	 * @param Request $request the request object
	 *
	 * @return FormTypeInterface|View
	 */
	public function cpostAction(Request $request)
	{
        $request->setRequestFormat($request->get('_format'));

	    $parameters = $request->request->all();

        $logger = $this->get('monolog.logger.og_server');
	    $logger->info("----------- NEW CLIENT -----------");
	    foreach ($parameters as $key => $parameter){
            $logger->info($key."=>".$parameter);
        }

		try {
			$object = $this->container->get('opengnsys_server.client_manager')->post($parameters);

            $serviceNetboot = $this->get('opengnsys_service.netboot');
            $serviceNetboot->createBootMode($object);
	
			return $object;
	
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Update existing Client from the submitted data or create a new Client at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\ClientType", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__client_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the client id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when Client not exist
	 */
	public function patchAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));

		try {
			$object = $this->container->get('opengnsys_server.client_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);

            $serviceNetboot = $this->get('opengnsys_service.netboot');
            $serviceNetboot->createBootMode($object);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single Client.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a Client for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Client",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the client is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="delete")
	 *
	 * @param int $slug the object id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when object not exist
	 */
	public function deleteAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));

		$object = $this->getOr404($slug);
		
		$object = $this->container->get('opengnsys_server.client_manager')->delete($object);
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}

    /**
     * List all Client.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="clients", nullable=true, description="")
     * @Annotations\QueryParam(name="ou", nullable=true, description="")
     *
     * @Annotations\View(templateVar="client", serializerGroups={""})
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function statusAction(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $request->setRequestFormat($request->get('_format'));

        $clients = ($paramFetcher->get('clients'))?explode(",",$paramFetcher->get('clients')):null;
        $ou = $paramFetcher->get('ou');


        $clientRepository = $this->container->get('opengnsys_server.client_manager')->getRepository();
        $clients = $clientRepository->searchStatus($clients, $ou);

        return $clients; //$this->view($clients, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a Client from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new object from the submitted data.",
     *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\ClientStatusType", "name" = ""},
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     */
    public function postStatusAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

        $logger = $this->get('monolog.logger.og_server');
        $clientManager = $this->container->get('opengnsys_server.client_manager');
        $clientRepository = $clientManager->getRepository();


        $data = $request->request->all();
        $ip = $data['ip'];
        $status = $data['status'];

        $logger->info("----------- CLIENT STATUS -----------");
        $logger->info("ip: " . $ip);
        $logger->info("status: " . $status);

        $client = $clientRepository->findOneBy(array("ip"=>$ip));
        $client->setStatus($status);
        $clientManager->persist($client);

        return $this->view($client, Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a Client from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new object from the submitted data.",
     *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\ClientConfigType", "name" = ""},
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     */
    public function postConfigAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

        $logger = $this->get('monolog.logger.og_server');
        $em = $this->getDoctrine()->getManager();
        $clientRepository = $em->getRepository(Client::class);


        $data = $request->request->all();
        $ip = $data['ip'];
        $mac = $data['mac'];
        $config = $data['config'];

        $logger->info("----------- CLIENT CONFIG -----------");
        $logger->info("ip: " . $ip. " - mac: ". $mac." - config: " . $config );

        $client = $clientRepository->findOneBy(array("ip"=>$ip, "mac"=>$mac));

        if (!$client) {
            throw new AccessDeniedHttpException();
        }

        $config = explode(';', $config);

        $serialno = $config[0];
        $client->setSerialno($serialno);

        //$client->setStatus($status);

        array_shift($config);
        array_pop($config);
        foreach ($config as $key => $part){
            $part = explode(':', $part);
            $partition = $client->getPartition($key);
            $partition->setNumDisk($part[0]);
            $partition->setNumPartition($part[1]);
            $partition->setPartitionCode($part[2]);
            $partition->setFilesystem($part[3]);
            $partition->setOsName($part[4]);
            $partition->setSize(intval(($part[5]=='')?'0':$part[5]));
            $partition->setUsage(floatval(($part[6]=='')?'0':$part[6]));

            // Si el Filesystem / PartitionCode es Cache leer la información del contenido de la cache.
            if($partition->getFilesystem() === "CACHE"){
                $path = $this->container->getParameter('path_client');
                $file = $ip.".cache.txt";
                $filePath = $path.$file;
                if(file_exists($filePath)) {

                    $data = file_get_contents($filePath);
                    /*
                    $data = explode("\n",trim($data));
                    unset($data[0]);
                    foreach ($data as $item){
                        $type = array_key_exists(0, $item)?trim($item[0]):"";
                        $description = array_key_exists(1, $item)?trim($item[1]):"";
                        if($type!= "" && $description != ""){
                            $logger->info("Hardware: ".$type." = ".$description);
                        }
                    }
                    */
                    $partition->setCacheContent($data);
                }
            }
        }
        $em->flush();

        return $this->view($client, Response::HTTP_NO_CONTENT);

    }
	
	/**
	 * Fetch a Client or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return Client
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_server.client_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}
}
