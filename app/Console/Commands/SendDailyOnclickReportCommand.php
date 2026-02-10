<?php

namespace App\Console\Commands;

use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendDailyOnclickReportCommand extends Command
{
    protected $signature = 'report:onclick-daily {--dry-run}';
    protected $description = 'Fetch today OnClick API data and email it daily';

    public function handle(ReportService $reportService): int
    {
        if ($this->option('dry-run')) {
            $startDate = CarbonImmutable::today()->startOfDay();
            $endDate   = CarbonImmutable::today()->endOfDay();
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            $data['report_title'] = 'Daily Report'; // Inject title for consistency with job
            $recipients = $reportService->getRecipients('monthly_onclick');

            $this->info("Dry run: Fetching data for {$startDate->toDateString()} to {$endDate->toDateString()}");
            $this->info("Dry run: would send email to: " . implode(', ', $recipients));
            $this->line("Dry run: would send email with payload:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        \App\Jobs\SendDailyOnclickReportJob::dispatch();

        $this->info('✅ Daily OnClick Report job dispatched to background.');

        return self::SUCCESS;
    }
}
