<?php

declare(strict_types=1);

namespace Glutamate\Schema;

use Glutamate\SchemaCompiler;
use Illuminate\Database\Eloquent\Model;

final class SchemaSnapshot
{
    /**
     * @param  array<string, array<string, mixed>>  $columns
     */
    public function __construct(
        public readonly string $modelClass,
        public readonly string $table,
        public readonly array $columns,
    ) {}

    /**
     * @param  class-string  $modelClass
     */
    public static function fromModel(string $modelClass): self
    {
        $columns = [];
        foreach (SchemaCompiler::compile($modelClass) as $name => $column) {
            $columns[$name] = $column->toSnapshotArray();
        }

        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();

        return new self($modelClass, $table, $columns);
    }

    /**
     * @param  array{modelClass: string, table: string, columns: array<string, array<string, mixed>>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['modelClass'], $data['table'], $data['columns']);
    }

    /**
     * @return array{modelClass: string, table: string, columns: array<string, array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'modelClass' => $this->modelClass,
            'table' => $this->table,
            'columns' => $this->columns,
        ];
    }
}
