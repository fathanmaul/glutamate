<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

final class DecimalColumn extends Column
{
    public static function make(): static
    {
        return new self;
    }

    protected int $precision = 8;

    protected int $scale = 2;

    protected bool $unsigned = false;

    public function precision(int $precision, int $scale): static
    {
        $this->precision = $precision;
        $this->scale = $scale;

        return $this;
    }

    public function unsigned(bool $value = true): static
    {
        $this->unsigned = $value;

        return $this;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getScale(): int
    {
        return $this->scale;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $col = $table->decimal($name, $this->precision, $this->scale);

        if ($this->unsigned) {
            $col->unsigned();
        }
        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?string' : 'string';
    }
}
