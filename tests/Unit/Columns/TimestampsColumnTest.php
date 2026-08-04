<?php

declare(strict_types=1);

use Glutamate\Columns\DateTimeColumn;
use Glutamate\Columns\TimestampsColumn;
use Glutamate\SchemaCompiler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TestTimestampsModel extends Model
{
    public static function timestamps(): TimestampsColumn
    {
        return TimestampsColumn::make()->withDeletedAt();
    }
}

it('creates timestamps columns', function () {
    $timestamps = TimestampsColumn::make();
    $cols = $timestamps->getColumns();

    expect($cols)->toHaveKeys(['created_at', 'updated_at']);
    expect($cols['created_at']->getName())->toBe('created_at');
    expect($cols['updated_at']->getName())->toBe('updated_at');

    Schema::dropIfExists('test_timestamps');
    Schema::create('test_timestamps', function ($table) use ($cols) {
        foreach ($cols as $name => $col) {
            $col->toBlueprint($table, $name);
        }
    });

    expect(Schema::hasTable('test_timestamps'))->toBeTrue();
    expect(Schema::hasColumn('test_timestamps', 'created_at'))->toBeTrue();
    expect(Schema::hasColumn('test_timestamps', 'updated_at'))->toBeTrue();
});

it('includes deleted_at column when withDeletedAt is called', function () {
    $timestamps = TimestampsColumn::make();
    $result = $timestamps->withDeletedAt();

    expect($result)->toBe($timestamps);

    $cols = $timestamps->getColumns();

    expect($cols)->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    expect($cols['created_at']->getName())->toBe('created_at');
    expect($cols['updated_at']->getName())->toBe('updated_at');
    expect($cols['deleted_at'])->toBeInstanceOf(DateTimeColumn::class);
    expect($cols['deleted_at']->getName())->toBe('deleted_at');
    expect($cols['deleted_at']->isNullable())->toBeTrue();

    Schema::dropIfExists('test_timestamps_deleted');
    Schema::create('test_timestamps_deleted', function ($table) use ($cols) {
        foreach ($cols as $name => $col) {
            $col->toBlueprint($table, $name);
        }
    });

    expect(Schema::hasTable('test_timestamps_deleted'))->toBeTrue();
    expect(Schema::hasColumn('test_timestamps_deleted', 'created_at'))->toBeTrue();
    expect(Schema::hasColumn('test_timestamps_deleted', 'updated_at'))->toBeTrue();
    expect(Schema::hasColumn('test_timestamps_deleted', 'deleted_at'))->toBeTrue();
});

it('compiles timestamps with deleted_at from model schema', function () {
    $columns = SchemaCompiler::compile(TestTimestampsModel::class);

    expect($columns)->toHaveKeys(['created_at', 'updated_at', 'deleted_at']);
    expect($columns['deleted_at'])->toBeInstanceOf(DateTimeColumn::class);
    expect($columns['deleted_at']->isNullable())->toBeTrue();
});
