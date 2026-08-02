<?php

declare(strict_types=1);

use Glutamate\Columns\TimestampsColumn;
use Illuminate\Support\Facades\Schema;

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
