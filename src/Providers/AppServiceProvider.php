<?php

namespace Flashpoint\Oxidiser\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Cors\CorsServiceProvider;

/**
 * Class AppServiceProvider
 * @package Flashpoint\Oxidiser\Providers
 *
 * @property \Laravel\Lumen\Application $app
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CorsServiceProvider::class);
        $this->app->singleton('routings', function() {
            return collect(require $this->app->basePath('/app/routing.php'));
        });
    }
}
