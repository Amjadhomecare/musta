<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\CarbonImmutable;

class SendComparativeTrial3EndsEmail extends Command
{
    protected $signature = 'report:comparative-trial-3ends-email {--dry-run}';
    protected $description = 'Email the 3-month-end comparative trial (Class→Group) with change column';

    public function handle(\App\Services\ReportService $reportService): int
    {
        $asOf = CarbonImmutable::now('Asia/Dubai');
        
        if ($this->option('dry-run')) {
            $reportData = $reportService->getComparativeTrial3EndsData($asOf);
            $recipients = $reportService->getRecipients('comparative_trial');
            $meta = $reportData['meta'];
            $this->info("[dry-run] Would send 3-ends report to: ".implode(', ', $recipients));
            $this->info("Columns: {$meta['col1']['label']}, {$meta['col2']['label']}, {$meta['col3']['label']}, {$meta['col4']['label']}");
            return self::SUCCESS;
        }

        \App\Jobs\SendComparativeTrial3EndsJob::dispatch();
        $this->info('✅ SendComparativeTrial3Ends job dispatched to background.');

        return self::SUCCESS;
    }
}
