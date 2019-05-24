<?php

namespace Opengnsys\ServerBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * CurlService
 */
class CurlService
{
    private $em;
    private $router;
    private $logger;

    public function __construct($em, $router, $logger)
    {
        $this->em = $em;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function sendCurl($url, $trace, $script, $sendConfig)
    {
        $client = $trace->getClient();

        $ip = $client->getIp();
        $agentToken = $client->getAgentToken();
        $url = "https://".$ip.":8000/opengnsys/".$url;

        $redirectUri = $this->router->generate('opengnsys_server__api_post_traces', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        $arrayToPost = array(
            'id' => $trace->getId(),
            'script' => base64_encode( $script),
            'ip' => $ip,
            'sendConfig'=> ($sendConfig)?"true":"false",
            'redirectUri' => $redirectUri
        ); // this will be json_encode. If you don't want to json_encode, use HttpPostJson instead of HttpPostJsonBody

        $headers[] = "Authorization: ".$agentToken;

        //$postUrl = http_build_query ($arrayToPost);
        $postUrl = json_encode ($arrayToPost);

        //$postUrl = base64_encode ($postUrl); //json_encode($arrayToPost);

        $this->logger->info("SEND CURL url: " . $url);
        $this->logger->info("SEND CURL id: " . $trace->getId());
        $this->logger->info("SEND CURL script: " . $script);
        $this->logger->info("SEND CURL ip: " . $ip);
        $this->logger->info("SEND CURL token: " . $agentToken);
        $this->logger->info("SEND CURL redirect_uri: " . $redirectUri);
        $this->logger->info("SEND CURL postUrl: " .$postUrl);

        // _GET
        //$url = $url."?".$postUrl;

        // abrimos la sesión cURL
        $ch = curl_init();

        // definimos la URL a la que hacemos la petición
        curl_setopt($ch, CURLOPT_URL,$url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // _POST_
        curl_setopt($ch, CURLOPT_POST, true); // indicamos el tipo de petición: POST
        // definimos cada uno de los parámetros
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postUrl);

        // recibimos la respuesta y la guardamos en una variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        curl_setopt($ch, CURLOPT_TIMEOUT, 20); //timeout in seconds

        $remote_server_output = curl_exec ($ch);
        $remote_server_error = "";

        $this->logger->info("SEND CURL output: " . $remote_server_output);

        if (curl_errno($ch)) {
            // this would be your first hint that something went wrong
            $remote_server_error = curl_error($ch);
            $this->logger->info("SEND CURL error: ". $remote_server_error);

            $client->setStatus("off");
            $this->em->flush();
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->logger->info("SEND CURL status code: ". $statusCode);

        // cerramos la sesión cURL
        curl_close ($ch);

        $output['id'] = $client->getId();
        $output['name'] = $client->getName();
        $output['statusCode'] = $statusCode;
        $output['output'] = $remote_server_output;
        $output['error'] = $remote_server_error;

        return $output;
    }
}
