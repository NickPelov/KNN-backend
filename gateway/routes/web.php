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
$router->group(['prefix' => 'v1', 'namespace' => 'v1'], function () use ($router) {

    $router->get('/test', "TestController@test"); //up-to-date

    $router->post('/user/register', 'Authentication\RegisterController@Register'); //up-to-date

    $router->post('/user/login', 'Authentication\LoginController@Login'); //up-to-date

    $router->post('/user/activate', 'Authentication\RegisterController@Activate'); //up-to-date

    $router->post('/user/password/reset', 'Authentication\PasswordController@ResetPassword');

    $router->post('/user/password/new', 'Authentication\PasswordController@SetNewPassword');

    //protected routes
    $router->group(['middleware' => 'verify'], function () use ($router) {

        $router->patch('/details/user', "UserController@updateUserDetails"); //up-to-date

        $router->post('/details/company', "CompanyController@createCompany"); //up-to-date


        
        //sockets
        $router->group(['prefix' => 'chat'], function () use ($router) {
            $router->post('/create', "MessageController@createConversation"); //up-to-date
        });

        //sockets
        $router->group(['prefix' => 'sockets'], function () use ($router) {

        });
    });
});
