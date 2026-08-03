<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

/**
 * @extends Column<string>
 */
final class TextColumn extends Column
{
    public static function make(?string $name = null): static
    {
        return new self($name);
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $col = $table->text($name);
        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?string' : 'string';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSnapshotArray(): array
    {
        return [
            'type' => 'TextColumn',
            'nullable' => $this->nullable,
            'hasDefault' => $this->hasDefault,
            'default' => $this->default,
            'unique' => $this->unique,
            'index' => $this->index,
            'meta' => [],
        ];
    }
}
