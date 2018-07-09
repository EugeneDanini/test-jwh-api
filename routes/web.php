<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * @var Laravel\Lumen\Routing\Router $router
 */

$router->get('/', 'Controller@index');
$router->get('user', 'UserController@getList');
$router->post('user', 'UserController@add');
$router->get('user/{id}', 'UserController@get');
$router->put('user/{id}', 'UserController@update');
$router->delete('user/{id}', 'UserController@delete');
$router->post('user/login', 'UserController@login');
