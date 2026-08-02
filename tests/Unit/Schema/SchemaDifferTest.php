<?php

declare(strict_types=1);

namespace Glutamate\Tests\Unit\Schema;

use Glutamate\Schema\SchemaDiffer;
use Glutamate\Schema\SchemaSnapshot;

it('detects all columns as added when previous snapshot is null', function () {
    $current = new SchemaSnapshot(
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

    $diff = SchemaDiffer::diff(null, $current);

    expect($diff->isEmpty())->toBeFalse();
    expect($diff->added)->toHaveKey('title');
    expect($diff->changed)->toBeEmpty();
    expect($diff->removed)->toBeEmpty();
});

it('detects additions, changes, and removals correctly', function () {
    $previous = new SchemaSnapshot(
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
            'status' => [
                'type' => 'StringColumn',
                'nullable' => false,
                'hasDefault' => true,
                'default' => 'draft',
                'unique' => false,
                'index' => false,
                'meta' => ['maxLength' => 50],
            ],
            'removed_col' => [
                'type' => 'IntColumn',
                'nullable' => true,
                'hasDefault' => false,
                'default' => null,
                'unique' => false,
                'index' => false,
                'meta' => ['size' => 'default', 'unsigned' => false, 'autoIncrement' => false],
            ],
        ],
    );

    $current = new SchemaSnapshot(
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
            'status' => [
                'type' => 'StringColumn',
                'nullable' => true, // changed
                'hasDefault' => true,
                'default' => 'draft',
                'unique' => false,
                'index' => false,
                'meta' => ['maxLength' => 50],
            ],
            'added_col' => [ // added
                'type' => 'IntColumn',
                'nullable' => false,
                'hasDefault' => true,
                'default' => 0,
                'unique' => false,
                'index' => false,
                'meta' => ['size' => 'default', 'unsigned' => true, 'autoIncrement' => false],
            ],
        ],
    );

    $diff = SchemaDiffer::diff($previous, $current);

    expect($diff->isEmpty())->toBeFalse();
    expect($diff->added)->toHaveKey('added_col');
    expect($diff->removed)->toContain('removed_col');
    expect($diff->changed)->toHaveKey('status');
    expect($diff->changed['status']['from']['nullable'])->toBeFalse();
    expect($diff->changed['status']['to']['nullable'])->toBeTrue();
});

it('returns empty diff if snapshots are identical', function () {
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

    $diff = SchemaDiffer::diff($snapshot, $snapshot);

    expect($diff->isEmpty())->toBeTrue();
});
