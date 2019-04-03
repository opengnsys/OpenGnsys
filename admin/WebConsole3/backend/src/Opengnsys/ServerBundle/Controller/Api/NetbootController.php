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
use Opengnsys\ServerBundle\Entity\Netboot;
use Opengnsys\ServerBundle\Form\Type\Api\NetbootType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
 * @RouteResource("Netboot")
 */
class NetbootController extends ApiController
{	
	
	/**
	 * Options a Netboot from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options Netboot",
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
		$array['class'] = NetbootType::class;
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all Netboot.
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
	 * @Annotations\View(templateVar="netboot", serializerGroups={"opengnsys_server__netboot_cget"})
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
		
		$objects = $this->container->get('opengnsys_server.netboot_manager')->searchBy($limit, $offset, $matching);

        $groups = array();
        $groups[] = 'opengnsys_server__netboot_get';

        $response = $this->view($objects);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
	}
	
	/**
	 * Get single Netboot.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a Netboot for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Netboot",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the netboot is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="netboot", serializerGroups={"opengnsys_server__netboot_get"})
	 *
	 * @param int     $slug      the netboot id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when netboot not exist
	 */
	public function getAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		$object = $this->getOr404($slug);

        $groups = array();
        $groups[] = 'opengnsys_server__netboot_get';
        $groups[] = 'opengnsys_server__repository_cget';
        $groups[] = 'opengnsys_server__hardware_profile_cget';
        $groups[] = 'opengnsys_server__partition_get';

        $response = $this->view($object);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
	}
	
	/**
	 * Create a Netboot from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\NetbootType", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__netboot_get"},
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
		try {
			$object = $this->container->get('opengnsys_server.netboot_manager')->post(
					$request->request->all()
			);
	
			return $object;
	
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Update existing Netboot from the submitted data or create a new Netboot at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\NetbootType", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__netboot_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the netboot id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when Netboot not exist
	 */
	public function patchAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		try {
			$object = $this->container->get('opengnsys_server.netboot_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single Netboot.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a Netboot for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Netboot",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the netboot is not found"
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
		
		$object = $this->container->get('opengnsys_server.netboot_manager')->delete($object);
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}

    /**
     * Create a Netboot from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new object from the submitted data.",
     *   input = {"class" = "", "name" = ""},
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "object",
     *  serializerGroups={"opengnsys_server__netboot_get"},
     *  statusCode = Response::HTTP_CREATED
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|View
     */
    public function postClientAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));
        $logger = $this->get('monolog.logger.og_server');
        $parameteres = $request->request->all();

        $serviceNetboot = $this->get('opengnsys_service.netboot');

        $em = $this->getDoctrine()->getManager();
        $repositoryNetboot = $this->getDoctrine()->getRepository(Netboot::class);
        $repositoryClient = $this->getDoctrine()->getRepository(Client::class);

        foreach($parameteres as $netbootId => $values){
            //$logger->info("Netboot: " . $netbootId);
            $netboot = $repositoryNetboot->find($netbootId);

            foreach($values as $clientId){
                //$logger->info("\tClient: ".$clientId);
                $client = $repositoryClient->find($clientId);
                $client->setNetboot($netboot);
                $serviceNetboot->createBootMode($client);
            }
            $em->flush();

        }
        $response = $this->view(null);

        return $response;
    }
	
	/**
	 * Fetch a Netboot or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return Netboot
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_server.netboot_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}
}
