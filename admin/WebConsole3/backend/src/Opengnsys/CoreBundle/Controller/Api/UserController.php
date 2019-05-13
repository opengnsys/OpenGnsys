<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\CoreBundle\Controller\Api;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
use Opengnsys\CoreBundle\Form\Type\Api\UserFormType;

/**
 * @RouteResource("User")
 */
class UserController extends ApiController
{	
	
	/**
	 * Options a User from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options User",
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
		$array['class'] = UserFormType::class;
		$array['options'] = array();
		
		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}
	
	/**
	 * List all User.
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
	 * @Annotations\View(templateVar="User", serializerGroups={"opengnsys_server__user_cget"})
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
		
		$objects = $this->container->get('opengnsys_core.user_manager')->searchBy($limit, $offset, $matching);
			
		return $objects;
	}
	
	/**
	 * Get single User.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a User for a given id",
	 *   output = "Opengnsys\CoreBundle\Entity\User",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the user is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="user", serializerGroups={"opengnsys_server__user_get"})
	 *
	 * @param int     $slug      the user id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when user not exist
	 */
	public function getAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		$object = $this->getOr404($slug);
	
		return $object;
	}
	
	/**
	 * Create a User from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "Opengnsys\CoreBundle\Form\Type\UserFormType", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__user_get"},
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
			$object = $this->container->get('opengnsys_core.user_manager')->post(
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
	 * Update existing User from the submitted data or create a new User at a specific location.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   input = {"class" = "Opengnsys\CoreBundle\Form\Type\UserFormType", "name" = ""},
	 *   statusCodes = {
	 *     204 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__user_get"},
	 *  statusCode = Response::HTTP_OK
	 * )
	 *
	 * @param Request $request the request object
	 * @param int     $slug      the user id
	 *
	 * @return FormTypeInterface|View
	 *
	 * @throws NotFoundHttpException when User not exist
	 */
	public function patchAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		try {
			$object = $this->container->get('opengnsys_core.user_manager')->patch(
					$this->getOr404($slug),
					$request->request->all()
			);
	
			return $object;
			
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}
	
	/**
	 * Delete single User.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Delete a User for a given id",
	 *   output = "Opengnsys\CoreBundle\Entity\User",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the user is not found"
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
		$object = $this->container->get('opengnsys_core.user_manager')->delete($object);
	
		return $this->view(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * Fetch a User or throw an 404 Exception.
	 *
	 * @param mixed $slug
	 *
	 * @return User
	 *
	 * @throws NotFoundHttpException
	 */
	protected function getOr404($slug)
	{
		if (!($object = $this->container->get('opengnsys_core.user_manager')->get($slug))) {
			throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
		}
	
		return $object;
	}

    /**
     *
     * @Annotations\View(templateVar="users", serializerGroups={"opengnsys_server__user_me"})
     *
     * @ApiDoc(resource = true)
     */
    public function getMeAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

        try{
            /*
                if (false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw new AccessDeniedException();
                }
                */
            $this->forwardIfNotAuthenticated();
            $response = $this->getUser();

            //$this->get('logger')->error(var_export($response,true));
        }catch(AccessDeniedException $ade){
            $response = new Response($ade->getMessage(), $ade->getCode());
        }

        return $response;
    }

    /**
     * Shortcut to throw a AccessDeniedException($message) if the user is not authenticated
     * @param String $message The message to display (default:'warn.user.notAuthenticated')
     */
    protected function forwardIfNotAuthenticated($message='warn.user.notAuthenticated'){
        if (!is_object($this->getUser()))
        {
            throw new AccessDeniedException($message);
        }
    }
}
