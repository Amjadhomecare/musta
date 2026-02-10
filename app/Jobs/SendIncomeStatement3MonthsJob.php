<?php

namespace App\Jobs;

use App\Services\ReportService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendIncomeStatement3MonthsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function handle(ReportService $reportService): void
    {
        $asOf = CarbonImmutable::now('Asia/Dubai');
        
        try {
            $reportData = $reportService->getIncomeStatement3MonthsData($asOf);
            $recipients = $reportService->getRecipients('income_statement');

            if (empty($recipients)) {
                Log::warning('SendIncomeStatement3MonthsJob: No recipients found.');
                return;
            }

            Mail::send(
                'emails.income_statement_3months',
                $reportData,
                function ($message) use ($recipients, $reportData) {
                    $message->to($recipients)->subject($reportData['meta']['heading']);
                }
            );

            Log::info("✅ SendIncomeStatement3MonthsJob completed. Sent to: " . implode(', ', $recipients));
        } catch (\Throwable $e) {
            Log::error('❌ SendIncomeStatement3MonthsJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
