<?php

declare(strict_types=1);

namespace Glutamate\Tests\Unit\Schema;

use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Glutamate\Entity;
use Glutamate\Schema\SchemaSnapshot;

class SnapshotTestEntity extends Entity
{
    public static function table(): string
    {
        return 'snapshot_test_table';
    }

    public static function title(): StringColumn
    {
        return StringColumn::make()->maxLength(200);
    }

    public static function views(): IntColumn
    {
        return IntColumn::make()->unsigned()->default(0);
    }
}

it('creates snapshot from entity class correctly', function () {
    $snapshot = SchemaSnapshot::fromEntity(SnapshotTestEntity::class);

    expect($snapshot->entityClass)->toBe(SnapshotTestEntity::class);
    expect($snapshot->table)->toBe('snapshot_test_table');
    expect($snapshot->columns)->toHaveKeys(['title', 'views']);

    $titleCol = $snapshot->columns['title'];
    expect($titleCol['type'])->toBe('StringColumn');
    expect($titleCol['meta']['maxLength'])->toBe(200);

    $viewsCol = $snapshot->columns['views'];
    expect($viewsCol['type'])->toBe('IntColumn');
    expect($viewsCol['meta']['unsigned'])->toBeTrue();
    expect($viewsCol['default'])->toBe(0);
});

it('supports round-trip via arrays', function () {
    $snapshot = SchemaSnapshot::fromEntity(SnapshotTestEntity::class);
    $array = $snapshot->toArray();

    $restored = SchemaSnapshot::fromArray($array);

    expect($restored->entityClass)->toBe($snapshot->entityClass);
    expect($restored->table)->toBe($snapshot->table);
    expect($restored->columns)->toBe($snapshot->columns);
});
