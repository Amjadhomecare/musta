<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category4Model;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use App\Models\AdvanceAndDedcutiotMaids;
use App\Models\PayMaidPayroll;
use App\Models\General_journal_voucher;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class maidsPayrollCntl extends Controller
{

    public function selectPayrollMonthCntl(){


        return view('ERP.cat4.select_month');
    }//END method

    public function calculateWorkingDays($maidName, $month, $year)
    {
        // Get the start and end dates for the requested month
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->toDateString();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    
        // SQL query with placeholders
        $query = "
            SELECT SUM(
                DATEDIFF(
                    LEAST(IFNULL(r.returned_date, ?), ?),
                    GREATEST(c.date, ?)
                ) + 1
            ) AS working_days
            FROM category4_models c
            LEFT JOIN returned_maids r
            ON c.Contract_ref = r.contract AND c.maid = r.maid_return_name
            WHERE c.maid = ?
            AND c.date <= ?
            AND (
                r.returned_date IS NULL OR r.returned_date >= ?
            )
        ";
    
        // Execute the query with dynamic values
        $result = \DB::select($query, [
            $endOfMonth,    // For LEAST(IFNULL(r.returned_date, ?), ?)
            $endOfMonth,    // For LEAST(IFNULL(r.returned_date, ?), ?)
            $startOfMonth,  // For GREATEST(c.date, ?)
            $maidName,      // For WHERE c.maid = ?
            $endOfMonth,    // For WHERE c.date <= ?
            $startOfMonth   // For AND (r.returned_date IS NULL OR r.returned_date >= ?)
        ]);
    
        // Return the working days or 0 if no rows match
        return $result[0]->working_days ?? 0;
    }
    
    

public function getMaidsSalariesPayRollsForCat4MaidsCntl(Request $request)
{
    $year = $request->year;
    $month = $request->month;

    $maidsData = MaidsDB::whereIn('maid_status', ['approved', 'hired'])
        ->whereIn('maid_type', ['HC', 'direct hire'])
        ->get();

    $contracts = Category4Model::with('maidInfo')
        ->whereIn('maid', $maidsData->pluck('name'))
        ->where(function ($query) use ($year, $month) {
            $startOfMonth = Carbon::create($year, $month, 1);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                  ->orWhere('date', '<', $startOfMonth);
        })
        ->get()
        ->groupBy('maid');

    $payrolls = PayMaidPayroll::whereMonth('accrued_month', $month)
        ->whereYear('accrued_month', $year)
        ->whereIn('maid', $maidsData->pluck('name'))
        ->pluck('maid');

    $maidDataFormatted = [];

    foreach ($maidsData as $maid) {
        $maidName = $maid->name;
        $totalDays = $this->calculateWorkingDays($maidName, $month, $year);

        $maidDeduction = AdvanceAndDedcutiotMaids::getNoteDeductionAllowanceByMaidMonthYear($maidName, $month, $year);
        $latestContractRef = $contracts->get($maidName)?->last();

        $isPaid = $payrolls->contains($maidName);

        $maidDataFormatted[] = [
            'name' => $maidName,
            'salary' => $maid->salary,
            'type' => $maid->maid_type,
            'book' => $maid->maid_booked,
            'id' => $maid->id,
            'totalDays' => $totalDays,
            'customer' => $latestContractRef->customer ?? 'No customer',
            'deduction' => $maidDeduction->deduction ?? 0,
            'allowance' => $maidDeduction->Allowance ?? 0,
            'note' => $maidDeduction->note ?? '',
            'idForDeduction' => $maidDeduction->id ?? '',
            'contract_ref' => $latestContractRef->Contract_ref ?? 'No ref',
            'maid_status' => $maid->maid_status,
            'payment' => $maid->payment ?? 'not assigned',
            'is_paid' => $isPaid ? 'Paid' : 'Unpaid',
        ];
    }

    if ($request->ajax()) {
        return DataTables::of(collect($maidDataFormatted))->make(true);
    }

    return view('ERP.cat4.maids_payroll', compact('month', 'year'));
}



    public function viewFormAdvanceAndDeductionCntl(){
       
        $maidsData = MaidsDB::whereIn('maid_type', ['HC'])->get();

        return view('ERP.cat4.advanceAndDeduction',compact('maidsData'));
    }//End Method

    
public function storeAdvanceOrDeductionCntl(Request $request)
{
    $validated = $request->validate([
        'maid'       => 'required|string',
        'note'       => 'required|string',
        'deduction'  => 'nullable|numeric|min:0',
        'allowance'  => 'nullable|numeric|min:0',
        'date'       => 'required', 
    ]);



     $date = $validated['date'] . '-01';

    $maidId = DB::table('maids_d_b_s')
        ->where('name', $validated['maid'])
        ->value('id');

    if (!$maidId) {
        return back()->with([
            'message'    => 'Maid not found.',
            'alert-type' => 'error',
        ]);
    }

    $existingRecord = AdvanceAndDedcutiotMaids::where('maid_id', $maidId)
        ->whereYear('date', Carbon::parse($date)->year)
        ->whereMonth('date', Carbon::parse($date)->month)
        ->first();

    if ($existingRecord) {
        return back()->with([
            'message'    => 'Record for this maid already exists in the selected month.',
            'alert-type' => 'error',
        ]);
    }

    // 5) Insert (use maid_id, not the name)
    DB::table('advance_and_dedcutiot_maids')->insert([
        'date'       => $date,
        'maid_id'    => $maidId,
        'note'       => $validated['note'],
        'deduction'  => $validated['deduction'] ?? 0,
        'Allowance'  => $validated['allowance'] ?? 0, 
        'created_by' => Auth::user()->name ?? 'system',
        'updated_by' => Auth::user()->name ?? 'system',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with([
        'message'    => 'Added Successfully',
        'alert-type' => 'success',
    ]);
}


// url /store-new

public function storeAdvanceAndDeductionCntl(Request $request)
{
    try {
        $validated = $request->validate([
            'maid_id'       => 'required|integer|exists:maids_d_b_s,id',
            'noteMaid'      => 'nullable|string',
            'allowanceMaid' => 'nullable|numeric',
            'deductionMaid' => 'nullable|numeric',
            'month'         => 'required|date_format:Y-m', 
        ]);



        $date = $validated['month'] . '-01';

        // uniqueness per maid_id + month
        $existingRecord = AdvanceAndDedcutiotMaids::where('maid_id', $validated['maid_id'])
            ->whereYear('date', Carbon::parse($date)->year)
            ->whereMonth('date', Carbon::parse($date)->month)
            ->first();

        if ($existingRecord) {
            return response()->json([
                'error' => 'Record for this maid already exists in the selected month.'
            ], 422);
        }

        $advance = AdvanceAndDedcutiotMaids::create([
            'maid_id'    => $validated['maid_id'],
            'note'       => $validated['noteMaid'] ?? null,
            'Allowance'  => $validated['allowanceMaid'] ?? 0,
            'deduction'  => $validated['deductionMaid'] ?? 0,
            'date'       => $date,
            'created_by' => Auth::user()->name ?? 'System',
            'updated_by' => Auth::user()->name ?? 'System',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Record added successfully',
            'data'    => $advance
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Create failed: ' . $e->getMessage()
        ], 500);
    }
}

// url /update-advance
public function updateAdvanceAndDeductionCntl(Request $request)
{
    try {
        $validated = $request->validate([
            'advanceDataId' => 'required|integer|exists:advance_and_dedcutiot_maids,id',
            'noteMaid'      => 'nullable|string',
            'allowanceMaid' => 'nullable|numeric',
            'deductionMaid' => 'nullable|numeric',
        ]);

        $advance = AdvanceAndDedcutiotMaids::findOrFail($validated['advanceDataId']);

        $advance->note        = $validated['noteMaid'] ?? null;
        $advance->Allowance   = $validated['allowanceMaid'] ?? 0;
        $advance->deduction   = $validated['deductionMaid'] ?? 0;
        $advance->updated_at  = Carbon::now();
        $advance->updated_by  = Auth::user()->name ?? 'System';
        $advance->save();

        return response()->json(['message' => 'Updated successfully', 'data' => $advance]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
    }
}

      

    public function bulkPaid(Request $request)
    {
        $maidsData = json_decode($request->getContent(), true)['maids'];
        
        $year = $maidsData[0]['year'] ?? Carbon::now()->year; 
        $month = $maidsData[0]['month'] ?? Carbon::now()->month;
        

        $cacheKey = "maids_salaries_{$year}_{$month}";
        

        Cache::forget($cacheKey);
    

        foreach ($maidsData as $maid) {
            $existingPayroll = PayMaidPayroll::where('maid', $maid['name'])
                                             ->where('accrued_month', $maid['date'])
                                             ->first();
            
            if (!$existingPayroll) {  
                PayMaidPayroll::create([
                    'maid' => $maid['name'],
                    'accrued_month' => $maid['date'],
                    'working_dayes' => $maid['totalDays'],
                    'deduction' => $maid['deduction'] ?? 0,
                    'allowance' => $maid['allowance'] ?? 0,
                    'note' => $maid['note'] ?? "No Note",
                    'basic' => $maid['salary'],
                    'maid_type' => $maid['type'],
                    'net_salary' => $maid['net'],
                    'method' => $maid['method'],
                    'status' => $maid['status'],
                    'created_by' => Auth::user()->name,
                ]);
            }
        }
    
        return response()->json(['message' => 'Payrolls Generated successfully']);
    }
       


    public function viwePaidMaids (){

        return view('ERP.cat4.payroll.paids_maids');
    }


    public function ajaxAllPaidMaidPayroll(Request $request)
  {
    try {
        $query = DB::table('pay_maid_payrolls')
            ->leftJoin('maids_d_b_s', 'pay_maid_payrolls.maid_id', '=', 'maids_d_b_s.id')
            ->select(
                'pay_maid_payrolls.*',
                'maids_d_b_s.name as maid',
                'maids_d_b_s.moi as maid_moi',
                'maids_d_b_s.branch as maid_branch'
            );

       
        if ($request->filled('min_date')) {
            $query->whereDate('pay_maid_payrolls.accrued_month', '>=', $request->min_date);
        }

        if ($request->filled('max_date')) {
            $query->whereDate('pay_maid_payrolls.accrued_month', '<=', $request->max_date);
        }

        return DataTables::of($query)
            ->addIndexColumn()

            // Fix search on aliased column maid_moi
            ->filterColumn('maid_moi', function ($query, $keyword) {
                $query->where('maids_d_b_s.moi', 'like', "%{$keyword}%");
            })

           ->filterColumn('maid', function ($query, $keyword) {
                $query->where('maids_d_b_s.name', 'like', "%{$keyword}%");
            })
            
            ->filterColumn('maid_branch', function ($query, $keyword) {
                $query->where('maids_d_b_s.branch', 'like', "%{$keyword}%");
            })

            ->addColumn('delete', function ($row) {
                $user = auth()->user();
                if ($user && $user->group === 'accounting') {
                    return '<button class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '">Delete</button>';
                }
                return '';
            })

            ->rawColumns(['delete'])
            ->make(true);

    } catch (\Exception $e) {
        logger()->error('ajaxAllPaidMaidPayroll Error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Something went wrong!'], 500);
    }
}


   /**
     * Delete a paid maid payroll
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * url: /delete-payroll/{id}
     */
    public function deletePaidMaidPayroll($id)
    {
        try {
            $payroll = PayMaidPayroll::findOrFail($id);
            $payroll->delete();
    
            return response()->json(['message' => 'Deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }


public function dataTableAdvancePayroll(Request $request)
{
    try {
        $qb = DB::table('advance_and_dedcutiot_maids as adm')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'adm.maid_id')
            ->select([
                'adm.id',
                'adm.date',
                'adm.note',
                'adm.deduction',
                'adm.Allowance',
                'adm.created_by',
                'adm.updated_by',
                'adm.created_at',
                'adm.updated_at',
                'adm.maid_id',
                DB::raw('m.name as maid_name'),
            ]);

        if ($request->filled('min_date')) {
            $qb->whereDate('adm.created_at', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $qb->whereDate('adm.created_at', '<=', $request->max_date);
        }

        return DataTables::of($qb) // ✅ works on all versions
            ->addIndexColumn()

            ->editColumn('date', function ($row) {
                return $row->date ? Carbon::parse($row->date)->format('F Y') : '';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : '';
            })
            ->editColumn('updated_at', function ($row) {
                return $row->updated_at ? Carbon::parse($row->updated_at)->format('Y-m-d') : '';
            })

            // Build the linked maid name from maid_id + maid_name
            ->addColumn('maid', function ($row) {
                $name = e($row->maid_name ?? '-');
                if (!$row->maid_id || $name === '-') return $name;
                $url = url("/payroll/history/{$row->maid_name}");
                return "<a href=\"{$url}\" target=\"_blank\">{$name}</a>";
            })

            // Map search on 'maid' to m.name
            ->filterColumn('maid', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })

            // Map ordering on 'maid' to m.name
            ->orderColumn('maid', 'm.name $1')

            ->rawColumns(['maid'])
            ->make(true);

    } catch (\Throwable $e) {
        report($e);
        return response()->json(['error' => 'Something went wrong!'], 500);
    }
}


}







