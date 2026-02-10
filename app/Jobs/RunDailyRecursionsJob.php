<?php

namespace App\Jobs;

use App\Services\AccountingService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunDailyRecursionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function handle(AccountingService $accountingService): void
    {
        $today = CarbonImmutable::now('Asia/Dubai');
        
        try {
            $count = $accountingService->processDailyRecursions($today);
            Log::info("✅ RunDailyRecursionsJob completed. {$count} entries processed.");
        } catch (\Throwable $e) {
            Log::error('❌ RunDailyRecursionsJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
