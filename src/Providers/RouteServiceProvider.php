<?php

namespace Flashpoint\Oxidiser\Providers;

use Flashpoint\Fuel\Routing;
use Flashpoint\Oxidiser\Http\Middleware\Authenticate;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Spatie\Cors\Cors;
use Spatie\Cors\CorsServiceProvider;

/**
 * Class RouteServiceProvider
 * @package Flashpoint\Oxidiser\Providers
 *
 * @property \Laravel\Lumen\Application $app
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Flashpoint\Oxidiser\Http\Controllers';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CorsServiceProvider::class);
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->middleware([
            Cors::class
        ]);
        $this->app->routeMiddleware([
            'auth' => Authenticate::class,
        ]);
        $this->mapFlashpointRoutes($this->app->make('config'), $this->app->make('router'));

        $this->app->bind(Routing::class, function ($app) {
            /** @var Collection $routings */
            $routings = $app['routings'];
            /** @var Laravel\Lumen\Http\Request $request */
            $request = $app['request'];

            return $routings
                    ->firstWhere('name',
                        $request->route('routingName')
                    )
                ?? abort(404, 'Routing not found');
        });
    }

    /**
     * Define the Flashpoint routes for doing it's thing.
     *
     * @param Repository $config
     * @param Laravel\Lumen\Routing\Router $router
     * @return void
     */
    protected function mapFlashpointRoutes($config, $router)
    {
        $router->group([
            'namespace' => 'Flashpoint\Oxidiser\Http\Controllers',
            'prefix' => $config->get('flashpoint.service_url', '/flashpoint') . '/service',
            'middleware' => [$config->get('flashpoint.guard', 'auth')]
        ], function ($router) {
            require __DIR__ . '/../routes.php';
        });
    }
}
