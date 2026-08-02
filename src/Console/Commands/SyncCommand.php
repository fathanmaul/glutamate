<?php

declare(strict_types=1);

namespace Glutamate\Console\Commands;

use Illuminate\Console\Command;

final class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'glutamate:sync {--dry-run}';

    /**
     * The console command description.
     */
    protected $description = 'Generate migration files and migrate the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->call('glutamate:generate', [
            '--dry-run' => $this->option('dry-run'),
        ]);

        if (! $this->option('dry-run')) {
            $this->call('migrate');
        }

        return self::SUCCESS;
    }
}
