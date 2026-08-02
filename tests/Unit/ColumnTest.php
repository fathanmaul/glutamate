<?php

declare(strict_types=1);

use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;

// Dummy class to simulate caller for backtrace
class DummyEntity
{
    public static function emailAddress(): StringColumn
    {
        return StringColumn::make();
    }

    public static function age(): IntColumn
    {
        return IntColumn::make();
    }

    public static function customEmail(): StringColumn
    {
        return StringColumn::make('explicit_name');
    }
}

it('auto-detects column name from immediate caller using backtrace', function () {
    $email = DummyEntity::emailAddress();
    $age = DummyEntity::age();

    expect($email->getName())->toBe('email_address');
    expect($age->getName())->toBe('age');
});

it('uses explicit name if passed to make()', function () {
    $custom = DummyEntity::customEmail();

    expect($custom->getName())->toBe('explicit_name');
});

it('allows overriding column name via columnName()', function () {
    $email = DummyEntity::emailAddress();
    $email->columnName('new_email');

    expect($email->getName())->toBe('new_email');
});

it('casts to string returning the resolved name', function () {
    $email = DummyEntity::emailAddress();

    expect((string) $email)->toBe('email_address');
});

it('supports common fluent modifiers and stores metadata', function () {
    $column = StringColumn::make('test')
        ->nullable()
        ->default('foo')
        ->unique()
        ->index();

    expect($column->isNullable())->toBeTrue();
    expect($column->getDefault())->toBe('foo');
    expect($column->isUnique())->toBeTrue();
    expect($column->isIndex())->toBeTrue();
});

it('supports string specific modifiers', function () {
    $column = StringColumn::make('test')->maxLength(100);

    expect($column->getMaxLength())->toBe(100);
});

it('supports integer specific modifiers', function () {
    $column = IntColumn::make('test')
        ->unsigned()
        ->autoIncrement();

    expect($column->isUnsigned())->toBeTrue();
    expect($column->isAutoIncrement())->toBeTrue();
});

it('returns correct php type representation', function () {
    $string = StringColumn::make('test');
    expect($string->phpType())->toBe('string');

    $nullableString = StringColumn::make('test')->nullable();
    expect($nullableString->phpType())->toBe('?string');

    $int = IntColumn::make('test');
    expect($int->phpType())->toBe('int');

    $nullableInt = IntColumn::make('test')->nullable();
    expect($nullableInt->phpType())->toBe('?int');
});

it('correctly maps to Laravel Blueprint', function () {
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getSchemaGrammar')->andReturn(new MySqlGrammar($connection));
    $table = new Blueprint($connection, 'users');

    $stringColumn = StringColumn::make('email')
        ->maxLength(150)
        ->nullable()
        ->default('test@example.com')
        ->unique();

    $stringColumn->toBlueprint($table, 'email');

    $columns = $table->getColumns();
    expect($columns)->toHaveCount(1);

    $col = $columns[0];
    expect($col->name)->toBe('email');
    expect($col->type)->toBe('string');
    expect($col->length)->toBe(150);
    expect($col->nullable)->toBeTrue();
    expect($col->default)->toBe('test@example.com');
    expect($col->unique)->toBeTrue();
});
