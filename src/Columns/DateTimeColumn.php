<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

final class DateTimeColumn extends Column
{
    public static function make(): static
    {
        return new self;
    }

    protected bool $useCurrent = false;

    protected bool $useCurrentOnUpdate = false;

    public function useCurrent(bool $value = true): static
    {
        $this->useCurrent = $value;

        return $this;
    }

    public function useCurrentOnUpdate(bool $value = true): static
    {
        $this->useCurrentOnUpdate = $value;

        return $this;
    }

    public function getUseCurrent(): bool
    {
        return $this->useCurrent;
    }

    public function getUseCurrentOnUpdate(): bool
    {
        return $this->useCurrentOnUpdate;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $col = $table->dateTime($name);

        if ($this->useCurrent) {
            $col->useCurrent();
        }

        if ($this->useCurrentOnUpdate) {
            $col->useCurrentOnUpdate();
        }
        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?\Carbon\Carbon' : '\Carbon\Carbon';
    }
}
