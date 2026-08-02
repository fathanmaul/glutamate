<?php

declare(strict_types=1);

namespace Glutamate;

use Glutamate\Columns\Column;

interface SchemaElement
{
    /**
     * Get the columns associated with this schema element.
     *
     * @return array<string, Column<mixed>>
     */
    public function getColumns(): array;
}
