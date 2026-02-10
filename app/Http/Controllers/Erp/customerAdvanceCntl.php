<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Log;
use App\Models\General_journal_voucher;
use App\Models\customerAdvance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use DataTables;

class customerAdvanceCntl extends Controller
{

    // /ar-ads
    public function pageCustomerAdvance(){

        return view('ERP.customers.advance');
    }

    // /table-ads
   public function tableCustomerAdvance(Request $request)
    {
        if (! $request->ajax()) {
            abort(400, 'Bad Request');
        }

        // Build with Query Builder (safe for null relations)
        $query = DB::table('customer_advances as ca')
            ->leftJoin('customers as c', 'c.id', '=', 'ca.customer_id')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'ca.maid_id')
            ->select([
                'ca.*',
                DB::raw('c.name as customer'),
                DB::raw('c.phone as phone_number'),
                DB::raw('m.name as maid'),
            ]);

        // Optional date filters (kept on created_at to match your current behavior)
        if ($request->filled('min_date')) {
            $query->whereDate('ca.created_at', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $query->whereDate('ca.created_at', '<=', $request->max_date);
        }

        $query->orderBy('ca.created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()

            // Customer name links to P4 report (handles nulls)
            ->editColumn('customer', function ($row) {
                if (empty($row->customer)) return '-';
                $name = e($row->customer);
                $url  = url("/customer/report/p4/{$name}");
                return '<a href="'.$url.'" target="_blank">'.$name.'</a>';
            })

            // Maid name links to maid report (handles nulls)
            ->editColumn('maid', function ($row) {
                if (empty($row->maid)) return '-';
                $name = e($row->maid);
                $url  = url("/maid-report/{$name}");
                return '<a href="'.$url.'" target="_blank">'.$name.'</a>';
            })

            // Ref link (handles nulls)
            ->editColumn('ref', function ($row) {
                if (empty($row->ref)) return '-';
                $ref = e($row->ref);
                $url = url("/receipt/{$ref}");
                return '<a href="'.$url.'" target="_blank">'.$ref.'</a>';
            })

            // Phone number column: link to SOA using customer name if present
            ->addColumn('phone_number', function ($row) {
                $phone = $row->phone_number ? e($row->phone_number) : '-';
                if (empty($row->customer)) return $phone;
                $name = e($row->customer);
                $url  = url("/customer/soa/{$name}");
                return '<a href="'.$url.'" target="_blank">'.$phone.'</a>';
            })
            ->filterColumn('phone_number', function ($query, $keyword) {
                $query->where('c.phone', 'like', "%{$keyword}%");
            })

            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('c.name', 'like', "%{$keyword}%");
            })

            ->filterColumn('maid', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })

            // Action button (only for accounting)
            ->addColumn('action', function ($row) {
                if (auth()->check() && auth()->user()->group === 'accounting') {
                    return '<button type="button" class="btn btn-primary btn-sm receive-advance-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#receive-advance-modal"
                                data-id="'.e($row->id).'">Receive Advance</button>';
                }
                return '';
            })

            ->rawColumns(['customer','maid','ref','phone_number','action'])
            ->make(true);
    }

    //ads/{id}
    public function getAdvanceDetails($id)
    {
        $customerAdvance = customerAdvance::with(['customerInfo:name,id', 'maidInfo:name,id'])->findOrFail($id);
        return response()->json($customerAdvance);
    }


    // /store-ar-ads 
    public function storeAdvance(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'nullable|date',
            'customer' => 'required|string|max:255|exists:customers,name', 
            'maid' => 'required|string|max:255',
            'post_type' => 'required|string',
            'note' => 'required|string|max:500',
            'amount' => 'required|integer|min:1',
           
        ]);

        $customerId = DB::table('customers')->where('name', $validatedData['customer'])->value('id');
        $maidId = DB::table('maids_d_b_s')->where('name', $validatedData['maid'])->value('id');
    
   
        $customerAdvance = customerAdvance::create([
            'date' => $validatedData['date'],
            'customer_id' => $customerId,
            'maid_id' => $maidId,
            'post_type' => $validatedData['post_type'],
            'note' => $validatedData['note'],
            'amount' => $validatedData['amount'],
            'created_by' => Auth()->user()->name ?? 'system',
      
        ]);
    
     
        return response()->json([
            'message' => 'Customer advance added successfully.',
            'data' => $customerAdvance
        ], 201);
    }//end 


    // /rv-advance
public function storeRvAdvance(Request $request)
{
    $validatedData = $request->validate([
        'customer_advance_id' => 'exists:customer_advances,id',
        'date' => 'required|date',
        'debit_ledger' => 'required|string',
        'credit_ledger' => 'required|string|max:255|exists:customers,name',
        'maid_name' => 'nullable|string|max:255',
        'note' => 'nullable|string|max:255',
        'amount_received' => 'required|numeric|min:1'
    ]);

    $randomRefNumber = "adv_" . Str::random(5);
    $now = now();

    $transaction = customerAdvance::findOrFail($validatedData['customer_advance_id']);

    $debitLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $validatedData['debit_ledger'])->first();
    $creditLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $validatedData['credit_ledger'])->first();

    if (!$debitLedger || !$creditLedger) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ledger not found for debit or credit account.',
        ], 422);
    }

    $voucherData = [
        [
            'date' => $validatedData['date'],
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'debit',

            'ledger_id' => $debitLedger->id,
            'amount' => $validatedData['amount_received'],
        
            'notes' => $validatedData['note'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'date' => $validatedData['date'],
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'credit',

            'ledger_id' => $creditLedger->id,
            'amount' => $validatedData['amount_received'],
         
            'notes' => $validatedData['note'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    ];

    DB::transaction(function () use ($voucherData, $transaction, $validatedData, $randomRefNumber) {
        General_journal_voucher::insert($voucherData);

        $transaction->update([
            'ref' => $randomRefNumber,
            'received' => $validatedData['amount_received'],
            'updated_by' => Auth::user()->name,
            'updated_at' => now(),
        ]);
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Receipt Voucher saved successfully'
    ], 201);
}


  
    
}
