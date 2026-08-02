<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

/**
 * @extends Column<int>
 */
final class IdColumn extends Column
{
    public static function make(?string $name = null): static
    {
        return new self($name);
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        if ($name === 'id') {
            $table->id();
        } else {
            $table->id($name);
        }
    }

    public function phpType(): string
    {
        return 'int';
    }

    public function toSnapshotArray(): array
    {
        return [
            'type' => 'IdColumn',
            'nullable' => false,
            'hasDefault' => false,
            'default' => null,
            'unique' => false,
            'index' => false,
            'meta' => [],
        ];
    }
}
