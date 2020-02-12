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

$router->group(['prefix' => '/entities/{routingName}'], function ($router) {
    /** @var Laravel\Lumen\Routing\Router $router */
    $router->get('/content', 'ContentController@get');

    $router->group(['prefix' => '/schema'], function ($router) {
        /** @var Laravel\Lumen\Routing\Router $router */
        $router->get('/', 'EntityController@index');
        $router->get('/create', 'EntityController@create');
        $router->get('/{id}', 'EntityController@show');
        $router->post('/{id}', 'EntityController@handle');
    });
});

$router->get('/entities', 'EntitiesController');