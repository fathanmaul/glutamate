<?php

declare(strict_types=1);

namespace Glutamate;

use Glutamate\Console\Commands\GlutamateCommand;
use Illuminate\Support\ServiceProvider;

class GlutamateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/glutamate.php', 'glutamate');

        $this->app->singleton(Glutamate::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/glutamate.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'glutamate');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'glutamate');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/glutamate.php' => config_path('glutamate.php'),
        ], ['glutamate', 'glutamate-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/glutamate'),
        ], ['glutamate', 'glutamate-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/glutamate'),
        ], ['glutamate', 'glutamate-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/glutamate'),
        ], ['glutamate', 'glutamate-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['glutamate', 'glutamate-migrations']);

        $this->commands([
            GlutamateCommand::class,
        ]);
    }
}
