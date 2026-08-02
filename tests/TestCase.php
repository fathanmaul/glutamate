<?php

declare(strict_types=1);

namespace Glutamate\Tests;

use Glutamate\GlutamateServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            GlutamateServiceProvider::class,
        ];
    }
}
