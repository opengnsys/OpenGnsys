<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\ServerBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormTypeInterface;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Globunet\ApiBundle\Exception\InvalidFormException;
use Globunet\ApiBundle\Controller\ApiController;

/**
 * @RouteResource("ValidationSettings")
 */
class ValidationSettingsController extends ApiController
{	
	
	/**
	 * Options a ValidationSettings from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options ValidationSettings",
	 * )
	 *
	 * @param Request $request the request object
	 *
	 * @return Response
	 */
	public function optionsAction(Request $request){
		$array = array();
		$array['class'] = "opengnsys_server__api_form_type_validation_settings";
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all ValidationSettings.
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
	 * @Annotations\View(templateVar="ValidationSettings", serializerGroups={"opengnsys_server__validation_settings_cget"})
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
		
		$objects = $this->container->get('opengnsys_server.api_validation_settings_manager')->all($limit, $offset, $matching);
			
		return $objects;
	}
	
	/**
	 * Get single ValidationSettings.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a ValidationSettings for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\ValidationSettings",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the validationSettings is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="validationSettings", serializerGroups={"opengnsys_server__validation_settings_get"})
	 *
	 * @param int     $slug      the validationSettings id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when validationSettings not exist
	 */
	public function getAction($slug)
	{
		$object = $this->getOr404($slug);
	
		return $object;
	}
	
	/**
	 * Create a ValidationSettings from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "opengnsys_server__api_form_type_validation_settings", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__validation_settings_get"},
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
			$object = $this->container->get('opengnsys_server.api_validation_settings_manager')->post(
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
	 * Update existing ValidationSettings from the submitted data or create a new ValidationSettings at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "opengnsys_server__api_form_type_validation_settings", "name" = ""},
	 *   statusCodes = {
	 *     201 = "Returned when the Activity is created",
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__validation_settings_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the validationSettings id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when validationSettings not exist
	 */
	public function putAction(Request $request, $slug)
	{
		try {
			if (!($object = $this->container->get('opengnsys_server.api_validation_settings_manager')->get($slug))) {
				$statusCode = Response::HTTP_CREATED;
				$object = $this->container->get('opengnsys_server.api_validation_settings_manager')->post(
						$request->request->all()
				);
			} else {
				$statusCode = Response::HTTP_NO_CONTENT;
				$object = $this->container->get('opengnsys_server.api_validation_settings_manager')->put(
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
	 * Update existing ValidationSettings from the submitted data or create a new ValidationSettings at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "opengnsys_server__api_form_type_validation_settings", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__validation_settings_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the validationSettings id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when ValidationSettings not exist
	 */
	public function patchAction(Request $request, $slug)
	{
		try {
			$object = $this->container->get('opengnsys_server.api_validation_settings_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single ValidationSettings.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a ValidationSettings for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\ValidationSettings",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the validationSettings is not found"
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
		
		$object = $this->container->get('opengnsys_server.api_validation_settings_manager')->delete($object);	
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * Fetch a ValidationSettings or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return ValidationSettings
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_server.api_validation_settings_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}
}
