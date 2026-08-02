<?php

declare(strict_types=1);

namespace Glutamate\Columns;

final class TimestampsColumn extends ColumnGroup
{
    public static function make(): static
    {
        return new self;
    }

    public function getColumns(): array
    {
        return [
            'created_at' => DateTimeColumn::make('created_at')->nullable(),
            'updated_at' => DateTimeColumn::make('updated_at')->nullable(),
        ];
    }
}
