<?php

namespace App\Jobs;

use App\Services\MaidService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResetBookedMaidsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function handle(MaidService $maidService): void
    {
        try {
            $affected = $maidService->resetStaleBookedMaids();
            Log::info("✅ ResetBookedMaidsJob completed. {$affected} records updated.");
        } catch (\Throwable $e) {
            Log::error('❌ ResetBookedMaidsJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
