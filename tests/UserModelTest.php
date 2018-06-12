<?php

use App\User;
use Firebase\JWT\JWT;

class UserModelTest extends TestCase
{
    public function testCreate()
    {
        $email = $this->getRandomEmail();
        $password = 'password';

        $user = User::create($email, $password);

        $this->assertInstanceOf('App\User', $user);
        $this->assertEquals($email, $user->email);
        $this->assertNotEquals($password, $user->getAuthPassword());
    }

    /**
     * @depends testCreate
     */
    public function testIsValidPassword()
    {
        $email = $this->getRandomEmail();
        $password = 'password';

        $user = User::create($email, $password);

        $this->assertTrue($user->isValidPassword($password));
    }

    /**
     * @depends testIsValidPassword
     */
    public function testUpdateCredentials()
    {
        $user = $this->createUser();
        $email = $this->getRandomEmail();
        $password = 'new_password';

        $user->updateCredentials($email, $password);

        $this->assertEquals($email, $user->email);
        $this->assertTrue($user->isValidPassword($password));
    }


    public function testGetByEmail()
    {
        $user = $this->createUser();

        $gotUser = User::getByEmail($user->email);

        $this->assertEquals($user->id, $gotUser->id);
    }

    public function testGetById()
    {
        $user = $this->createUser();

        $gotUser = User::getById($user->id);

        $this->assertEquals($user->id, $gotUser->id);
    }

    public function testGetJwtToken()
    {
        $user = $this->createUser();

        $token = $user->getJwtToken();

        $this->assertNotEmpty($token);
        $this->assertTrue(is_string($token));
    }

    /**
     * @depends testGetJwtToken
     * @depends testGetById
     */
    public function testGetByJwtToken()
    {
        $user = $this->createUser();
        $token = $user->getJwtToken();

        $gotUser = User::getByJwtToken($token);

        $this->assertEquals($user->id, $gotUser->id);
    }

    public function testGetByJwtTokenExpired()
    {
        $user = $this->createUser();

        $payload = [
            'iss' => env('APP_NAME'),
            'sub' => $user->id,
            'iat' => time() - 1,
            'exp' => time() - 1,
        ];
        $token =  JWT::encode($payload, env('JWT_SECRET'));

        $this->expectException('Firebase\JWT\ExpiredException');
        User::getByJwtToken($token);
    }

    public function testGetByJwtTokenInvalid()
    {
        $this->expectException('\UnexpectedValueException');
        User::getByJwtToken('Invalid token');
    }

    /**
     * @depends testGetById
     * @throws Exception
     */
    public function testDelete()
    {
        $user = $this->createUser();
        $id = $user->id;

        $user->delete();

        $this->assertNull(User::getById($id));
    }

}