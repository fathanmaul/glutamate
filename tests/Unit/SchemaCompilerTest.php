<?php

declare(strict_types=1);

use Glutamate\Columns\IntColumn;
use Glutamate\Columns\StringColumn;
use Glutamate\Entity;
use Glutamate\SchemaCompiler;

class TestValidEntity extends Entity
{
    public static function email(): StringColumn
    {
        return StringColumn::make()->maxLength(191)->unique();
    }

    public static function age(): IntColumn
    {
        return IntColumn::make()->unsigned()->nullable();
    }

    public static function someHelper(): string
    {
        return 'not a column';
    }
}

class TestCustomNameEntity extends Entity
{
    public static function emailAddress(): StringColumn
    {
        return StringColumn::make()->as('custom_email');
    }
}

class TestDuplicateNameEntity extends Entity
{
    public static function email(): StringColumn
    {
        return StringColumn::make();
    }

    public static function emailAddress(): StringColumn
    {
        return StringColumn::make()->as('email');
    }
}

class TestNonEntity
{
    //
}

it('throws InvalidArgumentException when compiling non-entity classes', function () {
    expect(function () {
        SchemaCompiler::compile(TestNonEntity::class);
    })->toThrow(InvalidArgumentException::class, TestNonEntity::class.' must extend '.Entity::class);
});

it('compiles valid entities and returns resolved columns', function () {
    $columns = SchemaCompiler::compile(TestValidEntity::class);

    expect($columns)->toHaveCount(2);
    expect($columns)->toHaveKeys(['email', 'age']);
    expect($columns['email'])->toBeInstanceOf(StringColumn::class);
    expect($columns['age'])->toBeInstanceOf(IntColumn::class);
});

it('does not invoke static methods that do not return a Column subtype', function () {
    // TestValidEntity has public static function someHelper() returning a string.
    // It should be skipped, not thrown or crashed.
    $columns = SchemaCompiler::compile(TestValidEntity::class);
    expect($columns)->not->toHaveKey('some_helper');
});

it('does not invoke inherited static methods from Entity parent class', function () {
    $columns = SchemaCompiler::compile(TestValidEntity::class);

    // fromRaw, fromEloquent etc. should be skipped
    expect($columns)->not->toHaveKeys(['from_raw', 'from_eloquent', 'from_eloquent_collection']);
});

it('resolves custom names using as() modifier', function () {
    $columns = SchemaCompiler::compile(TestCustomNameEntity::class);

    expect($columns)->toHaveCount(1);
    expect($columns)->toHaveKey('custom_email');
    expect($columns['custom_email'])->toBeInstanceOf(StringColumn::class);
});

it('throws LogicException when compiling duplicate column names', function () {
    expect(function () {
        SchemaCompiler::compile(TestDuplicateNameEntity::class);
    })->toThrow(LogicException::class, "Duplicate column name 'email' resolved");
});
