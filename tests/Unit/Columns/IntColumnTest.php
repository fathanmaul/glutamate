<?php

declare(strict_types=1);

use Glutamate\Columns\IntColumn;
use Illuminate\Support\Facades\Schema;

it('creates standard integer column', function () {
    Schema::create('test_ints', function ($table) {
        IntColumn::make('value')->toBlueprint($table, 'value');
    });

    expect(Schema::hasColumn('test_ints', 'value'))->toBeTrue();
    expect(Schema::getColumnType('test_ints', 'value'))->toBe('integer');
});

it('creates unsigned and auto-incrementing integer', function () {
    Schema::create('test_auto_ints', function ($table) {
        IntColumn::make('id')->unsigned()->autoIncrement()->toBlueprint($table, 'id');
    });

    expect(Schema::hasColumn('test_auto_ints', 'id'))->toBeTrue();
    expect(Schema::getColumnType('test_auto_ints', 'id'))->toBe('integer');
});

it('supports tiny, small, medium, and big integer sizes', function () {
    Schema::create('test_int_sizes', function ($table) {
        IntColumn::make('tiny_val')->tiny()->toBlueprint($table, 'tiny_val');
        IntColumn::make('small_val')->small()->toBlueprint($table, 'small_val');
        IntColumn::make('medium_val')->medium()->toBlueprint($table, 'medium_val');
        IntColumn::make('big_val')->big()->toBlueprint($table, 'big_val');
    });

    expect(Schema::getColumnType('test_int_sizes', 'tiny_val'))->toBe('integer');
    expect(Schema::getColumnType('test_int_sizes', 'small_val'))->toBe('integer');
    expect(Schema::getColumnType('test_int_sizes', 'medium_val'))->toBe('integer');
    expect(Schema::getColumnType('test_int_sizes', 'big_val'))->toBe('integer');
});

it('returns correct phpType', function () {
    $int = IntColumn::make('test');
    expect($int->phpType())->toBe('int');

    $nullableInt = IntColumn::make('test')->nullable();
    expect($nullableInt->phpType())->toBe('?int');
});
