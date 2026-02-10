<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaidService
{
    /**
     * Resets maid_booked to NULL for approved maids booked for over a day.
     *
     * @return int Number of affected records.
     */
    public function resetStaleBookedMaids(): int
    {
        $affected = DB::table('maids_d_b_s')
            ->where('maid_status', 'approved')
            ->where('maid_booked', 'like', '%booked%')
            ->where('updated_at', '<=', now()->subDay())
            ->update(['maid_booked' => null]);

        Log::info("MaidService: {$affected} stale booked maid records reset.");

        return $affected;
    }
}
