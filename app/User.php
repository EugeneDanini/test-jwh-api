<?php

namespace App;

use UnexpectedValueException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Hash;

/**
 * @property int id
 * @property string email
 * @property string password
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Model implements AuthenticatableContract
{
    use Authenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @param string $email
     * @param string $password
     * @return User
     */
    public static function create(string $email, string $password)
    {
        $user = new self;
        $user->email = $email;
        $user->password = self::getPasswordHash($password);
        $user->save();

        return $user;
    }

    /**
     * @param string $email
     * @return User|null
     */
    public static function getByEmail(string $email)
    {
        return User::query()->where('email', $email)->get()->first();
    }

    /**
     * @param int $id
     * @return User|null
     */
    public static function getById(int $id)
    {
        return User::query()->where('id', $id)->get()->first();
    }

    /**
     * @param string $token
     * @return User
     * @throws ExpiredException
     * @throws UnexpectedValueException
     */
    public static function getByJwtToken(string $token)
    {
        $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        return self::getById($credentials->sub);
    }


    /**
     * @return string
     */
    public function getJwtToken() {
        $payload = [
            'iss' => env('APP_NAME'), // Issuer
            'sub' => $this->id, // Subject
            'iat' => time(), // Issued time
            'exp' => time() + env('JWT_TTL') // Expiration time
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /**
     * @param string $email
     * @param string $password
     * @return void
     */
    public function updateCredentials(string $email, string $password)
    {
        $this->email = $email;
        $this->password = self::getPasswordHash($password);
        $this->save();
    }

    /**
     * @param string $password
     * @return bool
     */
    public function isValidPassword(string $password)
    {
        return Hash::check($password, $this->getAuthPassword());
    }

    /**
     * @param string $password
     * @return string
     */
    public static function getPasswordHash(string $password)
    {
        return Hash::make($password);
    }

}
