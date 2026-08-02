<?php

declare(strict_types=1);

use Glutamate\Columns\IdColumn;
use Illuminate\Support\Facades\Schema;

it('creates id column', function () {
    Schema::dropIfExists('test_ids');
    Schema::create('test_ids', function ($table) {
        IdColumn::make()->toBlueprint($table, 'id');
    });

    expect(Schema::hasTable('test_ids'))->toBeTrue();
    expect(Schema::hasColumn('test_ids', 'id'))->toBeTrue();
});

it('creates custom named id column', function () {
    Schema::dropIfExists('test_custom_ids');
    Schema::create('test_custom_ids', function ($table) {
        IdColumn::make()->toBlueprint($table, 'custom_id');
    });

    expect(Schema::hasTable('test_custom_ids'))->toBeTrue();
    expect(Schema::hasColumn('test_custom_ids', 'custom_id'))->toBeTrue();
});
