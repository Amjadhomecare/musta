<?php

namespace App\Jobs;

use App\Mail\MonthlyOnclickReport;
use App\Services\ReportService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendMonthlyOnclickReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 300; // 5 minutes

    public function handle(ReportService $reportService): void
    {
        $startDate = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $endDate   = CarbonImmutable::now()->subMonthNoOverflow()->endOfMonth();
        $periodLabel = $startDate->format('M Y');

        try {
            $data = $reportService->getOneClickReportData($startDate, $endDate);
            $recipients = $reportService->getRecipients('monthly_onclick');

            if (empty($recipients)) {
                Log::warning('SendMonthlyOnclickReportJob: No recipients found.');
                return;
            }

            Log::info("📧 SendMonthlyOnclickReportJob: About to send email", [
                'period' => $periodLabel,
                'recipients' => $recipients,
                'recipient_count' => count($recipients),
                'data_keys' => array_keys($data),
                'p1_count' => $data['p1Count'] ?? 'N/A',
                'p4_count' => $data['p4Count'] ?? 'N/A',
            ]);

            Mail::to($recipients)->send(new MonthlyOnclickReport($data, $periodLabel));
            
            Log::info("✅ SendMonthlyOnclickReportJob completed. Sent to: " . implode(', ', $recipients));
        } catch (\Throwable $e) {
            Log::error('❌ Failed in SendMonthlyOnclickReportJob', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }
}
