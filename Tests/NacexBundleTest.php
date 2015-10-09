<?php

namespace Selltag\NacexBundle\Tests;

use Selltag\NacexBundle\Serializer\NacexSerializer;

class NacexBundleTests extends \PHPUnit_Framework_TestCase
{
    protected $credentials;

    public function __construct()
    {
        $this->credentials = array('MYUSER', 'MY PASSWORD');
    }

    public function testInitEmptyDataSerializer()
    {
        $serializer = new NacexSerializer($this->credentials, array());
        $nacexData = array(
            'String_1' => $this->credentials[0],
            'String_2' => $this->credentials[1]
        );

        $this->assertEquals(
            $serializer->serialize(),
            $nacexData
        );
    }

    public function testPutRecogidaSerializer()
    {
        $data = array(
            array(
                'delcli' => '34455',
                'numcli' => '123456'
            )
        );

        $serializer = new NacexSerializer($this->credentials, $data);
        $nacexData = array(
            'String_1' => $this->credentials[0],
            'String_2' => $this->credentials[1],
            'arrayOfString_3' => array(
                sprintf('delcli=%s', $data[0]['delcli']),
                sprintf('numcli=%s', $data[0]['numcli'])
            )
        );

        $this->assertEquals(
            $serializer->serialize(),
            $nacexData
        );
    }

    public function testEstadoRecogidaSerializer()
    {
        $data = array(
            'reco_codigo' => null,
            'Del_Sol'     => '0001',
            'Num_Rec'     => '12345678',
            'ref'         => null
        );

        $serializer = new NacexSerializer($this->credentials, $data);
        $nacexData = array(
            'String_1' => $this->credentials[0],
            'String_2' => $this->credentials[1],
            'String_3' => '',
            'String_4' => $data['Del_Sol'],
            'String_5' => $data['Num_Rec'],
            'String_6' => '',
        );

        $this->assertEquals(
            $serializer->serialize(),
            $nacexData
        );
    }
}