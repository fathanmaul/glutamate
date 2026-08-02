<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Entities Path
    |--------------------------------------------------------------------------
    |
    | The absolute directory path where your Entity classes are located.
    |
    */
    'entities_path' => app_path('Entities'),

    /*
    |--------------------------------------------------------------------------
    | Entities Namespace
    |--------------------------------------------------------------------------
    |
    | The base namespace mapping for your Entity classes.
    |
    */
    'entities_namespace' => 'App\\Entities',

    /*
    |--------------------------------------------------------------------------
    | Snapshot Path
    |--------------------------------------------------------------------------
    |
    | The directory path where the JSON snapshot of Entity classes is saved.
    |
    */
    'snapshot_path' => storage_path('framework/glutamate/snapshots'),

];
