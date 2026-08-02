<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        config([
            'glutamate.models_path' => realpath(__DIR__.'/../Models'),
            'glutamate.models_namespace' => 'Workbench\\App\\Models',
            'glutamate.snapshot_path' => realpath(__DIR__.'/../../').'/storage/framework/glutamate/snapshots',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
