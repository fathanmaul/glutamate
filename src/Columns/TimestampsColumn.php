<?php

declare(strict_types=1);

namespace Glutamate\Columns;

final class TimestampsColumn extends ColumnGroup
{
    /** @var array<string, Column<mixed>> */
    protected array $columns = [];

    public static function make(): static
    {
        return new self;
    }

    public function getColumns(): array
    {
        return array_merge($this->columns, [
            'created_at' => DateTimeColumn::make('created_at')->nullable(),
            'updated_at' => DateTimeColumn::make('updated_at')->nullable(),
        ]);
    }

    public function withDeletedAt(): static
    {
        $this->columns['deleted_at'] = DateTimeColumn::make('deleted_at')->nullable();

        return $this;
    }
}
