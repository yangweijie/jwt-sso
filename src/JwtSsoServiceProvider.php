<?php

namespace yangweijie\jwt;

use Illuminate\Support\ServiceProvider;

class JwtSsoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (! app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/jwtSso.php', 'jwt');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/jwtSso.php' => config_path('jwt.php'),
        ], 'jwtSso-config');

        //
        $this->defineRoutes();
        //        $this->configureGuard();
        //        $this->configureMiddleware();
    }

    public function defineRoutes()
    {
        if (app()->routesAreCached()) {
            return;
        }
        $this->loadRoutesFrom(__DIR__.'/../resources/route/sso.php');
    }
}
