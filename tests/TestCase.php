<?php

use App\User;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * @param string $password
     * @return User
     */
    public function createUser(string $password = '')
    {
        if (!$password) {
            $password = uniqid();
        }
        $user = new User;
        $user->email = $this->getRandomEmail();
        $user->password = Hash::make($password);
        $user->save();

        return $user;
    }

    /**
     * @return string
     */
    public function getRandomEmail()
    {
        return uniqid('unit_') . '@example.com';
    }

    /**
     * @param int $code
     * @param bool $isSuccess
     */
    protected function _testResponseStructure(int $code, bool $isSuccess = true)
    {
        $content = $this->response->getOriginalContent();
        $this->assertEquals($code, $this->response->getStatusCode());
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('is_success', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertInternalType('array', $content['data']);
        $this->assertEquals($isSuccess, $content['is_success']);
    }

    /**
     * @param int $code
     */
    protected function _testErrorResponseStructure(int $code)
    {
        $this->_testResponseStructure($code, false);
        $content = $this->response->getOriginalContent();
        $this->assertArrayHasKey('errors', $content['data']);
        $this->assertInternalType('array', $content['data']['errors']);
        $this->assertGreaterThanOrEqual(1, count($content['data']['errors']));
    }
}
