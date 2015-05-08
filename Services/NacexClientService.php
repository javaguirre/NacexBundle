<?php

namespace Selltag\NacexBundle\Services;

use Selltag\NacexBundle\Exceptions\NacexClientException;
use Selltag\NacexBundle\Client\NacexClient;
use Selltag\NacexBundle\Serializer\NacexSerializer;

class NacexClientService
{
    protected $nacexClient;

    protected $credentials;

    public function __construct($nacexUsername, $nacexPassword, $nacexUrl)
    {
        $this->nacexClient = new NacexClient($nacexUrl);
        $this->credentials = array($nacexUsername, $nacexPassword);
    }

    public function __call($method, $arguments)
    {
        if (count($arguments) > 0) {
            $serializer = new NacexSerializer($this->credentials, $arguments[0]);
            $response = $this->nacexClient->execute(
                $method,
                $serializer->serialize()
            );

            return $response;
        }

        return $this->nacexClient->execute($method);
    }
}
