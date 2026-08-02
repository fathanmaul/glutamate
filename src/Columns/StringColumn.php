<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

class StringColumn extends Column
{
    protected ?int $maxLength = null;

    public function maxLength(?int $length): static
    {
        $this->maxLength = $length;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $column = $table->string($name, $this->maxLength);

        $this->applyModifiers($column);
    }

    public function phpType(): string
    {
        return $this->isNullable ? '?string' : 'string';
    }
}
