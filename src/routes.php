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

$router->group(['prefix' => '/entities/{routingName}', 'as' => 'fp.entities.'], function ($router) {
    /** @var Laravel\Lumen\Routing\Router $router */
    $router->get('/content', ['uses' => 'ContentController@get', 'as' => 'content.index']);

    $router->group(['prefix' => '/schema', 'as' => 'schema.'], function ($router) {
        /** @var Laravel\Lumen\Routing\Router $router */
        $router->get('/', ['uses' => 'EntityController@index', 'as' => 'index']);
        $router->post('/create', ['uses' => 'EntityController@create', 'as' => 'create']);
        $router->get('/{id}[/{sequence}]', ['uses' => 'EntityController@show', 'as' => 'show']);
        $router->post('/{id}', ['uses' => 'EntityController@handle', 'as' => 'handle']);
    });
});

$router->get('/entities', 'EntitiesController');