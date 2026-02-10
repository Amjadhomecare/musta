<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\CarbonImmutable;

class SendIncomeStatement3MonthsEmail extends Command
{
    protected $signature = 'report:income-3months-email {--dry-run}';
    protected $description = 'Email a 3-month Income Statement (Revenue & Expenses by Group) with change column';

    public function handle(\App\Services\ReportService $reportService): int
    {
        $asOf = CarbonImmutable::now('Asia/Dubai');
        
        if ($this->option('dry-run')) {
            $reportData = $reportService->getIncomeStatement3MonthsData($asOf);
            $recipients = $reportService->getRecipients('income_statement');
            $meta = $reportData['meta'];
            $this->info("[dry-run] Would send 3-month Income Statement to: ".implode(', ', $recipients));
            $this->info("Columns: {$meta['col1']['label']}, {$meta['col2']['label']}, {$meta['col3']['label']}, {$meta['col4']['label']}");
            return self::SUCCESS;
        }

        \App\Jobs\SendIncomeStatement3MonthsJob::dispatch();
        $this->info('✅ SendIncomeStatement3Months job dispatched to background.');

        return self::SUCCESS;
    }
}
