<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\ServerBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use Opengnsys\ServerBundle\Entity\OrganizationalUnit;
use Opengnsys\ServerBundle\Form\Type\Api\OrganizationalUnitType;
use Opengnsys\ServerBundle\OpengnsysServerBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormTypeInterface;


use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Opengnsys\CoreBundle\Exception\InvalidFormException;
use Opengnsys\CoreBundle\Controller\ApiController;

/**
 * @RouteResource("OrganizationalUnit")
 */
class OrganizationalUnitController extends ApiController
{	
	
	/**
	 * Options a OrganizationalUnit from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options OrganizationalUnit",
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
		$array['class'] = OrganizationalUnitType::class;
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all OrganizationalUnit.
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
     * @Annotations\QueryParam(name="hierarchical", requirements="[01]", default="1", nullable=true, description="Result by hierarchical")
     * @Annotations\QueryParam(name="leaf", requirements="[01]", default="0", nullable=true, description="Result by hierarchical")
     *
	 * @Annotations\View(templateVar="OrganizationalUnit", serializerGroups={"opengnsys_server__organizational_unit_cget"})
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

        $matching["hierarchical"] = $paramFetcher->get('hierarchical');
        $matching["leaf"] = $paramFetcher->get('leaf');
		
		//$matching = $this->filterCriteria($paramFetcher);

        $objects = $this->container->get('opengnsys_server.organizational_unit_manager')->searchBy($limit, $offset, $matching);

        $groups = array();
        $groups[] = 'opengnsys_server__client_cget';
        $groups[] = 'opengnsys_server__network_settings_cget';
        $groups[] = 'opengnsys_server__organizational_unit_get_children';
        $groups[] = 'opengnsys_server__partition_get';
        $groups[] = 'opengnsys_server__netboot_get';

        if($matching["hierarchical"]){
            $groups[] = 'opengnsys_server__organizational_unit_cget_h';
        }else{
            $groups[] = 'opengnsys_server__organizational_unit_cget';
        }

        $response = $this->view($objects);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

		return $response;
	}
	
	/**
	 * Get single OrganizationalUnit.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a OrganizationalUnit for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\OrganizationalUnit",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the organizationalUnit is not found"
	 *   }
	 * )
     *
     * @Annotations\QueryParam(name="parent", requirements="[01]", default="0", nullable=true, description="Get parent")
     * @Annotations\QueryParam(name="children", requirements="[01]", default="0", nullable=true, description="Get children")
	 *
	 * @Annotations\View(templateVar="organizationalUnit", serializerGroups={"opengnsys_server__organizational_unit_get"})
	 *
	 * @param int     $slug      the organizationalUnit id
     * @param ParamFetcherInterface $paramFetcher param fetcher service
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when organizationalUnit not exist
	 */
	public function getAction(Request $request, $slug, ParamFetcherInterface $paramFetcher)
	{
        $request->setRequestFormat($request->get('_format'));
        $groups = array();
        $groups[] = 'opengnsys_server__organizational_unit_get';
        $groups[] = 'opengnsys_server__client_cget';
        $groups[] = 'opengnsys_server__network_settings_cget';
        $groups[] = 'opengnsys_server__partition_get';
        $groups[] = 'opengnsys_server__netboot_get';

		$object = $this->getOr404($slug);

        $parent = $paramFetcher->get('parent');
        $children = $paramFetcher->get('children');

        if($parent){
            $groups[] = 'opengnsys_server__organizational_unit_get_parent';
        }

        if($children){
            $groups[] = 'opengnsys_server__organizational_unit_get_children';
        }

        $response = $this->view($object);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
	}

	/**
	 * Create a OrganizationalUnit from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\OrganizationalUnitType", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__organizational_unit_get"},
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
			$object = $this->container->get('opengnsys_server.organizational_unit_manager')->post(
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
	 * Update existing OrganizationalUnit from the submitted data or create a new OrganizationalUnit at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\OrganizationalUnitType", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__organizational_unit_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the organizationalUnit id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when OrganizationalUnit not exist
	 */
	public function patchAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		try {
			$object = $this->container->get('opengnsys_server.organizational_unit_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single OrganizationalUnit.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a OrganizationalUnit for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\OrganizationalUnit",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the organizationalUnit is not found"
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
		$object = $this->container->get('opengnsys_server.organizational_unit_manager')->delete($object);
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * Fetch a OrganizationalUnit or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return OrganizationalUnit
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_server.organizational_unit_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}
}
