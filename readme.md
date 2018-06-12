# Test JWH API

## Requirements
Requires: PHP >=7.1.3, composer, SQLite database driver, curl

## Installation
Clone repository
```bash
git clone https://github.com/EugeneDanini/test-jwh-api.git
```
Change working directory
```bash
cd test-jwh-api
```

Make sure that the .env file with config exists in directory. Development and production config files are provided as .env.development and .env.production in repository.

Run composer
```bash
composer install
```
Create database named database.sqlite in ./database directory
```bash
sqlite3 database/database.sqlite
```
Run migration
```bash
php artisan migrate
```
Start web server
```bash
php -S localhost:8000 -t public
```
Run tests
```bash
php ./vendor/phpunit/phpunit/phpunit --no-configuration tests
```

## Entities
### User
```json
{
"email":"test@example.com",
"updated_at":"2018-06-12 18:37:02",
"created_at":"2018-06-12 18:37:02",
"id":603
}
```
### Proxy result
```json
{
"userId": 1,
"id": 1,
"title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
"body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}
```

## Responses
Responses are always JSON with applicable status code (2** for success, 4** for errors).
### Success
```json
{
  "data": {
  },
  "is_success": true
}
```
### Error
```json
{
  "data": {
    "errors": []
  },
  "is_success": false
}
```

## Methods

### User API

##### POST /user
Create user.

**Returns:** created user entity.

**Required:** x-form-urlencoded params:
* email
* password

***

##### POST /user/login
Authorize user.

**Returns:** user JWT.

**Required:** x-form-urlencoded params:
* email
* password

***

##### GET /user
Get users list.

**Returns:** list of user entities.

**Required:** JWT header:
- *Authorization: token*

***

##### GET /user/%id%
Get user by given id.

**Returns:** user entity.

**Required:** JWT header:
- *Authorization: token*

***

##### PUT /user/%id%
Update user credentials by given id.

**Returns:** updated user entity.

**Required:** JWT header:
- *Authorization: token*

**Required:** x-form-urlencoded params:
* email
* password

***

##### DELETE /user/%id%
Update user by given id.

**Returns:** empty result.

**Required:** JWT header:
- *Authorization: token*

***

### Proxy API

##### GET /proxy/placeholder/%id%
Get data from third party source (https://jsonplaceholder.typicode.com) by given id.

**Returns:** proxy result.

***

## Notes

- Built-in PHP web server recommended only for development purposes;

- Production application should work over HTTPS.