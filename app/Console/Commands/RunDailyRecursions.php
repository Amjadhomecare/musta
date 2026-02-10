<?php

namespace App\Console\Commands;

use App\Models\General_journal_voucher;
use App\Services\AccountingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

class RunDailyRecursions extends Command
{
    protected $signature = 'recursions:daily {--dry : Show what will happen without saving}';
    protected $description = 'Runs every day at 10 PM to create Journal Voucher entries from accounting_recursions.';

    public function handle(AccountingService $accountingService): int
    {
        $today = CarbonImmutable::now('Asia/Dubai');
        $dry   = (bool) $this->option('dry');

        if ($dry) {
            $count = $accountingService->processDailyRecursions($today, true, function($message) {
                $this->line($message);
            });
            $this->info("DRY RUN complete. {$count} entries would be created.");
            return self::SUCCESS;
        }

        \App\Jobs\RunDailyRecursionsJob::dispatch();
        $this->info('✅ RunDailyRecursions job dispatched to background.');

        return self::SUCCESS;
    }
}
