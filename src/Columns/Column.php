<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Str;
use Stringable;

abstract class Column implements Stringable
{
    protected ?string $name = null;

    protected bool $isNullable = false;

    protected mixed $defaultValue = null;

    protected bool $isUnique = false;

    protected bool $isIndex = false;

    final public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public static function make(?string $name = null): static
    {
        if ($name === null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $caller = $trace[1] ?? null;

            if ($caller !== null) {
                $name = Str::snake($caller['function']);
            }
        }

        return new static($name);
    }

    public function columnName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function nullable(bool $value = true): static
    {
        $this->isNullable = $value;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function unique(bool $value = true): static
    {
        $this->isUnique = $value;

        return $this;
    }

    public function index(bool $value = true): static
    {
        $this->isIndex = $value;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function getDefault(): mixed
    {
        return $this->defaultValue;
    }

    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    public function isIndex(): bool
    {
        return $this->isIndex;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    protected function applyModifiers(ColumnDefinition $column): void
    {
        if ($this->isNullable) {
            $column->nullable();
        }

        if ($this->defaultValue !== null) {
            $column->default($this->defaultValue);
        }

        if ($this->isUnique) {
            $column->unique();
        }

        if ($this->isIndex) {
            $column->index();
        }
    }

    abstract public function toBlueprint(Blueprint $table, string $name): void;

    abstract public function phpType(): string;
}
