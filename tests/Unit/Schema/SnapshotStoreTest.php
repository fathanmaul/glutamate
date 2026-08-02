<?php

declare(strict_types=1);

namespace Glutamate\Tests\Unit\Schema;

use Glutamate\Schema\SchemaSnapshot;
use Glutamate\Schema\SnapshotStore;
use Illuminate\Support\Facades\File;

it('returns null if file does not exist', function () {
    $tempDir = sys_get_temp_dir().'/glutamate_tests_'.uniqid();
    $store = new SnapshotStore($tempDir);

    expect($store->load('NonExistentClass'))->toBeNull();
});

it('saves and loads snapshot correctly', function () {
    $tempDir = sys_get_temp_dir().'/glutamate_tests_'.uniqid();
    $store = new SnapshotStore($tempDir);

    $snapshot = new SchemaSnapshot(
        modelClass: 'App\\Entities\\Post',
        table: 'posts',
        columns: [
            'title' => [
                'type' => 'StringColumn',
                'nullable' => false,
                'hasDefault' => false,
                'default' => null,
                'unique' => false,
                'index' => false,
                'meta' => ['maxLength' => 200],
            ],
        ],
    );

    $store->save($snapshot);

    // Verify file exists with correct slugged name
    $expectedPath = $tempDir.'/App.Entities.Post.json';
    expect(file_exists($expectedPath))->toBeTrue();

    $loaded = $store->load('App\\Entities\\Post');
    expect($loaded)->not->toBeNull();
    expect($loaded->modelClass)->toBe($snapshot->modelClass);
    expect($loaded->table)->toBe($snapshot->table);
    expect($loaded->columns)->toBe($snapshot->columns);

    // Cleanup
    File::deleteDirectory($tempDir);
});
