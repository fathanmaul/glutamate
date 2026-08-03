<?php

declare(strict_types=1);

use Glutamate\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('creates text column', function () {
    Schema::create('test_texts', function ($table) {
        TextColumn::make('content')->toBlueprint($table, 'content');
    });

    expect(Schema::hasColumn('test_texts', 'content'))->toBeTrue();
    expect(Schema::getColumnType('test_texts', 'content'))->toBe('text');
});

it('sets default value in schema', function () {
    Schema::create('test_texts_default', function ($table) {
        $table->id();
        TextColumn::make('body')->default('hello world')->toBlueprint($table, 'body');
    });

    DB::table('test_texts_default')->insert(['id' => 1]);
    $row = DB::table('test_texts_default')->first();
    expect($row->body)->toBe('hello world');
});

it('returns correct phpType', function () {
    $text = TextColumn::make('content');
    expect($text->phpType())->toBe('string');

    $nullableText = TextColumn::make('content')->nullable();
    expect($nullableText->phpType())->toBe('?string');
});

it('snapshot array structure is correct', function () {
    $text = TextColumn::make('content');
    expect($text->toSnapshotArray())->toBe([
        'type' => 'TextColumn',
        'nullable' => false,
        'hasDefault' => false,
        'default' => null,
        'unique' => false,
        'index' => false,
        'meta' => [],
    ]);
});
