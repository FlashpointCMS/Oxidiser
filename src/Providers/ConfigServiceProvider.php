<?php

namespace Flashpoint\Oxidiser\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;

/**
 * Class RouteServiceProvider
 * @package Flashpoint\Oxidiser\Providers
 *
 * @property \Laravel\Lumen\Application $app
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig($this->app->make('config'), 'database');
        $this->registerConfig($this->app->make('config'), 'auth');
        $this->registerConfig($this->app->make('config'), 'flashpoint');
    }

    public function registerConfig(Repository $config, $path)
    {
        $this->app->configure($path);
        if(!file_exists($appConfig = $this->app->configPath("{$path}.php"))) {
            $config->set($path, require __DIR__ . "/../Config/{$path}.php");
        }
    }
}
