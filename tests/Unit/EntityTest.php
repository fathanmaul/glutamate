<?php

declare(strict_types=1);

use Glutamate\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

// Dummy classes for testing
class TestUserEntity extends Entity
{
    public string $name;

    public ?int $age = null;

    public bool $isActive;

    public ?Carbon $birthday = null;
}

class TestAmbiguousEntity extends Entity
{
    public string $emailAddress;

    public string $email_address;
}

class EloquentDummyUser extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'birthday' => 'datetime',
    ];
}

it('does not extend Eloquent Model', function () {
    $ref = new ReflectionClass(Entity::class);

    expect(is_subclass_of(Entity::class, Model::class))->toBeFalse();
    expect($ref->isSubclassOf(Model::class))->toBeFalse();
});

it('hydrates native typed properties from raw array, supporting snake_case and camelCase', function () {
    $entity = TestUserEntity::fromRaw([
        'name' => 'Lutfi',
        'age' => 25,
        'is_active' => '1', // String '1' should cast to bool true
    ]);

    expect($entity->name)->toBe('Lutfi');
    expect($entity->age)->toBe(25);
    expect($entity->isActive)->toBeTrue();
});

it('ignores keys that do not match any property', function () {
    $entity = TestUserEntity::fromRaw([
        'name' => 'Lutfi',
        'unregistered_key' => 'some_value',
    ]);

    expect($entity->name)->toBe('Lutfi');
    // Ensure no dynamic properties are created (or we can assert it has no property 'unregistered_key')
    expect(property_exists($entity, 'unregistered_key'))->toBeFalse();
});

it('hydrates from Eloquent model using attributesToArray to get cast values', function () {
    $model = new EloquentDummyUser;
    $model->forceFill([
        'name' => 'Lutfi',
        'age' => 25,
        'is_active' => '1',
        'birthday' => '2026-08-01 12:00:00',
    ]);

    $entity = TestUserEntity::fromEloquent($model);

    expect($entity->name)->toBe('Lutfi');
    expect($entity->isActive)->toBeTrue(); // '1' is cast to boolean true
});

it('hydrates a collection of entities from a collection of Eloquent models', function () {
    $model1 = new EloquentDummyUser;
    $model1->forceFill(['name' => 'User 1', 'age' => 20, 'is_active' => true]);

    $model2 = new EloquentDummyUser;
    $model2->forceFill(['name' => 'User 2', 'age' => 30, 'is_active' => false]);

    $collection = TestUserEntity::fromEloquentCollection([$model1, $model2]);

    expect($collection)->toHaveCount(2);
    expect($collection->first())->toBeInstanceOf(TestUserEntity::class);
    expect($collection->first()->name)->toBe('User 1');
    expect($collection->last()->name)->toBe('User 2');
});

it('throws LogicException on ambiguous property matches in testing environment', function () {
    expect(function () {
        TestAmbiguousEntity::fromRaw([
            'email_address' => 'test@example.com',
        ]);
    })->toThrow(LogicException::class, "Ambiguous property match for key 'email_address'");
});
