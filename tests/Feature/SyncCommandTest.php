<?php

declare(strict_types=1);

namespace Glutamate\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

it('runs dry-run without writing any files to disk', function () {
    $tempEntitiesDir = sys_get_temp_dir().'/glutamate_entities_'.uniqid();
    $tempSnapshotsDir = sys_get_temp_dir().'/glutamate_snapshots_'.uniqid();

    // Create temp entity folder
    if (! is_dir($tempEntitiesDir)) {
        mkdir($tempEntitiesDir, 0755, true);
    }

    $classCode = <<<'PHP'
<?php

namespace Glutamate\Tests\Feature\TempEntitiesDry;

use Glutamate\Entity;
use Glutamate\Columns\StringColumn;

class TempProductDryEntity extends Entity
{
    public static function table(): string
    {
        return 'temp_products_dry';
    }

    public static function name(): StringColumn
    {
        return StringColumn::make();
    }
}
PHP;

    $filePath = $tempEntitiesDir.'/TempProductDryEntity.php';
    file_put_contents($filePath, $classCode);

    // Require class manually since it is not autoloaded
    require_once $filePath;

    config([
        'glutamate.entities_path' => $tempEntitiesDir,
        'glutamate.entities_namespace' => 'Glutamate\\Tests\\Feature\\TempEntitiesDry',
        'glutamate.snapshot_path' => $tempSnapshotsDir,
    ]);

    // Clean up migrations directory
    $migrationsDir = database_path('migrations/glutamate');
    File::deleteDirectory($migrationsDir);

    // Run --dry-run
    $this->artisan('glutamate:sync --dry-run')
        ->expectsOutputToContain('Would generate migration for')
        ->expectsOutputToContain('Schema::create')
        ->assertSuccessful();

    // Assert no migration or snapshot files were written
    expect(is_dir($migrationsDir))->toBeFalse();
    expect(is_dir($tempSnapshotsDir))->toBeFalse();

    // Cleanup
    File::deleteDirectory($tempEntitiesDir);
    File::deleteDirectory($tempSnapshotsDir);
});

it('runs sync, writes files, and is idempotent on subsequent runs', function () {
    $tempEntitiesDir = sys_get_temp_dir().'/glutamate_entities_'.uniqid();
    $tempSnapshotsDir = sys_get_temp_dir().'/glutamate_snapshots_'.uniqid();

    if (! is_dir($tempEntitiesDir)) {
        mkdir($tempEntitiesDir, 0755, true);
    }

    $classCode = <<<'PHP'
<?php

namespace Glutamate\Tests\Feature\TempEntitiesSync;

use Glutamate\Entity;
use Glutamate\Columns\StringColumn;

class TempProductSyncEntity extends Entity
{
    public static function table(): string
    {
        return 'temp_products_sync';
    }

    public static function name(): StringColumn
    {
        return StringColumn::make();
    }
}
PHP;

    $filePath = $tempEntitiesDir.'/TempProductSyncEntity.php';
    file_put_contents($filePath, $classCode);

    require_once $filePath;

    config([
        'glutamate.entities_path' => $tempEntitiesDir,
        'glutamate.entities_namespace' => 'Glutamate\\Tests\\Feature\\TempEntitiesSync',
        'glutamate.snapshot_path' => $tempSnapshotsDir,
    ]);

    $migrationsDir = database_path('migrations/glutamate');
    File::deleteDirectory($migrationsDir);

    // 1. Run sync (first time)
    $this->artisan('glutamate:sync')
        ->expectsOutputToContain('Generated:')
        ->assertSuccessful();

    // Verify snapshot file exists
    $snapshotFile = $tempSnapshotsDir.'/Glutamate.Tests.Feature.TempEntitiesSync.TempProductSyncEntity.json';
    expect(file_exists($snapshotFile))->toBeTrue();

    // Verify migration file exists
    $migrationFiles = File::files($migrationsDir);
    expect($migrationFiles)->toHaveCount(1);
    expect($migrationFiles[0]->getFilename())->toContain('create_temp_products_sync_table');

    // 2. Run sync (second time, no changes)
    $this->artisan('glutamate:sync')
        ->expectsOutputToContain('All entities in sync, no migrations generated.')
        ->assertSuccessful();

    // Verify no new migration file was written
    $migrationFilesAfter = File::files($migrationsDir);
    expect($migrationFilesAfter)->toHaveCount(1);

    // Cleanup
    File::deleteDirectory($tempEntitiesDir);
    File::deleteDirectory($tempSnapshotsDir);
    File::deleteDirectory($migrationsDir);
});

it('loads generated migrations automatically via Service Provider loadMigrationsFrom()', function () {
    $tempEntitiesDir = sys_get_temp_dir().'/glutamate_entities_'.uniqid();
    $tempSnapshotsDir = sys_get_temp_dir().'/glutamate_snapshots_'.uniqid();

    if (! is_dir($tempEntitiesDir)) {
        mkdir($tempEntitiesDir, 0755, true);
    }

    $classCode = <<<'PHP'
<?php

namespace Glutamate\Tests\Feature\TempEntitiesProvider;

use Glutamate\Entity;
use Glutamate\Columns\StringColumn;

class TempProductProviderEntity extends Entity
{
    public static function table(): string
    {
        return 'temp_products_provider';
    }

    public static function name(): StringColumn
    {
        return StringColumn::make();
    }
}
PHP;

    $filePath = $tempEntitiesDir.'/TempProductProviderEntity.php';
    file_put_contents($filePath, $classCode);

    require_once $filePath;

    config([
        'glutamate.entities_path' => $tempEntitiesDir,
        'glutamate.entities_namespace' => 'Glutamate\\Tests\\Feature\\TempEntitiesProvider',
        'glutamate.snapshot_path' => $tempSnapshotsDir,
    ]);

    $migrationsDir = database_path('migrations/glutamate');
    File::deleteDirectory($migrationsDir);

    // Run sync to generate the migration file into database_path('migrations/glutamate')
    $this->artisan('glutamate:sync')
        ->assertSuccessful();

    // Call Artisan migrate DIRECTLY WITHOUT loadMigrationsFrom()
    Artisan::call('migrate');

    // Assert that the table is successfully created
    expect(Schema::hasTable('temp_products_provider'))->toBeTrue();
    expect(Schema::hasColumn('temp_products_provider', 'name'))->toBeTrue();

    // Cleanup
    File::deleteDirectory($tempEntitiesDir);
    File::deleteDirectory($tempSnapshotsDir);
    File::deleteDirectory($migrationsDir);
});
