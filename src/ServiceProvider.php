<?php

namespace Armincms\DatabaseLocalization;
 
use Illuminate\Translation\TranslationServiceProvider;

class ServiceProvider extends TranslationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {    
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/database-localization.php', 'database-localization'); 
    }  

    /**
     * Register the translation line loader.
     *
     * @return void
     */
    protected function registerLoader()
    { 
        $this->app->singleton('translation.loader', function ($app) {
            return new DatabaseLoader($app[Store::class], $app['files'], $app['path.lang']);
        });

        $this->app->singleton(Store::class, function ($app) {
            return new DatabaseStore($app['db'], $app['cache']);
        });

        $this->commands([
            Commands\SyncCommand::class,
        ]);
    }
}
