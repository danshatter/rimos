<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

/**
 * Public routes
 */
$router->post('/register', 'UserController@register');
$router->post('/login', 'UserController@login');
$router->delete('/users/{userId}', 'UserController@destroy');
$router->post('/departments/{departmentId}', 'DepartmentController@store');
$router->post('/users/{userId}/department', 'UserController@storeDepartment');

/**
 * Authenticated routes
 */
$router->group([
    'middleware' => 'auth'
], function() use ($router) {
    $router->get('/departments', 'UserController@departments');
    $router->put('/profile', 'UserController@update');
});