<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\RepositoryBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use Opengnsys\ServerBundle\Entity\Image;
use Opengnsys\ServerBundle\Form\Type\Api\ImageType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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
 * @RouteResource("Image")
 */
class ImageController extends ApiController
{
	
	/**
	 * List all Image.
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   statusCodes = {
	 *     200 = "Returned when successful"
	 *   }
	 * )
	 *
	 *
	 * @Annotations\View(templateVar="Image", serializerGroups={"opengnsys_server__image_cget"})
	 *
	 * @param Request               $request      the request object
	 * @param ParamFetcherInterface $paramFetcher param fetcher service
	 *
	 * @return array
	 */
	public function cgetAction(Request $request, ParamFetcherInterface $paramFetcher)
	{
        // Informacion de los discos
        exec("df -h | grep `df -P /opt/opengnsys/images | tail -1 | cut -d' ' -f 1` | tr -s ' '", $diskInfo);
        $objects["disk"] = [];
        foreach ($diskInfo as $index => $disk) {
            $disk = explode(" ", $disk);
            $objects['disk'][$index]["partition"]=$disk[0];
            $objects['disk'][$index]["total"]=$disk[1];
            $objects['disk'][$index]["used"]=$disk[2];
            $objects['disk'][$index]["free"]=$disk[3];
            $objects['disk'][$index]["percent"]=$disk[4];
            $objects['disk'][$index]["mountPoint"]=$disk[5];
        }

		$path = $this->getParameter("path_images");
		$finder = new Finder();
		$finder->files()->in($path);
		foreach ($finder as $file){
            $realPath = $file->getRealPath();
            $item["name"] = $file->getRelativePathname();
            $item["size"] = filesize($realPath);//number_format(filesize($realPath)/1024,2,',','');
            $objects['files'][] = $item;
        }
			
		return $objects;
	}

    /**
     * Delete hard Image
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete a Image for a given name",
     *   output = "",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the partition is not found"
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
        $path = $this->getParameter("path_images");
        $finder = new Finder();
        $filesystem = new Filesystem();

        $name = $slug."*";
        $finder->name($name);
        $finder->files()->in($path);
        foreach ($finder as $file){
            $realPath = $file->getRealPath();
            $item["path"] = $realPath;
            $item["name"] = $file->getRelativePath();
            $item["size"] = filesize($realPath);
            $objects['files'][] = $item;
            $filesystem->remove($realPath);
            //unlink($file);
        }

        return $this->view($objects, Response::HTTP_ACCEPTED);
    }
}
