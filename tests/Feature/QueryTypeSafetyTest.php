<?php

declare(strict_types=1);

namespace Glutamate\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('executes native Eloquent queries using Column objects on SQLite', function () {
    User::create([
        'name' => 'Lutfi',
        'email' => 'lutfi@example.com',
        'age' => 25,
    ]);

    User::create([
        'name' => 'Someone',
        'email' => 'someone@example.com',
        'age' => 30,
    ]);

    // Query using Column object directly as the column name
    $user = User::where(User::email(), 'lutfi@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBe('lutfi@example.com');
    expect((int) $user->age)->toBe(25);

    // Query with operator
    $olderUsers = User::where(User::age(), '>=', 30)->get();
    expect($olderUsers)->toHaveCount(1);
    expect($olderUsers->first()->email)->toBe('someone@example.com');
});
