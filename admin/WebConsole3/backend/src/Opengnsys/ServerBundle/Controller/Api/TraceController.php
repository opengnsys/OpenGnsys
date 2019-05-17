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
use Opengnsys\ServerBundle\Entity\Hardware;
use Opengnsys\ServerBundle\Entity\HardwareProfile;
use Opengnsys\ServerBundle\Entity\Image;
use Opengnsys\ServerBundle\Entity\Partition;
use Opengnsys\ServerBundle\Entity\Software;
use Opengnsys\ServerBundle\Entity\SoftwareProfile;
use Opengnsys\ServerBundle\Entity\Trace;
use Opengnsys\ServerBundle\Form\Type\Api\TraceType;
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
 * @RouteResource("Trace")
 */
class TraceController extends ApiController
{	
	
	/**
	 * Options a Trace from the submitted data.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Options Trace",
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
     * List all Trace.
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
     * @Annotations\QueryParam(name="finished", requirements="0|1", nullable=true, description="How many objects to return.")
     *
     * @Annotations\View(templateVar="objects", serializerGroups={"opengnsys_server__trace_cget"})
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

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Trace::class);

        $objects = $repository->searchBy($matching, [], $limit, $offset); //findBy(array("status"=>null), array(), $limit, $offset);

        $groups = array();
        $groups[] = 'opengnsys_server__client_cget';
        $groups[] = 'opengnsys_server__trace_cget';

        $response = $this->view($objects);
        $context = new Context();
        $context->addGroups($groups);
        $response->setContext($context);

        return $response;
    }

    /**
     * Post Trace.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View(
     *  template = "trace",
     *  serializerGroups={"opengnsys_server__trace_get"},
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
        $logger = $this->get('monolog.logger.og_server');

        $response = null;
        $output = "";

        $defaultData = array('trace' => 'execute trace');
        $form = $this->createForm(TraceType::class, $defaultData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $clientIp = $data['ip'];
            $clientMac = $data['mac'];
            $traceId = $data['trace'];
            $status = $data['status'];
            $output = base64_decode($data['output']);
            $error = base64_decode($data['error']);

            $logger->info("----------- COMMAND STATUS -----------");
            $logger->info("clientId: ".$clientIp);
            $logger->info("clientMac: ".$clientMac);
            $logger->info("trace: ".$traceId);
            $logger->info("status: ".$status);
            $logger->info("output: ".$output);
            $logger->info("error: ".$error);

            $em = $this->getDoctrine()->getManager();
            $traceRepository = $em->getRepository(Trace::class);

            $trace = $traceRepository->findOneBy(array("id"=>$traceId));
            // Doble comprobación
            if($trace != null){
                $trace->setStatus($status);
                $trace->setOutput($output);
                $trace->setError($error);
                $trace->setFinishedAt(new \DateTime());

                $client = $trace->getClient();

                switch ($trace->getCommandType()){
                    case \Opengnsys\ServerBundle\Entity\Enum\CommandType::HARDWARE_INVENTORY:
                        $hardwareRepository = $em->getRepository(Hardware::class);
                        $hardwareProfileRepository = $em->getRepository(HardwareProfile::class);

                        $logger->info("-> HARDWARE_INVENTORY <-");
                        //$path = $this->getParameter('path_client');

                        if($client != null){
                            //$ip = $client->getIp();
                            //$file = "hard-".$ip;
                            //$filePath = $path.$file;

                            //if(file_exists($filePath)) {
                            $description = "HardwareProfile-".$client->getName();
                            $hardwareProfile = $hardwareProfileRepository->findOneBy(array("description"=>$description));
                            if($hardwareProfile == null){
                                $logger->info("Crea Hardware Profile: ". $description);
                                $hardwareProfile = new HardwareProfile();
                                $hardwareProfile->setDescription($description);
                                $em->persist($hardwareProfile);
                            }else{
                                $logger->info("Ya existe Hardware Profile: ". $description);
                                $hardwareProfile->getHardwares()->clear();
                            }

                            //$data = file_get_contents($filePath);
                            $data = $output;

                            $data = explode("\n",trim($data));
                            unset($data[0]);
                            foreach ($data as $item){
                                $item = explode("=",trim($item));

                                $type = array_key_exists(0, $item)?trim($item[0]):"";
                                $description = array_key_exists(1, $item)?trim($item[1]):"";
                                if($type!= "" && $description != ""){
                                    $logger->info("Hardware: ".$type." = ".$description);

                                    $hardware = $hardwareRepository->findOneBy(array("type"=>$type, "description"=>$description));
                                    if($hardware == null){
                                        $logger->info("-- lo crea nuevo --");
                                        $hardware = new Hardware();
                                        $hardware->setType($type);
                                        $hardware->setDescription($description);
                                    }
                                    $hardwareProfile->addHardware($hardware);
                                }
                            }
                            $client->setHardwareProfile($hardwareProfile);
                            //}
                        }
                        break;
                    case \Opengnsys\ServerBundle\Entity\Enum\CommandType::CREATE_IMAGE:
                        $imageRepository = $em->getRepository(Image::class);
                        $partitionRepository = $em->getRepository(Partition::class);

                        $logger->info("-> CREATE_IMAGE <-");

                        if($client != null){
                            $script = $trace->getScript();
                            $script = explode("\n", $script);
                            $script = explode(" ", $script[1]);
                            $numDisk = array_key_exists(1, $script)?trim($script[1]):"";
                            $numPartition = array_key_exists(2, $script)?trim($script[2]):"";
                            $canonicalName = array_key_exists(3, $script)?trim($script[3]):"";

                            $image = $imageRepository->findOneBy(array("canonicalName"=>$canonicalName));
                            $partition = $partitionRepository->findOneBy(array("client"=>$client, "numDisk"=>$numDisk, "numPartition"=>$numPartition));
                            $partitionInfo = array();

                            $logger->info("Partition: ". "client: ".$client->getIp()." numDisk: ".$numDisk." numPartition: ".$numPartition);
                            if($partition != null){
                                $partition->setImage($image);

                                $partitionInfo["numDisk"] = $partition->getNumDisk();
                                $partitionInfo["numPartition"] = $partition->getNumPartition();
                                $partitionInfo["partitionCode"] = $partition->getPartitionCode();
                                $partitionInfo["filesystem"] = $partition->getFilesystem();
                                $partitionInfo["osName"] = $partition->getOsName();

                            }else{
                                $logger->info("Not Found Partition");
                            }

                            if($image != null){
                                $image->setClient($client);
                                $image->setRevision($image->getRevision() + 1);
                                $image->setPartitionInfo(json_encode($partitionInfo));

                                $path = $this->getParameter('path_images');
                                $file = $canonicalName.".img";
                                $filePath = $path.$file;

                                $logger->info("Image File: ". $filePath);

                                if(file_exists($filePath)) {
                                    $image->setPath($filePath);
                                    $image->setFileSize(filesize($filePath));
                                }

                                if($image->getCreatedAt() == null){
                                    $image->setCreatedAt(new \DateTime("now",new \DateTimeZone('Europe/Madrid')));
                                }

                                $image->setUpdatedAt(new \DateTime("now",new \DateTimeZone('Europe/Madrid')));


                            }else{
                                $logger->info("Not Found Image");
                            }
                        }
                        $em->flush();
                    case \Opengnsys\ServerBundle\Entity\Enum\CommandType::SOFTWARE_INVENTORY:
                        $softwareRepository = $em->getRepository(Software::class);
                        $softwareProfileRepository = $em->getRepository(SoftwareProfile::class);
                        $partitionRepository = $em->getRepository(Partition::class);

                        $logger->info("-> SOFTWARE_INVENTORY <-");
                        //$path = $this->getParameter('path_client');

                        if($client != null){
                            //$ip = $client->getIp();
                            $script = $trace->getScript();
                            $script = explode("\n", $script);
                            $script = explode(" ", $script[0]);
                            $numDisk = array_key_exists(1, $script)?trim($script[1]):"";
                            $numPartition = array_key_exists(2, $script)?trim($script[2]):"";

                            //$file = "soft-".$ip."-".$numDisk."-".$numPartition;
                            //$filePath = $path.$file;
                            //$logger->info("File: ". $filePath);

                            //if(file_exists($filePath)) {
                            $description = "SoftwareProfile-".$client->getName()."-".$numDisk."-".$numPartition;
                            $softwareProfile = $softwareProfileRepository->findOneBy(array("description"=>$description));
                            if($softwareProfile == null){
                                $logger->info("Crea Software Profile: ". $description);
                                $softwareProfile = new SoftwareProfile();
                                $softwareProfile->setDescription($description);

                                $em->persist($softwareProfile);
                            }else{
                                $logger->info("Ya existe Software Profile: ". $description);
                                $softwareProfile->getSoftwares()->clear();
                            }

                            //$data = file_get_contents($filePath);
                            $data = $output;

                            $data = explode("\n",trim($data));
                            foreach ($data as $item){
                                $item = trim($item);
                                if ($item === reset($data)) {
                                    $type = "os";
                                }else{
                                    $type = "app";
                                }

                                $description = $item;
                                if($description != ""){
                                    $logger->info("Software: ".$type." = ".$description);

                                    $software = $softwareRepository->findOneBy(array("description"=>$description));
                                    if($software == null){
                                        $logger->info("-- lo crea nuevo --");
                                        $software = new Software();
                                        $software->setType($type);
                                        $software->setDescription($description);
                                    }
                                    $softwareProfile->addSoftware($software);
                                }
                            }

                            $logger->info("Partition");
                            $partition = $partitionRepository->findOneBy(array("client"=>$client, "numDisk"=>$numDisk, "numPartition"=>$numPartition));
                            if($partition != null){
                                $image = $partition->getImage();
                                if($image != null){
                                    $logger->info("Assing Image : " . $image->getCanonicalName() . " to SoftwareProfile: ". $description);
                                    $image->setSoftwareProfile($softwareProfile);
                                }else{
                                    $logger->info("Not Found Image in Partition: ". "client: ".$client->getIp()." numDisk: ".$numDisk." numPartition: ".$numPartition);
                                }
                            }else{
                                $logger->info("Not Found Partition: ". "client: ".$client->getIp()." numDisk: ".$numDisk." numPartition: ".$numPartition);
                            }
                            //}else{
                            //    $logger->info("Not Found File: ". $filePath);
                            //}
                        }

                        break;

                }
                $client->setStatus(ClientStatus::OG_LIVE);
                $em->flush();
                $logger->info("Execute Flush");
            }

        }
        // http://172.16.140.210/opengnsys3/rest/web//api/private/clients/status
        // http://172.16.140.210/opengnsys3/rest/web/app_dev.php/api/clients/status


        //$objects = $this->container->get('opengnsys_server.client_manager')->searchBy($limit, $offset, $matching);

        return $this->view($output, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete single Trace.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete a Trace for a given id",
     *   output = "Opengnsys\ServerBundle\Entity\Trace",
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
        $em = $this->getDoctrine()->getManager();
        $traceRepository = $em->getRepository(Trace::class);

        if (!($object = $traceRepository->find($slug))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.',$slug));
        }

        $em->remove($object);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
