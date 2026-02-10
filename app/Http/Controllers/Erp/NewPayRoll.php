<?php

namespace App\Http\Controllers\Erp;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Log;
use App\Models\PayMaidPayroll;
use Carbon\Carbon;
use Auth;


class NewPayRoll extends Controller
{
    public function index()
    {
        return view('payroll.index'); 
    }



    public function getPayRoll(Request $request)
    {
        $startDate          = $request->input('start_date');
        $endDate            = $request->input('end_date');
        $maidStatus         = $request->input('maid_status');
        $maidType           = $request->input('maid_type');
        $maidPayment        = $request->input('maid_payment');
        $workingDaysFilter  = $request->input('working_days_filter');
        $paymentStatus      = $request->input('payment_status');
        $branch             = $request->input('branch');

        $filterNoNoteNoBooked = $request->input('filter_no_note_no_booked');
        $filterBooked         = $request->input('filter_booked');
        $filterNote           = $request->input('filter_note');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Please provide both start and end dates.'], 400);
        }

        // ----- Subqueries (by maid_id) ---------------------------------------------------------
        $paidSub = DB::table('pay_maid_payrolls')
            ->selectRaw('DISTINCT maid_id, created_at, created_by, note')
            ->whereBetween('accrued_month', [$startDate, $endDate]);

        $noteSub = DB::table('advance_and_dedcutiot_maids')
            ->selectRaw('DISTINCT maid_id, note, Allowance, deduction, id')
            ->whereBetween('date', [$startDate, $endDate]);

