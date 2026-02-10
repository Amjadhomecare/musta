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

class SendComparativeTrial3EndsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function handle(ReportService $reportService): void
    {
        $asOf = CarbonImmutable::now('Asia/Dubai');
        
        try {
            $reportData = $reportService->getComparativeTrial3EndsData($asOf);
            $recipients = $reportService->getRecipients('comparative_trial');

            if (empty($recipients)) {
                Log::warning('SendComparativeTrial3EndsJob: No recipients found.');
                return;
            }

            Mail::send(
                'emails.comparative_trial_3ends',
                $reportData,
                function ($message) use ($recipients, $reportData) {
                    $meta = $reportData['meta'];
                    $subject = "Comparative Trial — {$meta['col1']['label']} / {$meta['col2']['label']} / {$meta['col3']['label']}";
                    $message->to($recipients)->subject($subject);
                }
            );

            Log::info("✅ SendComparativeTrial3EndsJob completed. Sent to: " . implode(', ', $recipients));
        } catch (\Throwable $e) {
            Log::error('❌ SendComparativeTrial3EndsJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
