<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetBookedMaids extends Command
{
    // artisan command name
    protected $signature = 'maids:reset-booked';

    protected $description = 'Resets maid_booked to NULL for approved maids booked for over a day';

    public function handle(): int
    {
        \App\Jobs\ResetBookedMaidsJob::dispatch();

        $this->info('✅ ResetBookedMaids job dispatched to background.');

        return self::SUCCESS;
    }
}
