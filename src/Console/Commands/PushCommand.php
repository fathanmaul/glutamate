<?php

declare(strict_types=1);

namespace Glutamate\Console\Commands;

use Glutamate\Schema\DocblockGenerator;
use Glutamate\Schema\MigrationGenerator;
use Glutamate\Schema\SchemaDiffer;
use Glutamate\Schema\SchemaSnapshot;
use Glutamate\Schema\SnapshotStore;
use Glutamate\SchemaCompiler;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

final class PushCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'glutamate:push';

    /**
     * The console command description.
     */
    protected $description = 'Push schema changes directly to the database without generating migration files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelsPath = config('glutamate.models_path', app_path('Models'));
        $modelsNamespace = config('glutamate.models_namespace', 'App\\Models');
        $snapshotPath = config('glutamate.snapshot_path', storage_path('framework/glutamate/snapshots'));

        $store = new SnapshotStore($snapshotPath);
        $entities = self::discoverEntities($modelsPath, $modelsNamespace);

        if (empty($entities)) {
            $this->info('No models found.');

            return self::SUCCESS;
        }

        $anyChange = false;

        foreach ($entities as $modelClass) {
            DocblockGenerator::update($modelClass, SchemaCompiler::compile($modelClass));

            $current = SchemaSnapshot::fromModel($modelClass);
            $previous = $store->load($modelClass);
            $diff = SchemaDiffer::diff($previous, $current);

            if ($diff->isEmpty()) {
                $this->line("  {$modelClass}: no changes");

                continue;
            }

            $anyChange = true;
            $code = MigrationGenerator::generate($modelClass, $previous, $current, $diff);

            // Execute migration directly in-memory using a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'glutamate_');

            if ($tempFile === false) {
                $this->error('Failed to create temporary file.');

                return self::FAILURE;
            }

            file_put_contents($tempFile, $code);

            try {
                $migration = require $tempFile;
                $migration->up();
            } finally {
                @unlink($tempFile);
            }

            $store->save($current);
            $this->info("Pushed: schema for {$modelClass} applied directly to the database.");
        }

        if (! $anyChange) {
            $this->info('All entities in sync with the database.');
        }

        return self::SUCCESS;
    }

    /**
     * Discover all entity classes in the configured directory.
     *
     * @return class-string[]
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

            if (class_exists($className)) {
                $isModel = is_subclass_of($className, Model::class);

                if ($isModel) {
                    $ref = new ReflectionClass($className);

                    if (! $ref->isAbstract()) {
                        $classes[] = $className;
                    }
                }
            }
        }

        return $classes;
    }
}
