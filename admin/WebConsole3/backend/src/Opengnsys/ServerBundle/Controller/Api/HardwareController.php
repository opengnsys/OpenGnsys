<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\ServerBundle\Controller\Api;

use Opengnsys\ServerBundle\Form\Type\Api\HardwareType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Globunet\ApiBundle\Exception\InvalidFormException;
use Globunet\ApiBundle\Controller\ApiController;

/**
 * @RouteResource("Hardware")
 */
class HardwareController extends ApiController
{	
	
	/**
	 * Options a Hardware from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options Hardware",
	 * )
	 *
	 * @param Request $request the request object
	 *
	 * @return Response
	 */
	public function optionsAction(Request $request){
		$array = array();
		$array['class'] = HardwareType::class;
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all Hardware.
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
	 * @Annotations\View(templateVar="Hardware", serializerGroups={"opengnsys_server__hardware_cget"})
	 *
	 * @param Request               $request      the request object
	 * @param ParamFetcherInterface $paramFetcher param fetcher service
	 *
	 * @return array
	 */
	public function cgetAction(Request $request, ParamFetcherInterface $paramFetcher)
	{
		$offset = $paramFetcher->get('offset');
		$offset = null == $offset ? 0 : $offset;
		$limit = $paramFetcher->get('limit');
		
		$matching = $this->filterCriteria($paramFetcher);
		
		$objects = $this->container->get('opengnsys_server.api_hardware_manager')->all($limit, $offset, $matching);
			
		return $objects;
	}
	
	/**
	 * Get single Hardware.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a Hardware for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Hardware",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the hardware is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="hardware", serializerGroups={"opengnsys_server__hardware_get"})
	 *
	 * @param int     $slug      the hardware id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when hardware not exist
	 */
	public function getAction($slug)
	{
		$object = $this->getOr404($slug);
	
		return $object;
	}
	
	/**
	 * Create a Hardware from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "opengnsys_server__api_form_type_hardware", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__hardware_get"},
	 *  statusCode = Response::HTTP_CREATED
	 * )
	 *
	 * @param Request $request the request object
	 *
	 * @return FormTypeInterface|View
	 */
	public function cpostAction(Request $request)
	{
		try {
			$object = $this->container->get('opengnsys_server.api_hardware_manager')->post(
					$request->request->all()
			);	
			
			/*
			if (is_object($this->getUser()))
			{
				$admin = $this->container->get('globunet_api.admin.object');
				$admin->createObjectSecurity($object);
			}
			*/
	
			return $object;
	
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Update existing Hardware from the submitted data or create a new Hardware at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "opengnsys_server__api_form_type_hardware", "name" = ""},
	 *   statusCodes = {
	 *     201 = "Returned when the Activity is created",
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__hardware_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the hardware id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when hardware not exist
	 */
	public function putAction(Request $request, $slug)
	{
		try {
			if (!($object = $this->container->get('opengnsys_server.api_hardware_manager')->get($slug))) {
				$statusCode = Response::HTTP_CREATED;
				$object = $this->container->get('opengnsys_server.api_hardware_manager')->post(
						$request->request->all()
				);
			} else {
				$statusCode = Response::HTTP_NO_CONTENT;
				$object = $this->container->get('opengnsys_server.api_hardware_manager')->put(
						$object,
						$request->request->all()
				);
			}
			
			return $this->view($object, $statusCode);		
	
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Update existing Hardware from the submitted data or create a new Hardware at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "opengnsys_server__api_form_type_hardware", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__hardware_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the hardware id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when Hardware not exist
	 */
	public function patchAction(Request $request, $slug)
	{
		try {
			$object = $this->container->get('opengnsys_server.api_hardware_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single Hardware.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a Hardware for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Hardware",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the hardware is not found"
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
	public function deleteAction($slug)
	{
		$object = $this->getOr404($slug);
		
		$object = $this->container->get('opengnsys_server.api_hardware_manager')->delete($object);	
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * Fetch a Hardware or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return Hardware
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_server.api_hardware_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}
}
