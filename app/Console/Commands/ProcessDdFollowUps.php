<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DdFollowUpService;

class ProcessDdFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dd:process-follow-ups {--dry : Show what will happen without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Process Direct Debit follow-ups for rejected mandates with RR01 signature issues';

    /**
     * Execute the console command.
     */
    public function handle(DdFollowUpService $service): int
    {
        $dry = (bool) $this->option('dry');

        if ($dry) {
            // Run with dry mode, showing output in CLI
            $this->info('DRY RUN MODE - No changes will be saved');
            $stats = $service->processFollowUps(true, function($message) {
                $this->line($message);
            });
            $this->info("DRY RUN complete. Would process: {$stats['processed']}, SMS: {$stats['sms_sent']}, Manual: {$stats['marked_manual']}");
            return self::SUCCESS;
        }

        // Run the service directly (synchronously)
        $this->info('🚀 Starting Direct Debit follow-up processing...');
        $stats = $service->processFollowUps(false, function($message) {
            $this->line($message);
        });
        $this->info("✅ Complete - Processed: {$stats['processed']}, SMS Sent: {$stats['sms_sent']}, Marked Manual: {$stats['marked_manual']}");

        return self::SUCCESS;
    }
}
