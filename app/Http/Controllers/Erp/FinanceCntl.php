<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\CarbonImmutable;


class FinanceCntl extends Controller
{
    public function customerBalances(Request $request): JsonResponse
    {
        $customerGroups = ['customer'];
        $balanceFilter = $request->query('balance'); 
        
        $query = DB::table('general_journal_vouchers as gjv')
            ->join('all_account_ledger__d_b_s as ledger', 'gjv.ledger_id', '=', 'ledger.id')
            ->select(
                'gjv.ledger_id',
                'ledger.ledger as account_name',
                'ledger.group',
                DB::raw("SUM(CASE WHEN gjv.type = 'debit' THEN gjv.amount ELSE -gjv.amount END) as balance"),
                DB::raw("MAX(gjv.date) as latest_date")
            )
            ->whereIn('ledger.group', $customerGroups)
            ->groupBy('gjv.ledger_id', 'ledger.ledger', 'ledger.group');

        // Apply balance filter
        if ($balanceFilter === 'positive') {
            $query->havingRaw("SUM(CASE WHEN gjv.type = 'debit' THEN gjv.amount ELSE -gjv.amount END) > 0");
        } elseif ($balanceFilter === 'negative') {
            $query->havingRaw("SUM(CASE WHEN gjv.type = 'debit' THEN gjv.amount ELSE -gjv.amount END) < 0");
        } else {
            $query->havingRaw("SUM(CASE WHEN gjv.type = 'debit' THEN gjv.amount ELSE -gjv.amount END) != 0");
        }

        $results = $query->orderBy('latest_date', 'asc')->get();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row->group][] = [
                'ledger_id' => $row->ledger_id,
                'account_name' => $row->account_name,
                'balance' => $row->balance,
                'latest_date' => $row->latest_date,
            ];
        }

        return response()->json($grouped);
    }



