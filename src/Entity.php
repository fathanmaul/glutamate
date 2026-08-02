<?php

declare(strict_types=1);

namespace Glutamate;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

abstract class Entity
{
    /**
     * Create a new entity instance.
     */
    final public function __construct()
    {
        //
    }

    /**
     * Hydrate the entity from a raw array of attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromRaw(array $attributes): static
    {
        $entity = new static;
        $ref = new ReflectionClass($entity);

        foreach ($attributes as $key => $value) {
            $property = static::resolveProperty($ref, $key);

            if ($property === null) {
                continue;
            }

            $property->setAccessible(true);
            $property->setValue($entity, static::castValue($property->getType(), $value));
        }

        return $entity;
    }

    /**
     * Hydrate the entity from an Eloquent model.
     */
    public static function fromEloquent(Model $model): static
    {
        return static::fromRaw($model->attributesToArray());
    }

    /**
     * Hydrate a collection of entities from a collection of Eloquent models.
     *
     * @param  iterable<int, Model>  $collection
     * @return Collection<int, static>
     */
    public static function fromEloquentCollection(iterable $collection): Collection
    {
        return Collection::make($collection)
            ->map(fn (Model $model) => static::fromEloquent($model));
    }

    /**
     * Resolve a ReflectionProperty for the given key, supporting camelCase and snake_case mapping.
     *
     * @param  ReflectionClass<static>  $ref
     */
    protected static function resolveProperty(ReflectionClass $ref, string $key): ?ReflectionProperty
    {
        $exact = $key;
        $camel = Str::camel($key);
        $snake = Str::snake($key);

        $candidates = array_unique([$exact, $camel, $snake]);
        $matched = [];

        foreach ($candidates as $name) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);

                if (! $prop->isStatic()) {
                    $matched[$name] = $prop;
                }
            }
        }

        if (count($matched) > 1) {
            if (function_exists('app') && app()->environment('local', 'testing')) {
                throw new LogicException(
                    "Ambiguous property match for key '{$key}' on class {$ref->getName()}: matches [".implode(', ', array_keys($matched)).']',
                );
            }

            return null;
        }

        return count($matched) > 0 ? reset($matched) : null;
    }

    /**
     * Cast the raw value based on the property type declaration.
     */
    protected static function castValue(?ReflectionType $type, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($type === null) {
            return $value;
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();

            if ($typeName === DateTimeInterface::class || (class_exists($typeName) && is_subclass_of($typeName, DateTimeInterface::class))) {
                return Carbon::parse($value);
            }

            switch ($typeName) {
                case 'int':
                case 'integer':
                    return (int) $value;
                case 'float':
                case 'double':
                    return (float) $value;
                case 'bool':
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'string':
                    return (string) $value;
                case 'array':
                    return (array) $value;
            }
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && $subType->getName() !== 'null') {
                    return static::castValue($subType, $value);
                }
            }
        }

        return $value;
    }
}
