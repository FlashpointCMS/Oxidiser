<?php

/** @var Laravel\Lumen\Routing\Router $router */
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

$router->group(['prefix' => '/{routingName}'], function ($router) {
    $router->get('/content', 'ContentController@get');

    $router->group(['prefix' => '/entity'], function ($router) {
        $router->get('/', 'EntityController@index');
        $router->get('/create', 'EntityController@create');
        $router->get('/{id}', 'EntityController@show');
        $router->post('/{id}', 'EntityController@handle');
    });
});
