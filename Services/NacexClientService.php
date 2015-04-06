<?php

namespace Selltag\NacexBundle\Services;

use Selltag\NacexBundle\Exceptions\NacexClientException;

class NacexClientService
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
        $this->credentials = array($nacexUsername, $nacexPassword);
    }

    private function setRequestParameters($params)
    {
        $requestParameters = array();
        $count = 1;

        foreach ($params as $param) {
            $nextKey = $this->nextKey($param, $count);
            $requestParameters[$nextKey] = $this->setRequestParameter($param);
            $count++;
        }

        return $requestParameters;
    }

    private function setRequestParameter($param)
    {
        $dataResult = array();

        if (is_string($param)) {
            return $param;
        }

        if (is_array($param)) {
            foreach ($param as $key => $elem) {
                $dataResult[] = implode('=', array($key, $elem));
            }
        }

        return $dataResult;
    }

    private function nextKey($param, $count)
    {
        if (!is_array($param) && !is_string($param)) {
            throw new NacexClientException("The parameter $param is not valid");
        }

        $paramType = is_array($param) ? self::ARRAY_PARAM : self::STRING_PARAM;
        $validParameter = $this->setKey($param, $count, $paramType);

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

            throw new NacexClientException($errors);
        }
    }

    public function __call($method, $arguments)
    {
        if (count($arguments) > 0) {
            $requestParameters = $this->setRequestParameters(
                array_merge($this->credentials, $arguments)
            );
            $response = $this->nacexClient->$method($requestParameters);

            return $this->processResponse($method, $response);
        }

        return $this->nacexClient->$method();
    }
}