<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

class IntColumn extends Column
{
    protected bool $isUnsigned = false;

    protected bool $isAutoIncrement = false;

    public function unsigned(bool $value = true): static
    {
        $this->isUnsigned = $value;

        return $this;
    }

    public function autoIncrement(bool $value = true): static
    {
        $this->isAutoIncrement = $value;

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->isUnsigned;
    }

    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $column = $table->integer($name, $this->isAutoIncrement, $this->isUnsigned);

        $this->applyModifiers($column);
    }

    public function phpType(): string
    {
        return $this->isNullable ? '?int' : 'int';
    }
}
