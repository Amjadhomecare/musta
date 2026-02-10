<?php

namespace App\Services;

use App\Models\General_journal_voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class AccountingService
{
    /**
     * Process daily accounting recursions.
     *
     * @param CarbonInterface $today
     * @param bool $dryRun
     * @param callable|null $logCallback
     * @return int Number of processed entries.
     */
    public function processDailyRecursions(CarbonInterface $today, bool $dryRun = false, callable $logCallback = null): int
    {
        $tz = 'Asia/Dubai';
        $today = $today->startOfDay();

        // Active, started, not run today
        $recursions = DB::table('accounting_recursions')
            ->where('recursion_number', '>', 0)
            ->whereDate('start_date', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('last_run_at')
                  ->orWhereDate('last_run_at', '<', $today->toDateString());
            })
            ->orderBy('id')
            ->get();

        if ($recursions->isEmpty()) {
            return 0;
        }

        $count = 0;
        $refCode = 'cj_' . Str::upper(Str::random(5));

        foreach ($recursions as $rec) {
            $start = $rec->start_date ? CarbonImmutable::parse($rec->start_date, $tz)->startOfDay() : null;
            if (!$start) continue;

            $last = $rec->last_run_at ? CarbonImmutable::parse($rec->last_run_at, $tz)->startOfDay() : null;

            if (!$last) {
                if ($today->lt($start)) continue; 
            } else {
                $nextDue = ((int)$rec->recursion === 2)
                    ? $last->addWeek()
                    : $last->addMonthsNoOverflow(1);
                if ($today->lt($nextDue)) continue;
            }

            $remaining = max(0, (int)$rec->recursion_number - 1);
            $notesText = trim(($rec->name ?? '') . ' | remaining: ' . $remaining);

            if ($dryRun) {
                if ($logCallback) {
                    $logCallback("DRY RUN: JV ledger_id={$rec->ledger_id}, type={$rec->post_type}, amount={$rec->amount}, notes='{$notesText}' (refCode {$refCode}, refNumber 0)");
                }
                $count++;
                continue;
            }

            DB::transaction(function () use ($rec, $today, $refCode, $remaining, $notesText) {
                General_journal_voucher::create([
                    'date'            => $today->toDateString(),
                    'refCode'         => $refCode,
                    'refNumber'       => 0,
                    'voucher_type'    => 'Journal Voucher',
                    'type'            => $rec->post_type,
                    'ledger_id'       => $rec->ledger_id,
                    'amount'          => $rec->amount,
                    'invoice_balance' => 0.00,
                    'notes'           => $notesText,
                    'created_by'      => 'cron_job',
                    'updated_by'      => null,
                    'created_at'      => now($today->tzName),
                    'updated_at'      => now($today->tzName),
                ]);

                DB::table('accounting_recursions')
                    ->where('id', $rec->id)
                    ->update([
                        'last_run_at'      => $today->toDateTimeString(),
                        'recursion_number' => DB::raw('GREATEST(recursion_number - 1, 0)'),
                        'updated_at'       => now($today->tzName),
                    ]);
            });

            $count++;
        }

        return $count;
    }
}
