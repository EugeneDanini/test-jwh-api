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

$router->get('/', 'controller@index');
$router->get('user', 'userController@getList');
$router->post('user', 'userController@add');
$router->get('user/{id}', 'userController@get');
$router->put('user/{id}', 'userController@update');
$router->delete('user/{id}', 'userController@delete');
$router->post('user/login', 'userController@login');