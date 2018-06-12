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
}
