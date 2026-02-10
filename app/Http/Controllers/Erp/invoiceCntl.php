<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\General_journal_voucher;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\Pre_connection_invoiceDB;
use App\Models\Customer;
use App\Models\MaidsDB;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Auth;

class invoiceCntl extends Controller
{


   public function selectInvoiceId($id){

              
             $invoice = General_journal_voucher::with(['maidRelation','customerInfo','accountLedger'] )->findOrfail($id);
             return $invoice;
   }

    public function listNoContractInvoice(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = 30;

            $query = Customer::query();

            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            }

            $customers = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'total_count' => $customers->total(),
                'items' => $customers->map(function ($customer) {
                    return [
                        'id' => $customer->name,
                        'text' => "{$customer->name} / Phone: {$customer->phone} / Closing balance: " . General_journal_voucher::calculateCustomerClosingBalance($customer->name)
                    ];
                })
            ]);
        }
        else {

            $currentDate = date('Y-m-d');
            $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();

             return view('ERP.noContract.listInv' , compact('currentDate' ,'cashAndBank') );
        }
    }//end method


    public function storeNoContractInvoiceCntl(Request $request)
    {
        Validator::extend('sum_matches', function ($attribute, $value, $parameters, $validator) use ($request) {
            $sum = array_sum($request->total_amount);
            return $sum == $request->total_invoice;
        });
    
        $messages = [
            'sum_matches' => 'The sum of total amounts must match the total invoice.',
        ];
    
        $validated = $request->validate([
            'typing_services' => 'required',
            'date_jv' => 'required|date',
            'selected_customer' => 'required',
            'total_invoice' => 'required|numeric',
            'typing_invoice' => 'required',
            'account' => 'required|array',
            'total_amount' => 'required|array|sum_matches',
            'notes' => 'required|array',
            'maid' => 'required'
        ], $messages);
  
        
        DB::transaction(function () use ($request) {
            $randomRefNumber = "par_" . Str::random(6);
            $connectionName = $request->typing_services ?: "No connection";
    
            foreach ($request->notes as $index => $note) {
                General_journal_voucher::create([
                    'date' => date('Y-m-d', strtotime($request->date_jv)),
                    'refCode' => $randomRefNumber,
                    'refNumber' => 0,
                    'voucher_type' => 'invoice',
                    'type' => 'credit',
                    'maid_name' => $request->maid,
                    'account' => $request->account[$index],
                    'amount' => $request->total_amount[$index],
                    'notes' => $note,
                    'pre_connection_name' => $connectionName,
                    'created_by' => Auth::user()->name,
                ]);
            }
    
                General_journal_voucher::create([
                    'date' => date('Y-m-d', strtotime($request->date_jv)),
                    'account' => $request->selected_customer,
                    'maid_name' => $request->maid,
                    'refNumber' => 0,
                    'refCode' => $randomRefNumber,
                    'voucher_type' => 'invoice',
                    'type' => 'debit',
                    'amount' => $request->total_invoice,
                    'invoice_balance' => $request->total_invoice,
                    'pre_connection_name' => $connectionName,
                    'notes' => $connectionName,
                    'created_by' => Auth::user()->name,
                ]);
            });
    
        return response()->json([
            'success' => true,
            'message' => 'Invoice saved successfully!'
        ], 201);
    }
    
