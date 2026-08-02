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

final class GenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'glutamate:generate {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Detect schema changes and generate migration files';

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
            if (! $this->option('dry-run')) {
                DocblockGenerator::update($modelClass, SchemaCompiler::compile($modelClass));
            }

            $current = SchemaSnapshot::fromModel($modelClass);
            $previous = $store->load($modelClass);
            $diff = SchemaDiffer::diff($previous, $current);

            if ($diff->isEmpty()) {
                $this->line("  {$modelClass}: no changes");

                continue;
            }

            $anyChange = true;
            $code = MigrationGenerator::generate($modelClass, $previous, $current, $diff);

            if ($this->option('dry-run')) {
                $this->comment("Would generate migration for {$modelClass}:");
                $this->line($code);

                continue;
            }

            $slug = self::migrationSlug($modelClass, $previous);
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

    /**
     * @param  class-string  $modelClass
     */
    private static function migrationSlug(string $modelClass, ?SchemaSnapshot $previous): string
    {
        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();

        if ($previous === null) {
            return "create_{$table}_table";
        }

        return "update_{$table}_table";
    }
}
