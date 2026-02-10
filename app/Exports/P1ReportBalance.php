<?php

namespace App\Exports;

use App\Models\categoryOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class P1ReportBalance implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        $rows = categoryOne::query()
            ->select([
                'id',
                'customer_id',
                'maid_id',
                'started_date',
                'contract_ref',
                'contract_status',
                'amount',
                'created_by',
                'created_at',
            ])
            ->with([
  
                'customerInfo:id,name,phone,ledger_id',
                'maidInfo:id,name',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // 2) Gather all ledger_ids we need, compute balances in one query
        $ledgerIds = $rows->pluck('customerInfo.ledger_id')->filter()->unique()->values();

        $balances = DB::table('general_journal_vouchers')
            ->select('ledger_id', DB::raw("
                SUM(CASE WHEN type = 'debit' THEN amount ELSE -amount END) AS balance
            "))
            ->whereIn('ledger_id', $ledgerIds)
            ->groupBy('ledger_id')
            ->pluck('balance', 'ledger_id'); // map: [ledger_id => balance]

        // 3) Keep only rows with positive closing balance
        $filtered = $rows->filter(function ($r) use ($balances) {
            $ledgerId = $r->customerInfo?->ledger_id;
            if (!$ledgerId) return false;
            return ((float)($balances[$ledgerId] ?? 0)) > 0;
        });

        if ($filtered->isEmpty()) {
            return collect();
        }

        // 4) Map to your existing columns (no per-row DB calls)
        $export = $filtered
            ->sortByDesc('created_at')
            ->map(function ($r) use ($balances) {
                $customer  = $r->customerInfo;
                $maid      = $r->maidInfo;
                $ledgerId  = $customer?->ledger_id;
                $closing   = (float)($balances[$ledgerId] ?? 0);

                return [
                    'Customer'         => $customer->name ?? 'N/A',
                    'Maid'             => $maid->name ?? 'N/A',
                    'Started Date'     => $r->started_date,
                    'Contract Ref'     => $r->contract_ref,
                    'Contract Status'  => $r->contract_status,
                    'Amount'           => (float)$r->amount,
                    'Closing Balance'  => $closing,
                    'Created By'       => $r->created_by,
                ];
            })
            ->values();

        return collect($export);
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Maid',
            'Started Date',
            'Contract Ref',
            'Contract Status',
            'Amount',
            'Closing Balance',
            'Created By',
        ];
    }
}
