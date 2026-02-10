<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class P4ReportBalanceStream implements FromQuery, WithMapping, WithHeadings, WithCustomChunkSize, ShouldQueue
{
    use Exportable;

    public function query()
    {
        // ── Subquery: latest c4 row per (customer_id, maid_id) by created_at ──
        $latestC4 = DB::table('category4_models')
            ->select('customer_id', 'maid_id', DB::raw('MAX(created_at) AS max_created_at'))
            ->groupBy('customer_id', 'maid_id');

        // ── Subquery: closing balances per ledger_id ──
        $balances = DB::table('general_journal_vouchers')
            ->select('ledger_id', DB::raw("
                SUM(CASE WHEN type = 'debit' THEN amount ELSE -amount END) AS balance
            "))
            ->groupBy('ledger_id');

        // ── Subquery: latest invoice per (ledger_id, maid_id) ──
        $latestInvDate = DB::table('general_journal_vouchers')
            ->select('ledger_id', 'maid_id', DB::raw('MAX(date) AS max_date'))
            ->where('voucher_type', 'Invoice Package4')
            ->where('type', 'debit')
            ->groupBy('ledger_id', 'maid_id');

        $latestInv = DB::table('general_journal_vouchers as g')
            ->joinSub($latestInvDate, 'mx', function ($j) {
                $j->on('g.ledger_id', '=', 'mx.ledger_id')
                  ->on('g.maid_id',   '=', 'mx.maid_id')
                  ->on('g.date',      '=', 'mx.max_date');
            })
            ->where('g.voucher_type', 'Invoice Package4')
            ->where('g.type', 'debit')
            ->select('g.ledger_id','g.maid_id','g.date','g.amount','g.refCode');

        // ── Base: latest C4 rows (so we only export one row per (customer, maid)) ──
        $q = DB::table('category4_models as c4')
            // keep only latest c4 per pair
            ->joinSub($latestC4, 'lc4', function ($j) {
                $j->on('c4.customer_id', '=', 'lc4.customer_id')
                  ->on('c4.maid_id',     '=', 'lc4.maid_id')
                  ->on('c4.created_at',  '=', 'lc4.max_created_at');
            })

            // join customers (for name/phone/ledger_id)
            ->join('customers as cust', 'cust.id', '=', 'c4.customer_id')
            // join maids
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'c4.maid_id')

            // join balances (filter > 0)
            ->leftJoinSub($balances, 'bal', 'bal.ledger_id', '=', 'cust.ledger_id')

            // join latest invoice by (ledger_id, maid_id)
            ->leftJoinSub($latestInv, 'li', function ($j) {
                $j->on('li.ledger_id', '=', 'cust.ledger_id')
                  ->on('li.maid_id',   '=', 'c4.maid_id');
            })

            ->whereNotNull('cust.ledger_id')
            ->where('bal.balance', '>', 0)

            // final columns
            ->select([
                'c4.date as c4_date',
                'c4.Contract_ref',
                'c4.customer_id',
                'cust.name as customer_name',
                'cust.phone as customer_phone',
                'cust.ledger_id',

                'c4.maid_id',
                'm.name  as maid_name',
                'm.maid_type',

                'li.date   as inv_date',
                'li.amount as inv_amount',
                'li.refCode as inv_ref',

                'bal.balance as closing_balance',
                'c4.created_by',
                'c4.contract_status',
                'c4.created_at',
            ])
            ->orderByDesc('c4.created_at'); // stream newest first

        return $q;
    }

    public function map($row): array
    {
        // $row is stdClass from the query() select
        return [
            $row->c4_date,
            $row->Contract_ref,

            $row->customer_id,
            $row->customer_name ?? 'N/A',
            $row->customer_phone ?? 'N/A',

            $row->maid_id,
            $row->maid_name ?? 'N/A',
            $row->maid_type ?? 'N/A',

            $row->inv_date ?? 'N/A',
            (float)($row->inv_amount ?? 0),
            $row->inv_ref ?? 'N/A',

            (float)($row->closing_balance ?? 0),
            $row->created_by,
            $row->contract_status,
            $row->created_at,
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Contract Reference',

            'Customer ID',
            'Customer Name',
            'Customer Phone',

            'Maid ID',
            'Maid Name',
            'Maid Type',

            'Invoice Date',
            'Invoice Amount',
            'Invoice Ref Code',

            'Closing Balance',
            'Created By',
            'Contract Status',
            'created_at',
        ];
    }

    // Tune this based on DB and memory; 1k–5k is a good start
    public function chunkSize(): int
    {
        return 2000;
    }
}
