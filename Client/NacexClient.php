<?php

namespace Selltag\NacexBundle\Client;

use Selltag\NacexBundle\Exceptions\NacexClientException;

class NacexClient
{
    const NACEX_SOAP_SCHEMA =  'http://schemas.xmlsoap.org/soap/envelope/';

    protected $soapClient;

    public function __construct($url)
    {
        $options = array(
            'location'     => $url,
            'trace'        => 1,
            'exceptions'   => 1,
            'style'        => SOAP_DOCUMENT,
            'use'          => SOAP_LITERAL,
            'soap_version' => SOAP_1_1,
            'encoding'     => 'UTF-8',
            'connection_timeout' => 600
        );

        $this->soapClient = new \SoapClient($url . '?wsdl', $options);
    }

    public function execute($method, $parameters)
    {
        $response = $this->soapClient->$method($parameters);

        return $this->processResponse($method, $response);
    }

    public function getResult($action, $obj)
    {
        $xml = simplexml_load_string($obj, null, null, self::NACEX_SOAP_SCHEMA);
        $xml->registerXPathNamespace('soap', self::NACEX_SOAP_SCHEMA);
        $xml->registerXPathNamespace(
            'ns', 'urn:soap/types'
        );
        $nodes = $xml->xpath(
            '//env:Envelope/env:Body/ns0:' . $action . 'Response/result'
        );

        return $nodes;
    }

    private function processResponse($action, $response)
    {
        $response = array();

        $obj = $this->soapClient->__getLastResponse();
        $nodes = $this->getResult($action, $obj);

        $this->checkErrors($nodes);

        foreach ($nodes as $key => $node) {
            $response[$key] = (string)$node;
        }

        return $response;
    }

    private function checkErrors($nodes)
    {
        if (((string)$nodes[0]) == 'ERROR') {
            $errors = (string)$nodes[1];

            throw new NacexClientException($errors);
        }
    }
}