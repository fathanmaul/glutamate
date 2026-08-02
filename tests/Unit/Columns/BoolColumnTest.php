<?php

declare(strict_types=1);

use Glutamate\Columns\BoolColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates boolean column', function () {
    Schema::create('test_bools', function ($table) {
        BoolColumn::make('is_active')->toBlueprint($table, 'is_active');
    });

    expect(Schema::hasColumn('test_bools', 'is_active'))->toBeTrue();
    expect(Schema::getColumnType('test_bools', 'is_active'))->toBe('tinyint');
});

it('sets default value in schema', function () {
    Schema::create('test_bools_default', function ($table) {
        $table->id();
        BoolColumn::make('is_active')->default(true)->toBlueprint($table, 'is_active');
    });

    DB::table('test_bools_default')->insert(['id' => 1]);
    $row = DB::table('test_bools_default')->first();
    expect((bool) $row->is_active)->toBeTrue();
});

it('returns correct phpType', function () {
    $bool = BoolColumn::make('test');
    expect($bool->phpType())->toBe('bool');

    $nullableBool = BoolColumn::make('test')->nullable();
    expect($nullableBool->phpType())->toBe('?bool');
});
