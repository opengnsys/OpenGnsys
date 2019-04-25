<?php

namespace Opengnsys\CoreBundle\OAuth;

use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

class OgClientGrantExtension implements GrantExtensionInterface
{
    private $clientManager;

    public function __construct($clientManager)
    {
        $this->clientManager = $clientManager;
    }

    /*
     * {@inheritdoc}
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        // Check that the input data is correct
        if (!isset($inputData['ip']) || !isset($inputData['mac'])|| !isset($inputData['token'])) {
            return false;
        }

        $repository = $this->clientManager->getRepository();
        $client = $repository->findOneBy(['ip'=>$inputData['ip'], 'mac'=>$inputData['mac']]);

        if (!$client) {
            return false;
        }
        else {
            $token = $inputData['token'];
            $client->setAgentToken($token);
            $this->clientManager->persist($client);

            return array(
                // Only User
                //'data' => $client
            );
        }



        return false; // No number guessed, the grant will fail
    }
}