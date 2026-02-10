<?php

namespace App\Jobs;

use App\Mail\DailyOnclickReport;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailyOnclickReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 300; // 5 minutes

    public function handle(ReportService $reportService): void
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate   = Carbon::today()->endOfDay();
        $periodLabel = $startDate->toDateString(); // e.g. 2023-10-27

        try {
            $data = $reportService->getDailyOneClickReportData($startDate, $endDate);
            // $data['report_title'] = 'Daily Report'; // No longer needed as we use specific Mailable/Blade

            // We reuse the 'monthly_onclick' recipients for now
            $recipients = $reportService->getRecipients('monthly_onclick');

            if (empty($recipients)) {
                Log::warning('SendDailyOnclickReportJob: No recipients found.');
                return;
            }

            Mail::to($recipients)->send(new DailyOnclickReport($data, $periodLabel));
            Log::info("✅ SendDailyOnclickReportJob completed. Sent to: " . implode(', ', $recipients));
        } catch (\Throwable $e) {
            Log::error('❌ Failed in SendDailyOnclickReportJob', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
