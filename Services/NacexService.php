<?php

namespace Selltag\Bundle\NacexBundle\Services;

class NacexService
{
    const NACEX_SOAP_SCHEMA =  'http://schemas.xmlsoap.org/soap/envelope/';

    const STRING_PARAM = 'String';
    const ARRAY_PARAM = 'arrayOfString';

    public function __construct($nacexUsername, $nacexPassword, $nacexUrl)
    {
        $options = array(
            'location'     => $nacexUrl,
            'trace'        => 1,
            'exceptions'   => 1,
            'style'        => SOAP_DOCUMENT,
            'use'          => SOAP_LITERAL,
            'soap_version' => SOAP_1_1,
            'encoding'     => 'UTF-8',
            'connection_timeout' => 600
        );

        $this->nacexClient = new \SoapClient($nacexUrl . '?wsdl', $options);
        $this->credentials = array(
            'String_1' => $nacexUsername,
            'String_2' => $nacexPassword
        );
    }

    private function setRequestParameters($params)
    {
        $requestParameters = array();
        $count = 1;

        foreach ($params as $param) {
            $nextKey = $this->buildKey($param, count);
            $requestParameters[$nextKey] = $this->setRequestParameter($param);

            $count++;
        }
    }

    private function nextKey($param, $count)
    {
        $validParameter = null;

        if (is_array($param)) {
            $validParameter = $this->setKey($param, $count, self::STRING_PARAM);
        } elseif (is_string($param)) {
            $validParameter = $this->setKey($param, $count, self::ARRAY_PARAM);
        }

        if ($validParameter === null) {
            throw new NacexException("The parameter $param is not valid");
        }

        return $validParameter;
    }

    private function setKey($param, $count, $type)
    {
        return implode(
            '_',
            array(
                $type,
                (string)$count
            )
        );
    }

    private function processResponse($action, $response)
    {
        $response = array();

        $obj = $this->nacexClient->__getLastResponse();
        $nodes = $this->getXmlResult($action, $obj);

        $this->checkErrors($nodes);

        foreach ($nodes as $key => $node) {
            $response[$key] = (string)$node;
        }

        return $response;
    }

    private function getXmlResult($action, $obj)
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

    private function checkErrors($nodes)
    {
        if (((string)$nodes[0]) == 'ERROR') {
            $errors = (string)$nodes[1];

            throw new NacexException($errors);
        }
    }

    public function __call($name, $arguments)
    {
        if (count($arguments) > 1) {
            return call_user_func_array(
                array(
                    $this,
                    $name
                ),
                array_slice($arguments, 1)
            );
        }

        return $this->$name();
    }
}