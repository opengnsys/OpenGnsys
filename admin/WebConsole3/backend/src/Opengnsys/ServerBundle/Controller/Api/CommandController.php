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
use Opengnsys\ServerBundle\Entity\Enum\ClientStatus;
use Opengnsys\ServerBundle\Entity\Trace;
use Opengnsys\ServerBundle\Entity\Client;
use Opengnsys\ServerBundle\Entity\Command;
use Opengnsys\ServerBundle\Form\Type\Api\CommandExecuteType;
use Opengnsys\ServerBundle\Form\Type\Api\CommandType;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @RouteResource("Command")
 */
class CommandController extends ApiController
{
	/**
	 * Options a Command from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options Command",
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
		$array['class'] = CommandType::class;
		$array['options'] = array();

		$options = $this->container->get('nelmio_api_doc.parser.form_type_parser')->parse($array);
		return $options;
	}

    /**
     * List all Command.
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
     * @Annotations\View(templateVar="Hardware", serializerGroups={"opengnsys_server__command_cget"})
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

        $objects = $this->container->get('opengnsys_server.command_manager')->searchBy($limit, $offset, $matching);

        return $objects;
    }
	
	/**
	 * Get single Command.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Gets a Command for a given id",
	 *   output = "Opengnsys\ServerBundle\Entity\Command",
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     404 = "Returned when the command is not found"
	 *   }
	 * )
	 *
	 * @Annotations\View(templateVar="command", serializerGroups={"opengnsys_server__command_get"})
	 *
	 * @param int     $slug      the command id
	 *
	 * @return array
	 *
	 * @throws NotFoundHttpException when command not exist
	 */
	public function getAction(Request $request, $slug)
	{
        $request->setRequestFormat($request->get('_format'));
		$object = $this->getOr404($slug);
	
		return $object;
	}
	
	/**
	 * Create a Command from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Creates a new object from the submitted data.",
	 *   input = {"class" = "opengnsys_server__api_form_type_command", "name" = ""},
	 *   statusCodes = {
	 *     200 = "Returned when successful",
	 *     400 = "Returned when the form has errors"
	 *   }
	 * )
	 *
	 * @Annotations\View(
	 *  template = "object",
	 *  serializerGroups={"opengnsys_server__command_get"},
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
			$object = $this->container->get('opengnsys_server.command_manager')->post(
					$request->request->all()
			);
			return $object;
	
		} catch (InvalidFormException $exception) {
	
			return $exception->getForm();
		}
	}

    /**
     * Update existing Command from the submitted data or create a new Command at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = {"class" = "opengnsys_server__api_form_type_command", "name" = ""},
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "object",
     *  serializerGroups={"opengnsys_server__command_get"},
     *  statusCode = Response::HTTP_OK
     * )
     *
     * @param Request $request the request object
     * @param int     $slug      the command id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when Command not exist
     */
    public function patchAction(Request $request, $slug)
    {
        $request->setRequestFormat($request->get('_format'));
        try {
            $object = $this->container->get('opengnsys_server.command_manager')->patch(
                $this->getOr404($slug),
                $request->request->all()
            );

            return $object;

        } catch (InvalidFormException $exception) {

            return $exception->getForm();
        }
    }

    /**
     * Get single Command.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new object from the submitted data.",
     *   input = {"class" = "Opengnsys\ServerBundle\Form\Type\Api\CommandExecuteType", "name" = ""},
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(templateVar="command", serializerGroups={"opengnsys_server__command_get"})
     *
     *
     * @return array
     *
     * @throws NotFoundHttpException when command not exist
     */
    public function postExecuteAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));
        $logger = $this->get('monolog.logger.og_server');
        $logger->info("----------- COMMAND EXECUTE -----------");

        $user = $this->getUser();
        //$logger->info("user: ".get_class($user));

        $outputs = [];
        $response = null;

        $defaultData = array('command' => 'execute command');
        $form = $this->createForm(CommandExecuteType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $clientRepository = $em->getRepository(Client::class);
            $commandRepository = $em->getRepository(Command::class);
            $data = $form->getData();

            $id = $data["id"];
            $script = $data["script"];
            $type = $data["type"];
            $sendConfig = $data["sendConfig"];
            $clientIds = $data["clients"];
            $clientIds = explode(",", $clientIds);

            $clients = $clientRepository->findBy(array("id"=>$clientIds));

            if($type == \Opengnsys\ServerBundle\Entity\Enum\CommandType::POWER_ON){
                //TODO Habría que mandar la petición al Repositorio y este es el que ejecuta:
                $macs = "";
                foreach ($clients as $client) {
                    $macs .= $client->getMac()." ";
                }
                $macs = trim($macs);
                $script = 'wakeonlan '.$macs;

                exec($script, $retval);
                $output['output'] = $retval;
                $output['id'] = "";
                $output['name'] = "";
                $output['statusCode'] = 200;
                $output['error'] = "";

                $logger->info("script: ".$script);
                $outputs[] = $output;

            }else{
                foreach ($clients as $client) {
                    $client->setStatus(ClientStatus::BUSY);
                    $trace = new Trace();
                    $trace->setClient($client);
                    $trace->setExecutedAt(new \DateTime());
                    $trace->setScript($script);
                    $trace->setDoneBy($user);
                    $trace->setCommandType($type);
                    //$trace->setTitle();
                    $em->persist($trace);
                    $em->flush();

                    $curlService = $this->get("opengnsys_service.curl");
                    $result = $curlService->sendCurl("script", $trace, $script, $sendConfig);
                    $outputs[] = $result;

                    if($result["error"] != ""){
                        $logger->info("trace with errors");
                        $trace->setError($result["error"]);
                        $trace->setOutput($result["output"]);
                        $trace->setFinishedAt(new \DateTime());
                        $trace->setStatus(-1);
                        $em->flush();
                    }
                }
            }

            $outputs['type'] = "success";

            $response = Response::HTTP_OK;
        }else{
            $outputs = [];
            $outputs['type'] = "error";
            $outputs['message'] = "Error en la validación del formulario";
            $outputs['data'] = $form;
        }

        if($response == null){
            $response = Response::HTTP_OK;
        }

        return $this->view($outputs, $response);
    }

    /**
     * Delete single Command.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete a Command for a given id",
     *   output = "Opengnsys\ServerBundle\Entity\Command",
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

        $object = $this->container->get('opengnsys_server.command_manager')->delete($object);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Fetch a Client or throw an 404 Exception.
     *
     * @param mixed $slug
     *
     * @return Command
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($slug)
    {
        if (!($object = $this->container->get('opengnsys_server.command_manager')->get($slug))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
        }

        return $object;
    }

}
