<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Stringable;

abstract class Column implements Stringable
{
    protected ?string $name = null;

    protected bool $nullable = false;

    protected bool $hasDefault = false;

    protected mixed $default = null;

    protected bool $unique = false;

    protected bool $index = false;

    final public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function columnName(string $name): static
    {
        return $this->as($name);
    }

    public function as(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function nullable(bool $value = true): static
    {
        $this->nullable = $value;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->hasDefault = true;
        $this->default = $value;

        return $this;
    }

    public function unique(bool $value = true): static
    {
        $this->unique = $value;

        return $this;
    }

    public function index(bool $value = true): static
    {
        $this->index = $value;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function hasDefault(): bool
    {
        return $this->hasDefault;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isIndex(): bool
    {
        return $this->index;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    protected function applyCommonModifiers(ColumnDefinition $col): void
    {
        if ($this->nullable) {
            $col->nullable();
        }

        if ($this->hasDefault) {
            $col->default($this->default);
        }

        if ($this->unique) {
            $col->unique();
        }

        if ($this->index) {
            $col->index();
        }
    }

    abstract public function toBlueprint(Blueprint $table, string $name): void;

    abstract public function phpType(): string;

    /**
     * @return array<string, mixed>
     */
    abstract public function toSnapshotArray(): array;
}
