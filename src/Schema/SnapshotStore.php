<?php

declare(strict_types=1);

namespace Glutamate\Schema;

final class SnapshotStore
{
    public function __construct(private readonly string $basePath) {}

    public function path(string $entityClass): string
    {
        $slug = str_replace('\\', '.', $entityClass);

        return rtrim($this->basePath, '/')."/{$slug}.json";
    }

    public function load(string $entityClass): ?SchemaSnapshot
    {
        $file = $this->path($entityClass);

        if (! file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);

        if ($content === false) {
            return null;
        }

        return SchemaSnapshot::fromArray(json_decode($content, true, 512, JSON_THROW_ON_ERROR));
    }

    public function save(SchemaSnapshot $snapshot): void
    {
        $file = $this->path($snapshot->entityClass);

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        file_put_contents($file, json_encode($snapshot->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }
}
