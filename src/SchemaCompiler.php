<?php

declare(strict_types=1);

namespace Glutamate;

use Glutamate\Columns\Column;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class SchemaCompiler
{
    /**
     * Compile and return the column schema for the given entity class.
     *
     * @return array<string, Column>
     */
    public static function compile(string $entityClass): array
    {
        if (! is_subclass_of($entityClass, Entity::class)) {
            throw new InvalidArgumentException("{$entityClass} must extend ".Entity::class);
        }

        $ref = new ReflectionClass($entityClass);
        $columns = [];

        foreach ($ref->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip inherited static methods (from Entity parent class)
            if ($method->class !== $entityClass) {
                continue;
            }

            // Check return type before invoking
            $returnType = $method->getReturnType();

            if (! $returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
                continue;
            }

            $returnClassName = $returnType->getName();

            if ($returnClassName !== Column::class && ! is_subclass_of($returnClassName, Column::class)) {
                continue;
            }

            /** @var Column $column */
            $column = $method->invoke(null);

            $name = $column->getName() ?? self::snake($method->getName());

            if ($column->getName() === null) {
                $column->as($name);
            }

            if (isset($columns[$name])) {
                throw new LogicException(
                    "Duplicate column name '{$name}' resolved from {$entityClass}::{$method->getName()}() — "
                    .'already defined by another method. Use ->as() to disambiguate.',
                );
            }

            $columns[$name] = $column;
        }

        return $columns;
    }

    private static function snake(string $value): string
    {
        return Str::snake($value);
    }
}
