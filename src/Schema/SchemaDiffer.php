<?php

declare(strict_types=1);

namespace Glutamate\Schema;

final class SchemaDiffer
{
    public static function diff(?SchemaSnapshot $previous, SchemaSnapshot $current): SchemaDiff
    {
        $prevColumns = $previous !== null ? $previous->columns : [];
        $currColumns = $current->columns;

        $added = array_diff_key($currColumns, $prevColumns);
        $removed = array_keys(array_diff_key($prevColumns, $currColumns));

        $changed = [];
        foreach (array_intersect_key($currColumns, $prevColumns) as $name => $col) {
            if ($col !== $prevColumns[$name]) {
                $changed[$name] = ['from' => $prevColumns[$name], 'to' => $col];
            }
        }

        return new SchemaDiff($added, $changed, $removed);
    }
}
