<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\General_journal_voucher;
use App\Models\maidReturnCat1;
use App\Models\ReturnedMaid;
use App\Models\release;
use App\Models\Arrival;
use App\Models\categoryOne;
use App\Models\Category4Model;
use App\Models\logsdt;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\ApplyVisa;


class report extends Controller
{
    
    public function fetch_report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
    
        $data = MaidReturnCat1::whereBetween('returned_date', [$request->start_date, $request->end_date])->get();
        $count = $data->count();
    
        return response()->json([
            'message' => 'success',
            'data' => $data,
            'count' => $count
        ]);
    }
    
    public function fetch_cash_equivalent(Request $request){
 
        $data = All_account_ledger_DB::where('group', 'cash equivalent')->get();
    

        $result = $data->map(function($item) {
            if (isset($item->ledger)) {
      
                $balance = General_journal_voucher::calculateCustomerClosingBalance($item->ledger);
            
                return [
                    'item' => $item->ledger,
                    'balance' => $balance
                ];
            }
           
            return null;
        })->filter(); 
        return response()->json([
            'message' => 'success',
            'data' => $result
        ], 200);
    }
    private function report_for_past_three_months(string $column, string $value, string $type)
    {
        $endDate = Carbon::now()->startOfMonth();
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();

        $monthlyAmounts = General_journal_voucher::select(
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('DATE_FORMAT(date, "%Y-%m") as month')
        )
        ->where($column, $value)
        ->where('type', $type)
        ->whereBetween('date', [$startDate, $endDate])
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

        $result = $monthlyAmounts->map(function ($item) {
            return [
                'month' => $item->month,
                'amount' => $item->total_amount
            ];
        });

        return response()->json([
            'message' => 'success',
            'data' => $result
        ], 200);
    }

    public function income_last_three_months_typing()
    {
      return $this->report_for_past_three_months('voucher_type', 'Typing Invoice', 'debit');
    }

    public function income_last_three_months_package1()
    {
      return $this->report_for_past_three_months('voucher_type', 'Invoice Package1', 'debit');
    }

    public function income_last_three_months_package4()
    {
      return $this->report_for_past_three_months('voucher_type', 'Invoice Package4', 'debit');
    }

// URL /page-report
    public function pageReport(){

        return view('ERP.owner.report');
    }
    
    // url = /onclick-report
public function oneClickReport(Request $request, \App\Services\ReportService $reportService)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date'   => 'required|date',
    ]);

    try {
        $startDate = \Carbon\Carbon::parse($request->input('start_date'))
            ->setTimezone('Asia/Dubai')->startOfDay();
        $endDate = \Carbon\Carbon::parse($request->input('end_date'))
            ->setTimezone('Asia/Dubai')->endOfDay();
    } catch (\Throwable $e) {
        \Log::error('oneClickReport date parse error: '.$e->getMessage());
        return response()->json(['error' => 'Invalid date format provided.'], 400);
    }

    $data = $reportService->getOneClickReportData($startDate, $endDate);

    return response()->json($data);
}

 
    public function pageDynamicReport(){

        return view('ERP.owner.dynamic_report');
    }

    // /log-book
    public function logChecking(){

        return view('ERP.owner.logbook');
    }

    // table-log
    public function tableLog(Request $request){
              
        if ($request->ajax()) {
            $data = logsdt::orderBy('created_at', 'desc')->latest();
            return DataTables::of($data)
                    ->addIndexColumn()
                    ->make(true);
        }
    
    }


  
  //url /table-wrost-p4
public function tableWorsMaidsLastMonth(Request $request)
{
    $sql = "
    SELECT 
        m.name,
        m.nationality,
        COUNT(r.maid_id) AS record_count
    FROM 
        maids_d_b_s m
    LEFT JOIN 
        returned_maids r ON m.id = r.maid_id
    WHERE 
        m.maid_status = 'approved' AND
        m.maid_type = 'HC' AND
        r.returned_date >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m-01') AND 
        r.returned_date < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
    GROUP BY 
        m.name, m.nationality
    HAVING 
        COUNT(r.maid_id) > 1;
    ";

    $query = DB::select($sql);

    Log::info('Query Results:', ['data' => $query]);

    if ($request->ajax()) {
        return DataTables::of(collect($query))
            ->addColumn('name', function ($row) {
                return '<a target="_blank" href="/maid-report/p4/' . $row->name . '" class="btn btn-sm btn-blue">'. $row->name . ' </a>';
            })
            ->addColumn('nationality', function ($row) {
                return $row->nationality;
            })
            ->rawColumns(['name'])
            ->make(true);
    }

    return view('ERP.owner.page_worset_maids');
}

    
    
    // /sms-p4-warning

    public function smsWraningP4()
    {
        $P4Balance = Category4Model::select('customer', 'maid', 'Contract_ref', 'date', 'contract_status', 'created_by', 'created_at')
            ->with(['maidInfo', 'latestInvoiceInfo'])
            ->get();

            $customerNoIdImage = Customer::where('idImg', 'No ID')
            ->whereHas('userInfo', function ($query) {
                $query->where('group', 'sales');
            })
            ->where('created_at', '>', '2025-01-08')->count();
            
     


        $result = $P4Balance->filter(function ($p4) {
            $closingBalance = General_journal_voucher::calculateCustomerClosingBalance($p4->customer);
            return $closingBalance > 4000;
        })->map(function ($p4) {
            $latestInvoice = $p4->latestInvoiceInfo;
    
            return [
                'Date' => $p4->date,
                'Contract_ref' => $p4->Contract_ref,
                'Customer' => $p4->customer,
                'Maid' => $p4->maid,
                'Type' => $p4->maidInfo->maid_type ?? 'N/A',
                'Invoice Date' => $latestInvoice->date ?? 'N/A',
                'Invoice Amount' => $latestInvoice->amount ?? 0,
                'Invoice Ref Code' => $latestInvoice->refCode ?? 'N/A',
                'Closing Balance' => General_journal_voucher::calculateCustomerClosingBalance($p4->customer),
                'Created By' => $p4->created_by,
                'Contract Status' => $p4->contract_status,
                'created_at' => $p4->created_at,
            ];
        })
        ->sortByDesc('created_at')
        ->unique('Customer');
    
        return response()->json([
            'message' => 'success',
            'data' => $result,
            'count' => $result->count(),
            'no_id' =>  $customerNoIdImage
        ], 200);
    }
    
    
   
}
