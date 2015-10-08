<?php

namespace Selltag\NacexBundle\Client;

use Selltag\NacexBundle\Exceptions\NacexClientException;

class NacexClient
{
    const NACEX_SOAP_SCHEMA =  'http://schemas.xmlsoap.org/soap/envelope/';
    const NACEX_XPATH_RESULT = '//env:Envelope/env:Body/ns0:%sResponse/result';
    const CONNECTION_TIMEOUT = 600;
    const TRACE_ENABLED = 1;
    const EXCEPTIONS_ENABLED = 1;

    protected $soapClient;

    public function __construct($url)
    {
        $options = array(
            'location'     => $url,
            'trace'        => self::TRACE_ENABLED,
            'exceptions'   => self::EXCEPTIONS_ENABLED,
            'style'        => SOAP_DOCUMENT,
            'use'          => SOAP_LITERAL,
            'soap_version' => SOAP_1_1,
            'encoding'     => 'UTF-8',
            'connection_timeout' => self::CONNECTION_TIMEOUT
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
        $nodes = $xml->xpath(sprintf(self::NACEX_XPATH_RESULT, $action));

        return $nodes;
    }

    private function processResponse($action, $response)
    {
        $response = array();

        $obj = $this->soapClient->__getLastResponse();
        $nodes = $this->getResult($action, $obj);

        $this->checkErrors($nodes);

        foreach ($nodes as $key => $node) {
            $response[$key] = (string) $node;
        }

        return $response;
    }

    private function checkErrors($nodes)
    {
        if ($this->hasError($nodes)) {
            $errors = (string) $nodes[1];

            throw new NacexClientException($errors);
        }
    }

    private function hasError($nodes)
    {
        return ((string)$nodes[0]) == 'ERROR';
    }
}