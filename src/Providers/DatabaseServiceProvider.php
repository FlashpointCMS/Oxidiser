<?php

namespace Flashpoint\Oxidiser\Providers;

use Flashpoint\Fuel\Migrations\MongodbServiceProvider;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

/**
 * Class RouteServiceProvider
 * @package Flashpoint\Oxidiser\Providers
 *
 * @property \Laravel\Lumen\Application $app
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(MongodbServiceProvider::class);
        $this->app->withEloquent();
    }

    public function boot()
    {
        if($this->app->runningInConsole()) {
            tap($this->app->make('migrator'), function (Migrator $migrator) {
                $migrator->path(__DIR__ . '/../Migrations');
                $migrator->path($this->app->basePath('/app/Migrations'));
            });
        }
    }
}
