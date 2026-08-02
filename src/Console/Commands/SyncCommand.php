<?php

declare(strict_types=1);

namespace Glutamate\Console\Commands;

use Glutamate\Entity;
use Glutamate\Schema\MigrationGenerator;
use Glutamate\Schema\SchemaDiffer;
use Glutamate\Schema\SchemaSnapshot;
use Glutamate\Schema\SnapshotStore;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

final class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'glutamate:sync {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Detect Entity schema changes and generate migration files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $entitiesPath = config('glutamate.entities_path', app_path('Entities'));
        $entitiesNamespace = config('glutamate.entities_namespace', 'App\\Entities');
        $snapshotPath = config('glutamate.snapshot_path', storage_path('framework/glutamate/snapshots'));

        $store = new SnapshotStore($snapshotPath);
        $entities = self::discoverEntities($entitiesPath, $entitiesNamespace);

        if (empty($entities)) {
            $this->info('No entities found.');

            return self::SUCCESS;
        }

        $anyChange = false;

        foreach ($entities as $entityClass) {
            $current = SchemaSnapshot::fromEntity($entityClass);
            $previous = $store->load($entityClass);
            $diff = SchemaDiffer::diff($previous, $current);

            if ($diff->isEmpty()) {
                $this->line("  {$entityClass}: no changes");

                continue;
            }

            $anyChange = true;
            $code = MigrationGenerator::generate($entityClass, $previous, $current, $diff);

            if ($this->option('dry-run')) {
                $this->comment("Would generate migration for {$entityClass}:");
                $this->line($code);

                continue;
            }

            $slug = self::migrationSlug($entityClass, $previous);
            $filename = date('Y_m_d_His').'_'.$slug.'.php';
            $path = database_path('migrations/glutamate/'.$filename);

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, $code);
            $store->save($current);
            $this->info("Generated: {$path}");
        }

        if (! $anyChange) {
            $this->info('All entities in sync, no migrations generated.');
        }

        return self::SUCCESS;
    }

    /**
     * Discover all entity classes in the configured directory.
     *
     * @return string[]
     */
    private static function discoverEntities(string $path, string $namespace): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $classes = [];
        foreach ($regex as $fileInfo) {
            $filePath = is_array($fileInfo) ? ($fileInfo[0] ?? '') : $fileInfo;

            if (! is_string($filePath) || $filePath === '') {
                continue;
            }

            $relativePath = str_replace(
                [rtrim($path, '/\\').DIRECTORY_SEPARATOR, '.php'],
                ['', ''],
                $filePath,
            );

            $className = $namespace.'\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

            if (class_exists($className) && is_subclass_of($className, Entity::class)) {
                $ref = new ReflectionClass($className);

                if (! $ref->isAbstract()) {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }

    private static function migrationSlug(string $entityClass, ?SchemaSnapshot $previous): string
    {
        $table = $entityClass::table();

        if ($previous === null) {
            return "create_{$table}_table";
        }

        return "update_{$table}_table";
    }
}
