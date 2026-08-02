<?php

declare(strict_types=1);

namespace Glutamate\Schema;

final class SchemaDiff
{
    /**
     * @param  array<string, array<string, mixed>>  $added  name => column snapshot array
     * @param  array<string, array{from: array<string, mixed>, to: array<string, mixed>}>  $changed  name => ['from' => array, 'to' => array]
     * @param  string[]  $removed  list of removed column names
     */
    public function __construct(
        public readonly array $added,
        public readonly array $changed,
        public readonly array $removed,
    ) {}

    public function isEmpty(): bool
    {
        return empty($this->added) && empty($this->changed) && empty($this->removed);
    }
}
