<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

/**
 * @extends Column<bool>
 */
final class BoolColumn extends Column
{
    public static function make(?string $name = null): static
    {
        return new self($name);
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $col = $table->boolean($name);
        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?bool' : 'bool';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSnapshotArray(): array
    {
        return [
            'type' => 'BoolColumn',
            'nullable' => $this->nullable,
            'hasDefault' => $this->hasDefault,
            'default' => $this->default,
            'unique' => $this->unique,
            'index' => $this->index,
            'meta' => [],
        ];
    }
}
