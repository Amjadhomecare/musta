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

class SendMonthlyOnclickReportCommand extends Command
{
    protected $signature = 'report:onclick-monthly {--dry-run}';
    protected $description = 'Fetch last month OnClick API data and email it on the 3rd at 09:00 Asia/Dubai';

    public function handle(ReportService $reportService): int
    {
        if ($this->option('dry-run')) {
            $startDate = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
            $endDate   = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            $recipients = $reportService->getRecipients('monthly_onclick');

            $this->info("Dry run: Fetching data for {$startDate->toDateString()} to {$endDate->toDateString()}");
            $this->info("Dry run: would send email to: " . implode(', ', $recipients));
            $this->line("Dry run: would send email with payload:");
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        \App\Jobs\SendMonthlyOnclickReportJob::dispatch();

        $this->info('✅ Monthly OnClick Report job dispatched to background.');

        return self::SUCCESS;
    }
}
