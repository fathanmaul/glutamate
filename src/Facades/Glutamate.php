<?php

declare(strict_types=1);

namespace Glutamate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Glutamate\Glutamate
 */
class Glutamate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Glutamate\Glutamate::class;
    }
}
