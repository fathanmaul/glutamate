<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

final class BoolColumn extends Column
{
    public static function make(): static
    {
        return new self;
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
}
