<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

final class IntColumn extends Column
{
    public static function make(): static
    {
        return new self;
    }

    public const string SIZE_TINY = 'tiny';

    public const string SIZE_SMALL = 'small';

    public const string SIZE_MEDIUM = 'medium';

    public const string SIZE_DEFAULT = 'default';

    public const string SIZE_BIG = 'big';

    protected string $size = self::SIZE_DEFAULT;

    protected bool $unsigned = false;

    protected bool $autoIncrement = false;

    public function unsigned(bool $value = true): static
    {
        $this->unsigned = $value;

        return $this;
    }

    public function autoIncrement(bool $value = true): static
    {
        $this->autoIncrement = $value;

        return $this;
    }

    public function tiny(): static
    {
        $this->size = self::SIZE_TINY;

        return $this;
    }

    public function small(): static
    {
        $this->size = self::SIZE_SMALL;

        return $this;
    }

    public function medium(): static
    {
        $this->size = self::SIZE_MEDIUM;

        return $this;
    }

    public function big(): static
    {
        $this->size = self::SIZE_BIG;

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $method = match ($this->size) {
            self::SIZE_TINY => 'tinyInteger',
            self::SIZE_SMALL => 'smallInteger',
            self::SIZE_MEDIUM => 'mediumInteger',
            self::SIZE_BIG => 'bigInteger',
            default => 'integer',
        };

        $col = $table->{$method}($name, $this->autoIncrement, $this->unsigned);
        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?int' : 'int';
    }
}
