<?php

declare(strict_types=1);

namespace Glutamate\Columns;

use Illuminate\Database\Schema\Blueprint;

/**
 * @extends Column<int>
 */
final class ForeignIdColumn extends Column
{
    public static function make(?string $name = null): static
    {
        return new self($name);
    }

    protected bool $isConstrained = false;

    protected ?string $referencesTable = null;

    protected string $onDeleteAction = 'restrict';

    protected string $onUpdateAction = 'cascade';

    public function constrained(?string $table = null): static
    {
        $this->isConstrained = true;
        $this->referencesTable = $table;

        return $this;
    }

    public function onDelete(string $action): static
    {
        $this->onDeleteAction = $action;

        return $this;
    }

    public function onUpdate(string $action): static
    {
        $this->onUpdateAction = $action;

        return $this;
    }

    public function isConstrained(): bool
    {
        return $this->isConstrained;
    }

    public function getReferencesTable(): ?string
    {
        return $this->referencesTable;
    }

    public function getOnDelete(): string
    {
        return $this->onDeleteAction;
    }

    public function getOnUpdate(): string
    {
        return $this->onUpdateAction;
    }

    public function toBlueprint(Blueprint $table, string $name): void
    {
        $col = $table->foreignId($name);

        if ($this->isConstrained) {
            $fk = $this->referencesTable !== null
                ? $col->constrained($this->referencesTable)
                : $col->constrained();

            $fk->onDelete($this->onDeleteAction)
                ->onUpdate($this->onUpdateAction);
        }

        $this->applyCommonModifiers($col);
    }

    public function phpType(): string
    {
        return $this->nullable ? '?int' : 'int';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSnapshotArray(): array
    {
        return [
            'type' => 'ForeignIdColumn',
            'nullable' => $this->nullable,
            'hasDefault' => $this->hasDefault,
            'default' => $this->default,
            'unique' => $this->unique,
            'index' => $this->index,
            'meta' => [
                'isConstrained' => $this->isConstrained,
                'referencesTable' => $this->referencesTable,
                'onDelete' => $this->onDeleteAction,
                'onUpdate' => $this->onUpdateAction,
            ],
        ];
    }
}