public function comparativeTrial(Request $request)
{
    // Dates from user (defaults if empty)
    $start = $request->input('start', '2024-12-31');
     $end   = $request->input('end', now()->toDateString());

    // Sum by CLASS + GROUP (debit = +, credit = -)
    $rows = DB::table('general_journal_vouchers as jv')
        ->join('all_account_ledger__d_b_s as ld', 'ld.id', '=', 'jv.ledger_id')
        ->selectRaw("
            COALESCE(ld.class, '(No Class)')  AS ledger_class,
            COALESCE(ld.group, '(No Group)') AS ledger_group,
            SUM(CASE WHEN jv.date <= ? THEN
                    CASE WHEN jv.type='debit' THEN jv.amount
                         WHEN jv.type='credit' THEN -jv.amount
                         ELSE 0 END
                ELSE 0 END) AS opening_balance,
            SUM(CASE WHEN jv.date <= ? THEN
                    CASE WHEN jv.type='debit' THEN jv.amount
                         WHEN jv.type='credit' THEN -jv.amount
                         ELSE 0 END
                ELSE 0 END) AS ending_balance
        ", [$start, $end])
        ->groupBy('ld.class', 'ld.group')
        ->orderBy('ld.class')
        ->orderBy('ld.group')
        ->get()
        ->map(function ($r) {        // compute change in PHP for clarity
            $r->change_amount = (float)$r->ending_balance - (float)$r->opening_balance;
            return $r;
        });

    // Build: classes => [rows(by group), subtotals]
    $classes = $rows->groupBy('ledger_class')->map(function ($items) {
        return [
            'rows' => $items->values(),
            'subtotal_opening' => (float)$items->sum('opening_balance'),
            'subtotal_ending'  => (float)$items->sum('ending_balance'),
            'subtotal_change'  => (float)$items->sum('change_amount'),
        ];
    });

    // Grand total
    $totals = [
        'opening' => (float)$rows->sum('opening_balance'),
        'ending'  => (float)$rows->sum('ending_balance'),
        'change'  => (float)$rows->sum('change_amount'),
    ];

    return view('ERP.accounting.comparative_trial', compact('classes','totals','start','end'));
}


public function comparativeTrial3MonthEndsApi(Request $request): JsonResponse
{
    $tz   = 'Asia/Dubai';
    $asOf = CarbonImmutable::parse($request->query('asof', now()->toDateString()))
            ->timezone($tz)
            ->endOfDay();

    // Exclude current month
    $m3 = $asOf->subMonthNoOverflow()->endOfMonth(); // Latest closed month (e.g., Sep 30)
    $m2 = $m3->subMonthNoOverflow()->endOfMonth();   // e.g., Aug 31
    $m1 = $m2->subMonthNoOverflow()->endOfMonth();   // e.g., Jul 31

    $d1 = $m1->toDateString();  // Jul 31
    $d2 = $m2->toDateString();  // Aug 31
    $d3 = $m3->toDateString();  // Sep 30

    $rows = DB::table('general_journal_vouchers as jv')
        ->join('all_account_ledger__d_b_s as ld', 'ld.id', '=', 'jv.ledger_id')
        ->selectRaw("
            COALESCE(ld.class, '(No Class)')  AS ledger_class,
            COALESCE(ld.group, '(No Group)')  AS ledger_group,

            -- July-end balance
            SUM(CASE WHEN jv.date <= ? THEN
                     CASE WHEN jv.type='debit' THEN jv.amount
                          WHEN jv.type='credit' THEN -jv.amount
                          ELSE 0 END
                ELSE 0 END) AS jul_end,

            -- August-end balance
            SUM(CASE WHEN jv.date <= ? THEN
                     CASE WHEN jv.type='debit' THEN jv.amount
                          WHEN jv.type='credit' THEN -jv.amount
                          ELSE 0 END
                ELSE 0 END) AS aug_end,

            -- September-end balance
            SUM(CASE WHEN jv.date <= ? THEN
                     CASE WHEN jv.type='debit' THEN jv.amount
                          WHEN jv.type='credit' THEN -jv.amount
                          ELSE 0 END
                ELSE 0 END) AS sep_end
        ", [$d1, $d2, $d3])
        ->groupByRaw('1, 2')
        ->orderBy('ledger_class')
        ->orderBy('ledger_group')
        ->get()
        ->map(function ($r) {
            $r->jul_end = (float)$r->jul_end;
            $r->aug_end = (float)$r->aug_end;
            $r->sep_end = (float)$r->sep_end;
            // ✅ New column: Change = Sep - Jul
            $r->change_amount = (float)$r->sep_end - (float)$r->jul_end;
            return $r;
        });

    // Group by class + subtotal
    $classes = [];
    foreach ($rows->groupBy('ledger_class') as $className => $items) {
        $classes[$className] = [
            'rows' => $items->values()->map(fn($r) => [
                'ledger_group'   => $r->ledger_group,
                'jul_end'        => $r->jul_end,
                'aug_end'        => $r->aug_end,
                'sep_end'        => $r->sep_end,
                'change_amount'  => $r->change_amount,
            ]),
            'subtotal_jul'    => (float)$items->sum('jul_end'),
            'subtotal_aug'    => (float)$items->sum('aug_end'),
            'subtotal_sep'    => (float)$items->sum('sep_end'),
            'subtotal_change' => (float)$items->sum('change_amount'),
        ];
    }

    $totals = [
        'jul'     => (float)$rows->sum('jul_end'),
        'aug'     => (float)$rows->sum('aug_end'),
        'sep'     => (float)$rows->sum('sep_end'),
        'change'  => (float)$rows->sum('change_amount'),
    ];

    return response()->json([
        'asof'    => $asOf->toDateString(),
        'columns' => [
            ['key' => 'jul_end', 'label' => $m1->format('M Y'), 'date' => $d1],
            ['key' => 'aug_end', 'label' => $m2->format('M Y'), 'date' => $d2],
            ['key' => 'sep_end', 'label' => $m3->format('M Y'), 'date' => $d3],
            ['key' => 'change_amount', 'label' => 'Change (Sep − Jul)'],
        ],
        'classes' => $classes,
        'totals'  => $totals,
    ]);
}
}