/// URL /ajax/list/invoices
    public function ajaxAllListInvoice(Request $request)
    {
 
        try {
         
            $query = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'gjv.maid_id')
            ->leftJoin('all_account_ledger__d_b_s as a', 'a.id', '=', 'gjv.ledger_id')
            ->select(
                'gjv.date',
                'gjv.id',
                'gjv.created_at',
                'gjv.refCode',
                'gjv.contract_ref',
                'a.ledger as account',
                'm.name as maid_name',
                'gjv.pre_connection_name',
                'gjv.amount',
                'gjv.invoice_balance',
                'gjv.notes',
                'gjv.receiveRef',
                'gjv.creditNoteRef',
                'gjv.created_by',
                DB::raw("
                    CASE
                        WHEN gjv.invoice_balance = 0 THEN 'Paid'
                        WHEN gjv.amount - gjv.invoice_balance = 0 THEN 'Pending'
                        WHEN gjv.amount > gjv.invoice_balance THEN 'Partial'
                        ELSE 'Unknown'
                    END AS payment_status
                ")
            )
            ->where('gjv.voucher_type', 'invoice')
            ->where('gjv.type', 'debit')
            ->orderBy('gjv.created_at', 'desc');
        

         
            if ($request->has('min_date') && $request->min_date != '') {
                $query->whereDate('date', '>=', $request->min_date);
            }
    
            if ($request->has('max_date') && $request->max_date != '') {
                $query->whereDate('date', '<=', $request->max_date);
            }

                    if ($request->filled('invoice_balance')) {
            if ($request->invoice_balance === 'zero') {
                // Fully paid
                $query->where('gjv.invoice_balance', 0);
            } elseif ($request->invoice_balance === 'partial') {
                // Partially paid
                $query->whereColumn('gjv.amount', '>', 'gjv.invoice_balance')
                    ->where('gjv.invoice_balance', '>', 0);
            } elseif ($request->invoice_balance === 'pending') {
                // Not paid
                $query->whereColumn('gjv.amount', '=', 'gjv.invoice_balance');
            }
        }

         
        
            $data = DataTables::of($query)
                ->addColumn('action', function ($row) {

             if (auth()->user()->group === 'accounting') {
                    return '
                    
                           <button type="button" class="btn btn-sm btn-outline-warning btn-sm open-pay-modal-btn " 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#payment-inv" 
                                    data-id="'.$row->id.'" 
                                    data-customer="'.$row->account.'" 
                                    data-invoice="'.$row->refCode.'" 
                                    data-note="'.$row->notes.'">Add Payment</button>

                            <button type="button" class="btn btn-outline-warning btn-sm btn-credit-note" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#typing-credit-note-modal" 
                                    data-idForCustomer="'.$row->id.'" 
                                    data-refCode="'.$row->refCode.'">Credit Note</button>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-sm btn-apply-credit" 
                                     data-payment="'.$row->id.'" 
                                   ">Apply credit</button>
                                   
                                   ';
    

                    }
                })
                ->filterColumn('maid_name', function ($query, $keyword) {
                    $query->where('m.name', 'like', "%{$keyword}%");
                })
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
                })
                ->editColumn('payment_status', function ($row) {
                    $statusClass = match ($row->payment_status) {
                        'Paid' => 'badge bg-success',
                        'Pending' => 'badge bg-warning text-dark',
                        'Partial' => 'badge bg-info text-dark',
                        default => 'badge bg-secondary',
                    };
                    return '<span class="'.$statusClass.'">'.$row->payment_status.'</span>';
                })
                ->editColumn('refCode', function ($row) {
                    return '<a target="_blank" href="/no-contract-invoice/'.$row->refCode.'">'.$row->refCode.'</a>';
                })

                ->editColumn('account', function ($row) {
                    return '<a target="_blank" href="/customer/report/'.$row->account.'">'.$row->account.'</a>';
                })
                ->editColumn('receiveRef', function ($row) {
                    return $row->receiveRef ? '<a target="_blank" href="/receipt/'.$row->receiveRef.'">'.$row->receiveRef.'</a>' : '<p>No data</p>';
                })
                ->filterColumn('refCode', function ($query, $keyword) {
                    $query->where('gjv.refCode', 'like', "%{$keyword}%");
                })

                ->filterColumn('account', function ($query, $keyword) {
                    $query->where('a.ledger', 'like', "%{$keyword}%");
                })
                ->rawColumns(['action', 'payment_status', 'refCode', 'receiveRef','account'])
                ->make(true);
   
    
            return $data;
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ajaxAllTypingInvoicesCntl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
    
    

    public function viewNoContractInvoice($refCode){

        $allVouchers = General_journal_voucher::where('refCode', $refCode)
        ->orWhere(function($query) use ($refCode) {
            $query->where('receiveRef', $refCode)
                ->where('type', 'debit');
        })
        ->get();

        $invDetails = General_journal_voucher::where('refCode',$refCode)->get();
        $invDr = General_journal_voucher::where('refCode',$refCode)->where('type', 'debit')->get();
        $invCr = General_journal_voucher::where('refCode',$refCode)->where('type', 'credit')->get();
        $allRV = $allVouchers->where('receiveRef', $refCode)->where('type', 'debit');

        return view('ERP.noContract.template.invoiceNoContractFormat' , compact('invDetails','invDr','invCr','allRV'));
   }//End method


   public function searchMaid(Request $request)
   {
       $search = $request->input('search');

       $maids = MaidsDB::where('name', 'like', "%{$search}%")
                       ->paginate(10); 
   
       $results = $maids->map(function ($maid) {
           return [
               'id' => $maid->id,
               'text' => $maid->name
           ];
       });

       return response()->json([
           'items' => $results,
           'total_count' => $maids->total(),
       ]);
   }//end




   public function listConnectionInvoiceNonContract(Request $request){
    if ($request->ajax()) {
        $search = $request->input('search');
        $perPage = 30;

        $query = Pre_connection_invoiceDB::query();

        if (!empty($search)) {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('invoice_connection_name', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%');
            });
        }

        $service = $query->paginate($perPage);

        // Group items by invoice_connection_name
        $groupedData = $service->getCollection()->groupBy('invoice_connection_name');

        $items = $groupedData->map(function ($group) {
            return [
                'id' => $group->first()->id,
                'text' => "{$group->first()->invoice_connection_name} / Amount: {$group->first()->total_credit}",
                'full_data' => $group->toArray()
            ];
        })->values();

      

        return response()->json([
            'total_count' => $service->total(),
            'items' => $items
        ]);
    } else {
        return view('ERP.accounting.list_connection.connection_invoice');
    }
}


public function non_contract_invoice(){

    $currentDate = date('Y-m-d');
    $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();

    return view('ERP.noContract.non_contract_inv', compact('currentDate' ,'cashAndBank'));
}




}
