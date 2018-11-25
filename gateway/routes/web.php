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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/user/register', 'Authentication\RegisterController@Register');

$router->post('/user/login', 'Authentication\LoginController@Login');

$router->post('/user/activate', 'Authentication\RegisterController@Activate');

$router->post('/user/verify', 'Authentication\LoginController@Verify');

$router->post('/user/password/reset', 'Authentication\PasswordController@ResetPassword');

$router->post('/user/password/new', 'Authentication\PasswordController@SetNewPassword');
