<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Miguel Angel de Vega on 18/03/16. <miguelangel.devega@sic.com>
 * Copyright (c) 2015 Opengnsys. All rights reserved.
 *
 */
 
namespace Opengnsys\ServerBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Opengnsys\ServerBundle\Entity\Enum\CommandType;
use Symfony\Component\HttpFoundation\Request;
use Opengnsys\CoreBundle\Controller\ApiController;


/**
 * @RouteResource("Core")
 */
class CoreController extends ApiController
{
	/**
	 * Get general server information
     *
	 * @ApiDoc(
	 *   resource = true,
	 *   statusCodes = {
	 *     200 = "Returned when successful"
	 *   }
	 * )
	 *
	 * @return JSON object with basic server information (version, services, etc.)
	 */
	public function getInfoAction(Request $request)
	{
        $request->setRequestFormat($request->get('_format'));

        // Reading version file.
        @list($project, $version, $release) = explode(' ', file_get_contents('/opt/opengnsys/doc/VERSION.txt'));
        $response['project'] = trim($project);
        $response['version'] = trim($version);
        $response['release'] = trim($release);
        // Getting actived services.
        @$services = parse_ini_file('/etc/default/opengnsys');
        $response['services'] = Array();
        if (@$services["RUN_OGADMSERVER"] === "yes") {
            array_push($response['services'], "server");
            $hasOglive = true;
        }
        if (@$services["RUN_OGADMREPO"] === "yes")  array_push($response['services'], "repository");
        if (@$services["RUN_BTTRACKER"] === "yes")  array_push($response['services'], "tracker");
        // Reading installed ogLive information file.
        if ($hasOglive === true) {
            $data = json_decode(@file_get_contents('/opt/opengnsys/etc/ogliveinfo.json'));
            if (isset($data->oglive)) {
                $response['oglive'] = $data->oglive;
            }
        }

		return $response;
	}

    /**
     * Get the server status
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @return JSON object with all data collected from server status (RAM, %CPU, etc.).
     */
    public function getStatusAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

        // Getting memory and CPU information.
        exec("awk '$1~/Mem/ {print $2}' /proc/meminfo",$memInfo);
        $memInfo = array("total" => $memInfo[0], "used" => $memInfo[0]-$memInfo[1]);
        $cpuInfo = exec("awk '$1==\"cpu\" {printf \"%.2f\",($2+$4)*100/($2+$4+$5)}' /proc/stat");
        $cpuModel = exec("awk -F: '$1~/model name/ {print $2}' /proc/cpuinfo");
        $response["memInfo"] = $memInfo;
        $response["cpu"] = array("model" => trim($cpuModel), "usage" => $cpuInfo);

        // Informacion de los discos
        exec("df -h | grep /dev/ | tr -s ' '", $diskInfo);
        $response["disk"] = [];
        foreach ($diskInfo as $index => $disk) {
            $disk = explode(" ", $disk);
            $response['disk'][$index]["partition"]=$disk[0];
            $response['disk'][$index]["total"]=$disk[1];
            $response['disk'][$index]["used"]=$disk[2];
            $response['disk'][$index]["free"]=$disk[3];
            $response['disk'][$index]["percent"]=$disk[4];
            $response['disk'][$index]["mountPoint"]=$disk[5];
        }

        // Estadisticas de red (bytes enviados y recibidos por la tarjeta de red principal)
        // bytes por sengundo de entrada y salida
        $response["network"] = array("card" => exec("route | grep '^default' | grep -o '[^ ]*$'"));
        $response["network"]["inBytes"] = exec("ifdata -sib `route | grep '^default' | grep -o '[^ ]*$'`");
        $response["network"]["outBytes"] = exec("ifdata -sob `route | grep '^default' | grep -o '[^ ]*$'`");

        $response["ogServices"] = array();
        $serviceNames = array("ogAdmServer", "ogAdmRepo", "ogAdmAgent");
        $ogService = array();
        foreach($serviceNames as $serviceName){
            // La informacion de los servicios se devuelven separados por , [nombre,cpu,etime,memoria]
            $cmd = exec("ps -ax -o comm= -o pcpu= -o etime= -o vsz= | tr -s \" \" \",\" | grep ".$serviceName);
            if($cmd) {
                $ogService = explode(",", $cmd);
                $ogService[4] = "online";
            }
            else{
                $ogService = array(0 => $serviceName, 1 => "0", 2 => "0", 3 => "0", 4 => "offline");
            }
            $response["ogServices"][] = array("status" => $ogService[4],"name" => $ogService[0], "cpu" => $ogService[1], "etime" => $ogService[2], "memory" => $ogService[3]);
        }

        return $response;
    }

    /**
     * Get the partitiontable, filesystems, operatingsystems
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @return JSON object with all data collected from server status (RAM, %CPU, etc.).
     */
    public function getEngineAction(Request $request)
    {
        $request->setRequestFormat($request->get('_format'));

        $appPath = $this->container->getParameter('kernel.root_dir');

        $content = file_get_contents($appPath.'/doc/engine.json');
        $json = json_decode($content, true);

        $json["commandtypes"] = CommandType::$options;

        $oglivecli = $this->container->getParameter('oglivecli');
        $cmd = $oglivecli. " show all";
        $oglive = shell_exec($cmd);
        $array = json_decode(json_encode(json_decode($oglive)), true);
        $json["ogliveinfo"] = $array;

        return $json;
    }
}
