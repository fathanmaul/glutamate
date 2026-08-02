<?php

declare(strict_types=1);

use Glutamate\Columns\DecimalColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates decimal column with precision and scale', function () {
    Schema::create('test_decimals', function ($table) {
        DecimalColumn::make('value')->precision(10, 4)->toBlueprint($table, 'value');
    });

    expect(Schema::hasColumn('test_decimals', 'value'))->toBeTrue();

    $result = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='test_decimals'");
    $sql = $result[0]->sql;
    expect($sql)->toContain('"value" numeric');
});

it('creates unsigned decimal column', function () {
    Schema::create('test_decimals_unsigned', function ($table) {
        DecimalColumn::make('value')->precision(8, 2)->unsigned()->toBlueprint($table, 'value');
    });

    $result = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='test_decimals_unsigned'");
    $sql = $result[0]->sql;
    expect($sql)->toContain('"value" numeric');
});

it('returns correct phpType', function () {
    $decimal = DecimalColumn::make('test');
    expect($decimal->phpType())->toBe('string');

    $nullableDecimal = DecimalColumn::make('test')->nullable();
    expect($nullableDecimal->phpType())->toBe('?string');
});
