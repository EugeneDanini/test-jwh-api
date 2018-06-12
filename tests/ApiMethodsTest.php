<?php


use App\User;
use Illuminate\Http\Response;

class ApiMethodsTest extends TestCase
{
    public function testAdd()
    {
        $email = $this->getRandomEmail();
        $password = 'password';

        $this->post('user', ['email' => $email, 'password' => $password]);

        $this->_testResponseStructure(Response::HTTP_CREATED);
        $content = $this->response->getOriginalContent();
        $this->assertEquals($email, $content['data']['email']);
        $this->assertTrue(User::getById($content['data']['id'])->isValidPassword($password));
    }

    public function testLogin()
    {
        $password = 'password';
        $user = $this->createUser($password);

        $this->post('user/login', ['email' => $user->email, 'password' => $password]);

        $this->_testResponseStructure(Response::HTTP_OK);
        $content = $this->response->getOriginalContent();
        $this->assertArrayHasKey('token', $content['data']);
        $this->assertNotEmpty($content['data']['token']);
        $this->assertTrue(is_string($content['data']['token']));
    }

    public function testGetById()
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $this->get('user/' . $user->id, ['Authorization' => $token]);

        $this->_testResponseStructure(Response::HTTP_OK);
        $content = $this->response->getOriginalContent();
        $this->assertEquals($user->id, $content['data']['id']);
    }

    public function testGetList()
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $this->get('user/' . $user->id, ['Authorization' => $token]);

        $this->_testResponseStructure(Response::HTTP_OK);
        $content = $this->response->getOriginalContent();
        $this->assertInternalType('array', $content['data']);
        $this->assertGreaterThanOrEqual(1, count($content['data']));
    }

    public function testUpdate()
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();
        $newEmail = $this->getRandomEmail();
        $password = 'new_password';

        $this->put('user/' . $user->id, ['email' => $newEmail, 'password' => $password], ['Authorization' => $token]);

        $this->_testResponseStructure(Response::HTTP_ACCEPTED);
        $content = $this->response->getOriginalContent();
        $this->assertEquals($user->id, $content['data']['id']);
        $this->assertEquals($newEmail, $content['data']['email']);
        $this->assertTrue(User::getById($user->id)->isValidPassword($password));
    }

    public function testDelete()
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $this->delete('user/' . $user->id, [], ['Authorization' => $token]);

        $this->_testResponseStructure(Response::HTTP_ACCEPTED);
    }

    /**
     * @dataProvider providerInvalidRequestCredentials
     * @param string $method
     * @param string $endpoint
     */
    public function testInvalidRequestCredentials(string $method, string $endpoint)
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $this->{$method}($endpoint, ['email' => 'invalid_email', 'password' => null], ['Authorization' => $token]);

        $this->_testErrorResponseStructure(Response::HTTP_BAD_REQUEST);
    }

    public function providerInvalidRequestCredentials()
    {
        return [
            'CREATE' => ['post', 'user'],
            'LOGIN' => ['post', 'user/login'],
            'UPDATE' => ['put', 'user/0'],
        ];
    }

    /**
     * @dataProvider providerInvalidRequestCredentials
     * @param string $method
     * @param string $endpoint
     */
    public function testDuplicateEmail(string $method, string $endpoint)
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $this->{$method}($endpoint, ['email' => $user->email, 'password' => 'password'], ['Authorization' => $token]);

        $this->_testErrorResponseStructure(Response::HTTP_BAD_REQUEST);
    }

    public function providerDuplicateEmail()
    {
        return [
            'CREATE' => ['post', 'user'],
            'UPDATE' => ['put', 'user/0'],
        ];
    }

    /**
     * @dataProvider providerInvalidToken
     * @param string $method
     * @param string $endpoint
     * @param bool $isNeedSendData
     */
    public function testInvalidToken(string $method, string $endpoint, bool $isNeedSendData)
    {
        if ($isNeedSendData) {
            $this->{$method}($endpoint,
                ['email' => $this->getRandomEmail(), 'password' => 'password'],
                ['Authorization' => 'invalid token']
            );
        } else {
            $this->{$method}($endpoint, ['Authorization' => 'invalid token']);
        }

        $this->_testErrorResponseStructure(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @dataProvider providerInvalidToken
     * @param string $method
     * @param string $endpoint
     * @param bool $isNeedSendData
     * @throws Exception
     */
    public function testUserNotFoundByToken(string $method, string $endpoint, bool $isNeedSendData)
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();
        $user->delete();

        if ($isNeedSendData) {
            $this->{$method}($endpoint,
                ['email' => $this->getRandomEmail(), 'password' => 'password'],
                ['Authorization' => $token]
            );
        } else {
            $this->{$method}($endpoint, ['Authorization' => $token]);
        }

        $this->_testErrorResponseStructure(Response::HTTP_NOT_FOUND);
    }

    public function providerInvalidToken()
    {
        return [
            'GET' => ['get', 'user', false],
            'GET_BY_ID' => ['get', 'user/0', false],
            'UPDATE' => ['put', 'user/0', true],
            'DELETE' => ['delete', 'user/0', true],
        ];
    }

    /**
     * @dataProvider providerUserNotFound
     * @param string $method
     * @param string $endpoint
     * @param bool $isNeedSendData
     * @throws Exception
     */
    public function testUserNotFound(string $method, string $endpoint, bool $isNeedSendData)
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        if ($isNeedSendData) {
            $this->{$method}($endpoint,
                ['email' => $this->getRandomEmail(), 'password' => 'password'],
                ['Authorization' => $token]
            );
        } else {
            $this->{$method}($endpoint, ['Authorization' => $token]);
        }

        $this->_testErrorResponseStructure(Response::HTTP_NOT_FOUND);
    }

    public function providerUserNotFound()
    {
        return [
            'GET_BY_ID' => ['get', 'user/0', false],
            'UPDATE' => ['put', 'user/0', true],
            'DELETE' => ['delete', 'user/0', true],
        ];
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