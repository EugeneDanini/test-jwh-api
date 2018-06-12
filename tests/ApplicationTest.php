<?php

use Illuminate\Http\Response;

class ApplicationTest extends TestCase
{
    public function test404()
    {
        $this->get('404');

        $this->_testErrorResponseStructure(Response::HTTP_NOT_FOUND);
    }

    public function testIndex()
    {
        $this->get('/');

        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->response->getStatusCode());
        $this->assertEquals(env('APP_REPO_URL'), $this->response->headers->get('location'));
    }
}