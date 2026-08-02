<?php

declare(strict_types=1);

namespace Glutamate\Console\Commands;

use Illuminate\Console\Command;

class GlutamateCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'glutamate:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package glutamate.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Glutamate placeholder command executed.');

        return self::SUCCESS;
    }
}
