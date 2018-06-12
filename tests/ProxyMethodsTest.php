<?php

use Illuminate\Http\Response;

class ProxyMethodsTest extends TestCase
{
    public function testJsonPlaceholderProxy()
    {
        $this->get('proxy/placeholder/1');

        $this->_testResponseStructure(Response::HTTP_OK);
    }

    public function testJsonPlaceholderProxyNotFound()
    {
        $this->get('proxy/placeholder/0');

        $this->_testErrorResponseStructure(Response::HTTP_NOT_FOUND);
    }
}