<?php

namespace App\Services;

use App\Models\maidReturnCat1;
use App\Models\ReturnedMaid;
use App\Models\release;
use App\Models\Arrival;
use App\Models\categoryOne;
use App\Models\Category4Model;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use App\Models\ApplyVisa;
use App\Models\ReportRecipient;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonInterface;

class ReportService
{
    public function getOneClickReportData(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        // Top counters
        $maidReturnCat1Count = maidReturnCat1::whereBetween('created_at', [$startDate, $endDate])->count();
        $returnedMaidCount   = ReturnedMaid::whereBetween('created_at', [$startDate, $endDate])->count();
        $releaseCount        = release::whereBetween('created_at', [$startDate, $endDate])->count();
        $arrivalCount        = Arrival::whereBetween('created_at', [$startDate, $endDate])->count();
        $p1Count             = categoryOne::whereBetween('created_at', [$startDate, $endDate])->count();
        $p4Count             = Category4Model::whereBetween('created_at', [$startDate, $endDate])->count();

        $typing = General_journal_voucher::where('voucher_type', 'Typing Invoice')
            ->where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $p4AsNewContracts = Category4Model::where('Contract_ref', 'like', 'P4_%')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'HC');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $p5Count = Category4Model::with('maidInfo')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'Direct hire');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $p4AllActive = Category4Model::where('contract_status', '1')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'HC');
            })
            ->count();

        $p5AllActive = Category4Model::where('contract_status', '1')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'Direct hire');
            })
            ->count();

        // By creator
        $categoryOneCounts = categoryOne::select('created_by', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        $category4ModelCounts = Category4Model::select('created_by', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        // Release breakdown
        $groupByRelased = release::select('new_status', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('new_status')
            ->get();

        // Cash received (debit)
        $cashReport = All_account_ledger_DB::select('all_account_ledger__d_b_s.ledger', DB::raw('SUM(gjv.amount) AS total_received'))
            ->join('general_journal_vouchers AS gjv', 'all_account_ledger__d_b_s.id', '=', 'gjv.ledger_id')
            ->where('all_account_ledger__d_b_s.group', 'cash equivalent')
            ->where('gjv.type', 'debit')
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('all_account_ledger__d_b_s.ledger')
            ->get();

        // Cash paid (credit)
        $creditCashReport = All_account_ledger_DB::select('all_account_ledger__d_b_s.ledger', DB::raw('SUM(gjv.amount) AS total_paid'))
            ->join('general_journal_vouchers AS gjv', 'all_account_ledger__d_b_s.id', '=', 'gjv.ledger_id')
            ->where('all_account_ledger__d_b_s.group', 'cash equivalent')
            ->where('gjv.type', 'credit')
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('all_account_ledger__d_b_s.ledger')
            ->get();

        // Closing balances
        $ledgers = All_account_ledger_DB::select('id', 'ledger')
            ->where('group', 'cash equivalent')
            ->distinct()
            ->get();

        $closingBalances = $ledgers->map(function ($ledger) {
            return [
                'ledger'           => $ledger->ledger,
                'closing_balance'  => General_journal_voucher::calculateCustomerBalanceByLedgerId($ledger->id),
            ];
        })->values();

        // Visa applications
        $serviceLabels = [
            0 => 'visa_renewal',
            2 => 'new_visa',
            3 => 'cancellation',
            4 => 'absconding',
            5 => 'other',
        ];

        $countsByService = ApplyVisa::where('status', '!=', 11)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('service', DB::raw('COUNT(*) AS total'))
            ->groupBy('service')
            ->pluck('total', 'service');

        $visaApplications = collect($serviceLabels)->map(function ($label, $code) use ($countsByService) {
            return [
                'service' => $code,
                'label'   => $label,
                'total'   => (int) ($countsByService[$code] ?? 0),
            ];
        })->values();

        $visaTotal = $visaApplications->sum('total');

        return [
            'maidReturnCat1_count'   => $maidReturnCat1Count,
            'returnedMaid_count'     => $returnedMaidCount,
            'release_count'          => $releaseCount,
            'arrival_count'          => $arrivalCount,
            'typing_count'           => $typing,
            'categoryOne_counts'     => $categoryOneCounts,
            'category4Model_counts'  => $category4ModelCounts,
            'p1Count'                => $p1Count,
            'p4Count'                => $p4Count,
            'relase'                 => $groupByRelased,
            'cash'                   => $cashReport,
            'cash_out'               => $creditCashReport,
            'closing_balance'        => $closingBalances,
            'visa_applications'      => $visaApplications,
            'visa_total'             => $visaTotal,
            'p4AsNewContracts' => $p4AsNewContracts,
            'p5Count' => $p5Count,
            'p4AllActive' => $p4AllActive,
            'p5AllActive' => $p5AllActive,
        ];
    }

    public function getDailyOneClickReportData(CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        // Top counters - Use returned_date for Returns
        $maidReturnCat1Count = maidReturnCat1::whereBetween('returned_date', [$startDate, $endDate])->count();
        $returnedMaidCount   = ReturnedMaid::whereBetween('returned_date', [$startDate, $endDate])->count();

        // Others use created_at as before
        $releaseCount        = release::whereBetween('created_at', [$startDate, $endDate])->count();
        $arrivalCount        = Arrival::whereBetween('created_at', [$startDate, $endDate])->count();
        $p1Count             = categoryOne::whereBetween('created_at', [$startDate, $endDate])->count();
        $p4Count             = Category4Model::whereBetween('created_at', [$startDate, $endDate])->count();

        $typing = General_journal_voucher::where('voucher_type', 'Typing Invoice')
            ->where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $p4AsNewContracts = Category4Model::where('Contract_ref', 'like', 'P4_%')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'HC');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $p5Count = Category4Model::with('maidInfo')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'Direct hire');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $p4AllActive = Category4Model::where('contract_status', '1')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'HC');
            })
            ->count();

        $p5AllActive = Category4Model::where('contract_status', '1')
            ->whereHas('maidInfo', function ($query) {
                $query->where('maid_type', 'Direct hire');
            })
            ->count();

        // By creator
        $categoryOneCounts = categoryOne::select('created_by', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        $category4ModelCounts = Category4Model::select('created_by', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        // Release breakdown
        $groupByRelased = release::select('new_status', DB::raw('COUNT(*) AS total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('new_status')
            ->get();

        // Cash received (debit)
        $cashReport = All_account_ledger_DB::select('all_account_ledger__d_b_s.ledger', DB::raw('SUM(gjv.amount) AS total_received'))
            ->join('general_journal_vouchers AS gjv', 'all_account_ledger__d_b_s.id', '=', 'gjv.ledger_id')
            ->where('all_account_ledger__d_b_s.group', 'cash equivalent')
            ->where('gjv.type', 'debit')
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('all_account_ledger__d_b_s.ledger')
            ->get();

        // Cash paid (credit)
        $creditCashReport = All_account_ledger_DB::select('all_account_ledger__d_b_s.ledger', DB::raw('SUM(gjv.amount) AS total_paid'))
            ->join('general_journal_vouchers AS gjv', 'all_account_ledger__d_b_s.id', '=', 'gjv.ledger_id')
            ->where('all_account_ledger__d_b_s.group', 'cash equivalent')
            ->where('gjv.type', 'credit')
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('all_account_ledger__d_b_s.ledger')
            ->get();

        // Closing balances
        $ledgers = All_account_ledger_DB::select('id', 'ledger')
            ->where('group', 'cash equivalent')
            ->distinct()
            ->get();

        $closingBalances = $ledgers->map(function ($ledger) {
            return [
                'ledger'           => $ledger->ledger,
                'closing_balance'  => General_journal_voucher::calculateCustomerBalanceByLedgerId($ledger->id),
            ];
        })->values();

        // Visa applications
        $serviceLabels = [
            0 => 'visa_renewal',
            2 => 'new_visa',
            3 => 'cancellation',
            4 => 'absconding',
            5 => 'other',
        ];

        $countsByService = ApplyVisa::where('status', '!=', 11)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('service', DB::raw('COUNT(*) AS total'))
            ->groupBy('service')
            ->pluck('total', 'service');

        $visaApplications = collect($serviceLabels)->map(function ($label, $code) use ($countsByService) {
            return [
                'service' => $code,
                'label'   => $label,
                'total'   => (int) ($countsByService[$code] ?? 0),
            ];
        })->values();

        $visaTotal = $visaApplications->sum('total');

        return [
            'maidReturnCat1_count'   => $maidReturnCat1Count,
            'returnedMaid_count'     => $returnedMaidCount,
            'release_count'          => $releaseCount,
            'arrival_count'          => $arrivalCount,
            'typing_count'           => $typing,
            'categoryOne_counts'     => $categoryOneCounts,
            'category4Model_counts'  => $category4ModelCounts,
            'p1Count'                => $p1Count,
            'p4Count'                => $p4Count,
            'relase'                 => $groupByRelased,
            'cash'                   => $cashReport,
            'cash_out'               => $creditCashReport,
            'closing_balance'        => $closingBalances,
            'visa_applications'      => $visaApplications,
            'visa_total'             => $visaTotal,
            'p4AsNewContracts' => $p4AsNewContracts,
            'p5Count' => $p5Count,
            'p4AllActive' => $p4AllActive,
            'p5AllActive' => $p5AllActive,
        ];
    }

    public function getComparativeTrial3EndsData(CarbonInterface $asOf): array
    {
        $asOf = $asOf->endOfDay();

        // Exclude current month: use last 3 CLOSED month-ends
        $m3 = $asOf->subMonthNoOverflow()->endOfMonth(); // latest closed
        $m2 = $m3->subMonthNoOverflow()->endOfMonth();
        $m1 = $m2->subMonthNoOverflow()->endOfMonth();

        $d1 = $m1->toDateString();
        $d2 = $m2->toDateString();
        $d3 = $m3->toDateString();

        // Query (debit=+, credit=-) as-of each month end
        $rows = DB::table('general_journal_vouchers as jv')
            ->join('all_account_ledger__d_b_s as ld', 'ld.id', '=', 'jv.ledger_id')
            ->selectRaw("
                COALESCE(ld.class, '(No Class)')  AS ledger_class,
                COALESCE(ld.group, '(No Group)')  AS ledger_group,

                SUM(CASE WHEN jv.date <= ? THEN
                         CASE WHEN jv.type='debit' THEN jv.amount
                              WHEN jv.type='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS bal_m1,

                SUM(CASE WHEN jv.date <= ? THEN
                         CASE WHEN jv.type='debit' THEN jv.amount
                              WHEN jv.type='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS bal_m2,

                SUM(CASE WHEN jv.date <= ? THEN
                         CASE WHEN jv.type='debit' THEN jv.amount
                              WHEN jv.type='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS bal_m3
            ", [$d1, $d2, $d3])
            ->groupByRaw('1, 2')
            ->orderBy('ledger_class')
            ->orderBy('ledger_group')
            ->get()
            ->map(function ($r) {
                $r->bal_m1 = (float)$r->bal_m1; // earliest
                $r->bal_m2 = (float)$r->bal_m2;
                $r->bal_m3 = (float)$r->bal_m3; // latest
                $r->change_amount = $r->bal_m3 - $r->bal_m1; // latest - earliest
                return $r;
            });

        // Group into classes with subtotals
        $classes = [];
        foreach ($rows->groupBy('ledger_class') as $className => $items) {
            $classes[$className] = [
                'rows' => $items->values()->map(fn($r) => [
                    'ledger_group'  => $r->ledger_group,
                    'bal_m1'        => $r->bal_m1,
                    'bal_m2'        => $r->bal_m2,
                    'bal_m3'        => $r->bal_m3,
                    'change_amount' => $r->change_amount,
                ]),
                'subtotal_m1'   => (float)$items->sum('bal_m1'),
                'subtotal_m2'   => (float)$items->sum('bal_m2'),
                'subtotal_m3'   => (float)$items->sum('bal_m3'),
                'subtotal_change'=> (float)$items->sum('change_amount'),
            ];
        }

        $totals = [
            'm1'     => (float)$rows->sum('bal_m1'),
            'm2'     => (float)$rows->sum('bal_m2'),
            'm3'     => (float)$rows->sum('bal_m3'),
            'change' => (float)$rows->sum('change_amount'),
        ];

        $meta = [
            'asof' => $asOf->toDateString(),
            'col1' => ['key'=>'bal_m1','label'=>$m1->format('M Y'),'date'=>$d1],
            'col2' => ['key'=>'bal_m2','label'=>$m2->format('M Y'),'date'=>$d2],
            'col3' => ['key'=>'bal_m3','label'=>$m3->format('M Y'),'date'=>$d3],
            'col4' => ['key'=>'change_amount','label'=>"Change ({$m3->format('M')} − {$m1->format('M')})"],
        ];

        return [
            'classes' => $classes,
            'totals'  => $totals,
            'meta'    => $meta,
        ];
    }

    public function getIncomeStatement3MonthsData(CarbonInterface $asOf): array
    {
        $asOf = $asOf->endOfDay();

        // Use the last 3 CLOSED months (exclude current month)
        $m3 = $asOf->copy()->subMonthNoOverflow()->endOfMonth(); // latest closed
        $m2 = $m3->copy()->subMonthNoOverflow()->endOfMonth();
        $m1 = $m2->copy()->subMonthNoOverflow()->endOfMonth();

        // Period boundaries for each month (activity)
        $m1_start = $m1->copy()->startOfMonth()->toDateString();
        $m1_end   = $m1->toDateString();
        $m2_start = $m2->copy()->startOfMonth()->toDateString();
        $m2_end   = $m2->toDateString();
        $m3_start = $m3->copy()->startOfMonth()->toDateString();
        $m3_end   = $m3->toDateString();

        // Query monthly activity for only Revenue & Expenses, grouped by Group
        $incomeRows = DB::table('general_journal_vouchers as jv')
            ->join('all_account_ledger__d_b_s as ld', 'ld.id', '=', 'jv.ledger_id')
            ->whereIn(DB::raw('LOWER(ld.class)'), ['revenue', 'expenses'])
            ->selectRaw("
                COALESCE(ld.class, '(No Class)')  AS ledger_class,
                COALESCE(ld.`group`, '(No Group)')  AS ledger_group,

                SUM(CASE WHEN jv.`date` BETWEEN ? AND ? THEN
                         CASE WHEN jv.`type`='debit' THEN jv.amount
                              WHEN jv.`type`='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS m1,

                SUM(CASE WHEN jv.`date` BETWEEN ? AND ? THEN
                         CASE WHEN jv.`type`='debit' THEN jv.amount
                              WHEN jv.`type`='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS m2,

                SUM(CASE WHEN jv.`date` BETWEEN ? AND ? THEN
                         CASE WHEN jv.`type`='debit' THEN jv.amount
                              WHEN jv.`type`='credit' THEN -jv.amount
                              ELSE 0 END
                    ELSE 0 END) AS m3
            ", [$m1_start, $m1_end, $m2_start, $m2_end, $m3_start, $m3_end])
            ->groupByRaw('1, 2')
            ->orderBy('ledger_class')
            ->orderBy('ledger_group')
            ->get()
            ->map(function ($r) {
                $isRevenue = strcasecmp($r->ledger_class, 'Revenue') === 0;
                $m1 = (float)$r->m1 * ($isRevenue ? -1 : 1);
                $m2 = (float)$r->m2 * ($isRevenue ? -1 : 1);
                $m3 = (float)$r->m3 * ($isRevenue ? -1 : 1);

                return (object)[
                    'ledger_class'  => $r->ledger_class,
                    'ledger_group'  => $r->ledger_group,
                    'm1'            => $m1,
                    'm2'            => $m2,
                    'm3'            => $m3,
                    'change_amount' => $m3 - $m1,
                ];
            });

        // Build class-level groupings and subtotals
        $incomeClasses = [];
        foreach ($incomeRows->groupBy('ledger_class') as $className => $items) {
            $incomeClasses[$className] = [
                'rows' => $items->values()->map(fn($r) => [
                    'ledger_group'  => $r->ledger_group,
                    'm1'            => $r->m1,
                    'm2'            => $r->m2,
                    'm3'            => $r->m3,
                    'change_amount' => $r->change_amount,
                ]),
                'subtotal_m1'     => (float)$items->sum('m1'),
                'subtotal_m2'     => (float)$items->sum('m2'),
                'subtotal_m3'     => (float)$items->sum('m3'),
                'subtotal_change' => (float)$items->sum('change_amount'),
            ];
        }

        // Totals and Net Income (Revenue − Expenses)
        $rev1 = $incomeClasses['Revenue']['subtotal_m1'] ?? 0.0;
        $rev2 = $incomeClasses['Revenue']['subtotal_m2'] ?? 0.0;
        $rev3 = $incomeClasses['Revenue']['subtotal_m3'] ?? 0.0;

        $exp1 = $incomeClasses['Expenses']['subtotal_m1'] ?? 0.0;
        $exp2 = $incomeClasses['Expenses']['subtotal_m2'] ?? 0.0;
        $exp3 = $incomeClasses['Expenses']['subtotal_m3'] ?? 0.0;

        $incomeTotals = [
            'revenue' => ['m1' => $rev1, 'm2' => $rev2, 'm3' => $rev3, 'change' => $rev3 - $rev1],
            'expenses' => ['m1' => $exp1, 'm2' => $exp2, 'm3' => $exp3, 'change' => $exp3 - $exp1],
            'net'     => [
                'm1' => $rev1 - $exp1,
                'm2' => $rev2 - $exp2,
                'm3' => $rev3 - $exp3,
                'change' => ($rev3 - $exp3) - ($rev1 - $exp1),
            ],
        ];

        $meta = [
            'asof'    => $asOf->toDateString(),
            'col1'    => ['key' => 'm1', 'label' => $m1->format('M Y'), 'start' => $m1_start, 'end' => $m1_end],
            'col2'    => ['key' => 'm2', 'label' => $m2->format('M Y'), 'start' => $m2_start, 'end' => $m2_end],
            'col3'    => ['key' => 'm3', 'label' => $m3->format('M Y'), 'start' => $m3_start, 'end' => $m3_end],
            'col4'    => ['key' => 'change_amount', 'label' => "Change ({$m3->format('M')} − {$m1->format('M')})"],
            'heading' => "Income Statement — {$m1->format('M Y')} / {$m2->format('M Y')} / {$m3->format('M Y')}",
        ];

        return [
            'incomeClasses' => $incomeClasses,
            'incomeTotals'  => $incomeTotals,
            'meta'          => $meta,
        ];
    }

    public function getRecipients(string $reportType = 'all'): array
    {
        return ReportRecipient::where('is_active', true)
            ->where(function ($q) use ($reportType) {
                $q->where('report_type', 'all')
                  ->orWhere('report_type', $reportType);
            })
            ->pluck('email')
            ->toArray();
    }
}