        // ----- Main aggregation subquery -------------------------------------------------------
        $subQuery = DB::table('category4_models as c')
            ->leftJoin('returned_maids as r', 'c.Contract_ref', '=', 'r.contract')
            ->leftJoin('customers as cu', 'c.customer_id', '=', 'cu.id')
            ->leftJoin('maids_d_b_s as m', 'c.maid_id', '=', 'm.id')
            ->leftJoinSub($paidSub, 'paid', function ($join) {
                $join->on('m.id', '=', 'paid.maid_id');
            })
            ->leftJoinSub($noteSub, 'note', function ($join) {
                $join->on('m.id', '=', 'note.maid_id');
            })
            ->select([
                DB::raw('MAX(m.id) as maid_id'),
                DB::raw('MAX(m.name) as maid'),
                DB::raw('MAX(c.date) as latest_date'),
                DB::raw('MAX(c.Contract_ref) as latest_contract'),
                DB::raw('MAX(cu.name) as latest_customer'),
                DB::raw('MAX(m.maid_status) as latest_maid_status'),
                DB::raw('MAX(m.maid_type) as latest_maid_type'),
                DB::raw('MAX(m.payment) as payment'),
                DB::raw('MAX(m.salary) as salary'),
                DB::raw('MAX(m.maid_booked) as maid_booked'),
                DB::raw('MAX(m.moi) as maid_moi'),
                DB::raw('MAX(m.branch) as maid_branch'),
                DB::raw('MAX(m.nationality) as nationality'),
                DB::raw('MAX(m.start_as_p4) as start'),
                DB::raw('MAX(r.returned_date) as latest_returned_date'),
                DB::raw('MAX(paid.maid_id) as paid'),
                DB::raw('MAX(paid.created_by) as paid_by'),
                DB::raw('MAX(paid.created_at) as paid_at'),
                DB::raw('MAX(paid.note) as paid_note'),
                DB::raw('MAX(note.note) as note'),
                DB::raw('MAX(note.Allowance) as Allowance'),
                DB::raw('MAX(note.deduction) as deduction'),
                DB::raw('MAX(note.id) as idForDeduction'),
            ])
            ->selectRaw("
                SUM(
                    GREATEST(
                        DATEDIFF(
                            LEAST(COALESCE(r.returned_date, ?), ?),
                            CASE 
                                WHEN c.date < ? THEN ?
                                ELSE c.date
                            END
                        ), 0
                    )
                ) AS total_days_difference
            ", [$endDate, $endDate, $startDate, $startDate])
            ->whereIn('m.maid_type', ['HC', 'direct hire'])
            ->whereIn('m.maid_status', ['approved', 'hired'])
            ->where(function ($q) use ($startDate) {
                $q->whereNull('r.returned_date')
                  ->orWhere('r.returned_date', '>=', $startDate);
            })
            ->groupBy('m.id');

        // ----- Wrap safely to preserve bindings ------------------------------------------------
        $query = DB::query()->fromSub($subQuery, 'grouped_data')->select('*');

        // ----- Post-aggregation filters --------------------------------------------------------
        if ($maidStatus) {
            $query->where('latest_maid_status', $maidStatus);
        }
        if ($maidType) {
            $query->where('latest_maid_type', $maidType);
        }
        if ($maidPayment) {
            $query->where('payment', $maidPayment);
        }

        if ($workingDaysFilter === 'more_than_25') {
            $query->where('total_days_difference', '>=', 25);
        } elseif ($workingDaysFilter === 'less_than_25') {
            $query->where('total_days_difference', '<', 25);
        }

        if ($paymentStatus === 'paid') {
            $query->whereNotNull('paid');
        } elseif ($paymentStatus === 'unpaid') {
            $query->whereNull('paid');
        }

        if ($filterNoNoteNoBooked) {
            $query->whereNull('note')->whereNull('maid_booked');
        }
        if ($filterBooked) {
            $query->whereNotNull('maid_booked');
        }
        if ($filterNote) {
            $query->whereNotNull('note');
        }

        if ($branch) {
            $query->where('maid_branch', $branch);
        }

        // ----- DataTables response -------------------------------------------------------------
        return DataTables::of($query)->make(true);
    }

// url /store-payroll
public function newbulkPaid(Request $request)
{
    $maidsData = $request->input('maids', []);

    if (empty($maidsData) || !is_array($maidsData)) {
        return response()->json(['error' => 'No maid data received'], 400);
    }

    DB::beginTransaction();

    try {
        foreach ($maidsData as $maid) {
            // Require maid_id and date
            if (!isset($maid['maid_id'], $maid['date'])) {
                continue;
            }

            // Parse and normalize accrued month (stored as Y-m-d)
            try {
                $accruedMonth = Carbon::parse($maid['date'])->format('Y-m-d');
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['error' => 'Invalid date format'], 400);
            }

            $maidId     = (int) ($maid['maid_id']);
            $totalDays  = isset($maid['totalDays']) ? (int) $maid['totalDays'] : 0;
            $deduction  = isset($maid['deduction']) ? (float) $maid['deduction'] : 0.0;
            $allowance  = isset($maid['allowance']) ? (float) $maid['allowance'] : 0.0;
            $note       = $maid['note'] ?? 'No Note';
            $basic      = isset($maid['salary']) ? (float) $maid['salary'] : 0.0;
            $maidType   = $maid['type'] ?? 'Unknown';
            $netSalary  = isset($maid['net']) ? (float) $maid['net'] : 0.0;
            $method     = strtolower($maid['method'] ?? 'cash');     
            $status     = $maid['status'] ?? 'Pending';
            $createdBy  = Auth::user()->name ?? 'System';

          
            $existing = PayMaidPayroll::where('maid_id', $maidId)
                ->where('accrued_month', $accruedMonth)
                ->lockForUpdate()
                ->first();

            if (!$existing) {
                PayMaidPayroll::create([
                    'maid_id'       => $maidId,
                    'accrued_month' => $accruedMonth,
                    'working_dayes' => $totalDays,
                    'deduction'     => $deduction,
                    'allowance'     => $allowance,
                    'note'          => $note,
                    'basic'         => $basic,
                    'maid_type'     => $maidType,
                    'net_salary'    => $netSalary,
                    'method'        => $method,
                    'status'        => $status,
                    'created_by'    => $createdBy,
                ]);
            }
        }

        DB::commit();
        return response()->json(['message' => 'Payrolls generated successfully'], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'error'   => 'Something went wrong',
            'details' => $e->getMessage(),
        ], 500);
    }
}
       

}    
