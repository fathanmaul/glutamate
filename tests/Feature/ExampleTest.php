<?php

declare(strict_types=1);

use Glutamate\Glutamate;

it('resolves the singleton', function () {
    expect(app(Glutamate::class))->toBeInstanceOf(Glutamate::class);
});

it('returns the same instance from the container', function () {
    expect(app(Glutamate::class))->toBe(app(Glutamate::class));
});

it('merges the package config', function () {
    expect(config('glutamate.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('glutamate::messages.placeholder'))->toBe('Glutamate placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('glutamate::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('glutamate:placeholder')
        ->expectsOutputToContain('Glutamate placeholder command executed.')
        ->assertSuccessful();
});
