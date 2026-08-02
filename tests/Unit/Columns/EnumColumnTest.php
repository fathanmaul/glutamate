<?php

declare(strict_types=1);

use Glutamate\Columns\EnumColumn;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('throws InvalidArgumentException when creating with empty values', function () {
    expect(function () {
        EnumColumn::make([]);
    })->toThrow(InvalidArgumentException::class, 'EnumColumn requires at least one value.');
});

it('creates enum column with values', function () {
    Schema::create('test_enums', function ($table) {
        $table->id();
        EnumColumn::make(['pending', 'completed'])->toBlueprint($table, 'status');
    });

    expect(Schema::hasColumn('test_enums', 'status'))->toBeTrue();

    DB::table('test_enums')->insert(['status' => 'pending']);
    $row = DB::table('test_enums')->first();
    expect($row->status)->toBe('pending');
});

it('fails when inserting value outside of enum list', function () {
    Schema::create('test_enums_constrained', function ($table) {
        $table->id();
        EnumColumn::make(['a', 'b'])->toBlueprint($table, 'value');
    });

    expect(function () {
        DB::table('test_enums_constrained')->insert(['value' => 'c']);
    })->toThrow(QueryException::class);
});

it('returns correct phpType', function () {
    $enum = EnumColumn::make(['a', 'b']);
    expect($enum->phpType())->toBe('string');

    $nullableEnum = EnumColumn::make(['a', 'b'])->nullable();
    expect($nullableEnum->phpType())->toBe('?string');
});
