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

    public function testInitEmptyData()
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
}