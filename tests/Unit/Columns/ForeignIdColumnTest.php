<?php

declare(strict_types=1);

use Glutamate\Columns\ForeignIdColumn;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates foreign key and enforces onDelete cascade', function () {
    DB::statement('PRAGMA foreign_keys = ON;');

    Schema::dropIfExists('posts');
    Schema::dropIfExists('users');

    Schema::create('users', function ($table) {
        $table->id();
    });

    Schema::create('posts', function ($table) {
        $table->id();
        ForeignIdColumn::make('user_id')->constrained('users')->onDelete('cascade')->toBlueprint($table, 'user_id');
    });

    expect(Schema::hasColumn('posts', 'user_id'))->toBeTrue();

    DB::table('users')->insert(['id' => 1]);
    DB::table('posts')->insert(['id' => 10, 'user_id' => 1]);

    expect(DB::table('posts')->count())->toBe(1);

    DB::table('users')->where('id', 1)->delete();

    expect(DB::table('posts')->count())->toBe(0);
});

it('infers table name from column name when constrained is called without argument', function () {
    DB::statement('PRAGMA foreign_keys = ON;');

    Schema::dropIfExists('comments');
    Schema::dropIfExists('users');

    Schema::create('users', function ($table) {
        $table->id();
    });

    Schema::create('comments', function ($table) {
        $table->id();
        ForeignIdColumn::make('user_id')->constrained()->onDelete('restrict')->toBlueprint($table, 'user_id');
    });

    expect(Schema::hasColumn('comments', 'user_id'))->toBeTrue();

    expect(function () {
        DB::table('comments')->insert(['id' => 1, 'user_id' => 999]);
    })->toThrow(QueryException::class);
});

it('returns correct phpType', function () {
    $foreignId = ForeignIdColumn::make('user_id');
    expect($foreignId->phpType())->toBe('int');

    $nullableForeignId = ForeignIdColumn::make('user_id')->nullable();
    expect($nullableForeignId->phpType())->toBe('?int');
});
