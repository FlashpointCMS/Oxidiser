<?php

namespace Flashpoint\Oxidiser\Providers;

use Dusterio\LumenPassport\LumenPassport;
use Dusterio\LumenPassport\PassportServiceProvider as LumenPassportServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\PassportServiceProvider as BasePassportServiceProvider;

/**
 * Class AuthServiceProvider
 * @package Flashpoint\Oxidiser\Providers
 *
 * @property \Laravel\Lumen\Application $app
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(BasePassportServiceProvider::class);
        $this->app->register(LumenPassportServiceProvider::class);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Repository $config */
        $config = $this->app->make('config');
        LumenPassport::routes($this->app, [
            'prefix' => $config->get('flashpoint.service_url', '/flashpoint') . '/auth'
        ]);
    }
}
