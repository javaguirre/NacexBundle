<?php

namespace Selltag\NacexBundle\Serializer;

use Selltag\NacexBundle\Exceptions\NacexClientException;

class NacexSerializer
{
    const STRING_PARAM = 'String';
    const ARRAY_PARAM = 'arrayOfString';

    protected $data;

    public function __construct($credentials, $parameters)
    {
        $this->data = array_merge($credentials, $parameters);
    }

    public function serialize()
    {
        return $this->serializeData($this->data);
    }

    private function serializeData($params)
    {
        $dataParameters = array();
        $count = 1;

        foreach ($params as $param) {
            $nextKey = $this->nextKey($param, $count);
            $dataParameters[$nextKey] = $this->setDataParameter($param);
            $count++;
        }

        return $dataParameters;
    }

    private function setDataParameter($param)
    {
        $dataResult = array();

        if (is_string($param)) {
            return $param;
        }

        if (is_array($param)) {
            foreach ($param as $key => $elem) {
                if (is_null($elem)) {
                    $dataResult[] = '';
                } else {
                    $dataResult[] = implode('=', array($key, $elem));
                }
            }
        }

        if (empty($dataResult)) {
            $dataResult = '';
        }

        return $dataResult;
    }

    private function nextKey($param, $count)
    {
        if (!is_array($param) && !is_string($param) && !is_null($param)) {
            throw new NacexClientException("The parameter $param is not valid");
        }

        $paramType = is_array($param) ? self::ARRAY_PARAM : self::STRING_PARAM;
        $validParameter = $this->setKey($param, $count, $paramType);

        return $validParameter;
    }

    /**
     * Calculates the next key needed for a correct
     * Nacex SOAP request
     *
     * @param string $param
     *
     * @return string
     */
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
}