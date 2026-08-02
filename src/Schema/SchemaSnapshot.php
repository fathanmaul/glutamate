<?php

declare(strict_types=1);

namespace Glutamate\Schema;

use Glutamate\SchemaCompiler;

final class SchemaSnapshot
{
    /**
     * @param  array<string, array<string, mixed>>  $columns
     */
    public function __construct(
        public readonly string $entityClass,
        public readonly string $table,
        public readonly array $columns,
    ) {}

    public static function fromEntity(string $entityClass): self
    {
        $columns = [];
        foreach (SchemaCompiler::compile($entityClass) as $name => $column) {
            $columns[$name] = $column->toSnapshotArray();
        }

        return new self($entityClass, $entityClass::table(), $columns);
    }

    /**
     * @param  array{entityClass: string, table: string, columns: array<string, array<string, mixed>>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['entityClass'], $data['table'], $data['columns']);
    }

    /**
     * @return array{entityClass: string, table: string, columns: array<string, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'entityClass' => $this->entityClass,
            'table' => $this->table,
            'columns' => $this->columns,
        ];
    }
}
