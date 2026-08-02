<?php

declare(strict_types=1);

use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('applies common modifiers: nullable, default, unique, index', function () {
    Schema::create('test_shared_modifiers', function ($table) {
        $table->id();
        IntColumn::make('value')
            ->nullable()
            ->default(100)
            ->unique()
            ->index()
            ->toBlueprint($table, 'value');
    });

    expect(Schema::hasColumn('test_shared_modifiers', 'value'))->toBeTrue();

    $columns = Schema::getColumns('test_shared_modifiers');
    $valCol = collect($columns)->firstWhere('name', 'value');
    expect($valCol['nullable'])->toBeTrue();

    DB::table('test_shared_modifiers')->insert(['id' => 1]);
    $row = DB::table('test_shared_modifiers')->first();
    expect((int) $row->value)->toBe(100);
});

it('string column preserves all behavior after refactoring to applyCommonModifiers', function () {
    Schema::create('test_string_regression', function ($table) {
        $table->id();
        StringColumn::make('email')
            ->maxLength(100)
            ->nullable()
            ->default('test@example.com')
            ->unique()
            ->toBlueprint($table, 'email');
    });

    expect(Schema::hasColumn('test_string_regression', 'email'))->toBeTrue();

    $columns = Schema::getColumns('test_string_regression');
    $emailCol = collect($columns)->firstWhere('name', 'email');
    expect($emailCol['nullable'])->toBeTrue();
    expect($emailCol['type'])->toContain('varchar');

    $result = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='test_string_regression'");
    expect($result[0]->sql)->toContain('"email" varchar');
});
