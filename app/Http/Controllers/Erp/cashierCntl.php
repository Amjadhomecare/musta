<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountingPreConnection;
use App\Models\All_account_ledger_DB;
use App\Models\Pre_connection_invoiceDB;
use App\Models\MaidsDB;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use DataTables;

use App\Models\General_journal_voucher;


class cashierCntl extends Controller
{
    // page/cashier
    public function pageCashir (){

        return view('ERP.accounting.cashier');
    }


       
   public function storeRv(Request $request)
{
    $validatedData = $request->validate([
        'debit_ledger' => 'required|string',
        'credit_ledger' => 'required|string',
        'maid_name' => 'nullable|string|max:255',
        'note' => 'nullable|string|max:255',
        'amount_received' => 'required|numeric|min:1'
    ]);

    $debitLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $validatedData['debit_ledger'])->first();
    $creditLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $validatedData['credit_ledger'])->first();
    $maidID =MaidsDB::where('name', $validatedData['maid_name'])->value('id');

     if ($validatedData['maid_name'] && !$maidID) {
        return response()->json([
            'status' => 'error',
            'message' => 'Maid not found.'
        ], 422);
    }

    if (!$debitLedger || !$creditLedger) {
        return response()->json([
            'status' => 'error',
            'message' => 'One or both ledgers not found.'
        ], 422);
    }

    $randomRefNumber = Str::random(5);
    $now = Carbon::now();

    $voucherData = [
        [
            'date' => $now,
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'debit',
            'ledger_id' => $debitLedger->id,
            'amount' => $validatedData['amount_received'],
            'maid_id' => $maidID ?? null,
            'notes' => $validatedData['note'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'date' => $now,
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'credit',
            'ledger_id' => $creditLedger->id,
            'amount' => $validatedData['amount_received'],
            'maid_id' => $maidID ?? null,
            'notes' => $validatedData['note'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    ];

    DB::transaction(function () use ($voucherData) {
        General_journal_voucher::insert($voucherData);
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Receipt Voucher saved successfully'
    ], 201);
}



public function dataTableRv(Request $request)
{
    try {
        $query = General_journal_voucher::query()
            // IMPORTANT: include the FKs used by relations
            ->select('id','date','voucher_type','refCode','amount','notes','type','created_at','ledger_id','maid_id')
            ->with([
                'accountLedger:id,ledger',   // don't alias here
                'maidRelation:id,name',      // correct relation name
            ])
            ->where('voucher_type','Receipt Voucher');

        if ($request->filled('min_date')) {
            $query->whereDate('date', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $query->whereDate('date', '<=', $request->max_date);
        }

        return DataTables::of($query)
            // Flatten related fields for the table
            ->addColumn('account', function ($row) {
                return $row->accountLedger->ledger ?? '-';
            })
            ->addColumn('maid_name', function ($row) {
                return $row->maidRelation->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                return '<a target="__blank" href="/receipt/' . $row->refCode . '" class="btn btn-blue rounded-pill waves-effect waves-light">
                            <i class="fa fa-eye" aria-hidden="true"></i> View
                        </a>';
            })
            // Searching on flattened columns
            ->filterColumn('refCode', function($q, $keyword) {
                $q->where('refCode', 'like', "%{$keyword}%");
            })
            ->filterColumn('account', function($q, $keyword) {
                $q->whereHas('accountLedger', function($qq) use ($keyword) {
                    $qq->where('ledger', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('maid_name', function($q, $keyword) {
                $q->whereHas('maidRelation', function($qq) use ($keyword) {
                    $qq->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error in dataTableRv:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}




        // receipt/{num} 

        public function showRV($num)
        {
            $debitData = General_journal_voucher::where('refCode', $num)
                            ->where('type', 'debit')
                            ->first();
        
            $creditData = General_journal_voucher::where('refCode', $num)
                             ->where('type', 'credit')
                             ->first();

            $relatedReceiveData = General_journal_voucher::where('refCode', $debitData->receiveRef)->first();
                    
            return view('ERP.accounting.template.rv_voucher', compact('debitData', 'creditData','relatedReceiveData'));
        }
        
    }
