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
use DataTables;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class typingCntl extends Controller
{

    public function viewTypingInvoicesCntl(Request $request)
    {

        return view('ERP.typing.typing_invoice', compact( 'currentDate'));

    }



    public function viewAllTypingInvoicesCntl() {

        $date =date('Y-m-d');
        $cashAndBank = All_account_ledger_DB::where('group' ,'cash equivalent')->get();

        return view('ERP.typing.show_all_typing_invoices', compact('cashAndBank','date'));
    }//end method
  
    
public function ajaxAllTypingInvoicesCntl(Request $request)
{
    try {
        $query = DB::table('general_journal_vouchers as gjv')
                ->leftJoin('all_account_ledger__d_b_s as a', 'a.id', '=', 'gjv.ledger_id')
                ->select(
                    'gjv.id',
                    'gjv.date',
                    'gjv.created_at',
                    'gjv.refCode',
                    'a.ledger as account',
                    'gjv.pre_connection_name',
                    'gjv.amount',
                    'gjv.invoice_balance',
                    'gjv.notes',
                    'gjv.receiveRef',
                    'gjv.creditNoteRef',
                    'gjv.created_by',
                    DB::raw('
                        CASE
                            WHEN gjv.invoice_balance = 0 THEN "Paid"
                            WHEN gjv.amount - gjv.invoice_balance = 0 THEN "Pending"
                            WHEN gjv.amount > gjv.invoice_balance THEN "Partial"
                            ELSE "Unknown"
                        END as payment_status
                    ')
                )
            ->where('gjv.voucher_type', 'typing invoice')
            ->where('gjv.type', 'debit')
            ->orderBy('gjv.created_at', 'desc');

        if ($request->filled('min_date')) {
            $query->whereDate('gjv.date', '>=', $request->min_date);
        }

        if ($request->filled('max_date')) {
            $query->whereDate('gjv.date', '<=', $request->max_date);
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
                        <button type="button" class="btn btn-sm btn-outline-warning open-modal-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#typing-payment-modal" 
                                data-id="'.$row->id.'" 
                                data-customer="'.$row->account.'" 
                                data-invoice="'.$row->refCode.'" 
                                data-note="'.$row->notes.'">Add Payment</button>
                        <button type="button" class="btn btn-outline-warning btn-sm btn-credit-note" 
                                data-bs-toggle="modal" 
                                data-bs-target="#typing-credit-note-modal" 
                                data-idForCustomer="'.$row->id.'" 
                                data-refCode="'.$row->refCode.'">Credit Note</button>
                        <button type="button" class="btn btn-sm btn-outline-warning btn-apply-credit" 
                                data-payment="'.$row->id.'">Apply credit</button>
                    ';
                }
                return '';
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
                return '<a target="_blank" href="/invoice/typing/'.$row->refCode.'">'.$row->refCode.'</a>';
            })
            ->editColumn('account', function ($row) {
                // $row->account now comes from the joined ledger when possible
                return '<a target="_blank" href="/customer/report/'.$row->account.'">'.$row->account.'</a>';
            })
            ->editColumn('receiveRef', function ($row) {
                return $row->receiveRef
                    ? '<a target="_blank" href="/receipt/'.$row->receiveRef.'">'.$row->receiveRef.'</a>'
                    : '<p>No data</p>';
            })
            ->filterColumn('account', function ($query, $keyword) {
                $query->where('a.ledger', 'like', "%{$keyword}%");
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->where('gjv.created_by', 'like', "%{$keyword}%");
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'payment_status', 'refCode', 'receiveRef', 'account'])
            ->make(true);

        return $data;

    } catch (\Exception $e) {
        // This endpoint is read-only; no transaction to roll back
        Log::error('Error in ajaxAllTypingInvoicesCntl', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

    
    
    public function viewTypingInvoice($refCode){
        // Fetch all journal vouchers related or linked to the refCode once.
        $allVouchers = General_journal_voucher::where('refCode', $refCode)
            ->orWhere(function($query) use ($refCode) {
                $query->where('receiveRef', $refCode)
                    ->where('type', 'debit');
            })
            ->get();

        // Extract details, debit, and credit entries from the fetched collection.
        $invDetails = $allVouchers->where('refCode', $refCode)->first();
        $invDr = $allVouchers->where('refCode', $refCode)->where('type', 'debit');
        $invCr = $allVouchers->where('refCode', $refCode)->where('type', 'credit');
        $allRV = $allVouchers->where('receiveRef', $refCode)->where('type', 'debit');
        $vatId = All_account_ledger_DB::where('ledger','VAT')->first();
        // Calculate totals by pre-connection name for credit type vouchers.
        $totalPreConnection = $invCr->groupBy('pre_connection_name')
            ->map(function ($items, $key) use ($allVouchers , $vatId) {
                $vatAmount = $allVouchers->where('type', 'credit')
                    ->where('ledger_id', $vatId->id)
                    ->where('pre_connection_name', $key)
                    ->sum('amount');
                return [
                    'total' => $items->sum('amount'),
                    'vatAmount' => $vatAmount
                ];
            });

    
        return view('ERP.typing.template.invoiceTypingFormat', compact('invDetails', 'invDr', 'invCr', 'totalPreConnection', 'allRV'));
    }//End method




    public function saveTypingInvoice(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'typing_services' => 'required|array',
            'date_jv' => 'required|date',
            'selected_customer' => 'required',
            'total_invoice' => 'required|numeric',
            'typing_invoice' => 'required',
            'qtn' => 'required|array', 
        ]);
    
        try {
            if ($validator->fails()) {
                return response([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ]);
            }
    
            $randomRefNumber = 'typ_' . Str::random(6);
            $servicesString = implode(', ', $request->typing_services);
            $notes = implode(', ', $request->notes);
            $totalCredit = 0;
    
            DB::beginTransaction();
    
            foreach ($request->typing_services as $index => $value) {
                $amountsFromConnection = Pre_connection_invoiceDB::where('invoice_connection_name', $value)->get();
                
               
                $qty = $request->qtn[$index]; 
    
                foreach ($amountsFromConnection as $invoiceDetail) {
                 
                    $amount = $invoiceDetail->amount * $qty;
    
                    $totalCredit += $amount;
    
                    General_journal_voucher::create([
                        'date' => date('Y-m-d', strtotime($request->date_jv)),
                        'account' => $invoiceDetail->ledger,
                        'refNumber' => 0,
                        'refCode' => $randomRefNumber,
                        'voucher_type' => $request->typing_invoice,
                        'type' => 'credit',
                        'amount' => $amount, // Updated with amount * qty
                        'pre_connection_name' => $invoiceDetail->invoice_connection_name,
                        'notes' => $notes,
                        'created_by' => Auth::user()->name,
                        'created_at' => now(),
                    ]);
                }
            }
    
            // Check if total credit matches the total invoice
            if (number_format($totalCredit, 2) !== number_format($request->total_invoice, 2)) {
                throw new \Exception('Total credit and debit not equal!');
            }
    
            // Insert the debit entry for the customer
            General_journal_voucher::create([
                'date' => date('Y-m-d', strtotime($request->date_jv)),
                'account' => $request->selected_customer,
                'refNumber' => 0,
                'refCode' => $randomRefNumber,
                'voucher_type' => $request->typing_invoice,
                'type' => 'debit',
                'amount' => $request->total_invoice,
                'invoice_balance' => $request->total_invoice,
                'pre_connection_name' => $servicesString,
                'notes' => $notes,
                'created_by' => Auth::user()->name,
                'created_at' => now(),
            ]);
    
            DB::commit();
    
            return response([
                'success' => true,
                'message' => 'Invoice saved successfully!'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'success' => false,
                'message' => 'Failed to save invoice: ' . $e->getMessage()
            ]);
        }
    }
    
    

    public function listConnectionInvoice(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $perPage = 30;
    
            $query = Pre_connection_invoiceDB::query();
    
            // Apply search conditions if necessary
            if (!empty($search)) {
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('invoice_connection_name', 'like', '%' . $search . '%')
                        ->orWhere('amount', 'like', '%' . $search . '%');
                });
            }
            $query->whereIn('amount', function ($subquery) {
                $subquery->selectRaw('MAX(amount) AS amount')
                    ->from('pre_connection_invoice_d_b_s')
                    ->groupBy('invoice_connection_name');
            });
    
            $service = $query->paginate($perPage);
    
            // Send the entire object data
            return response()->json([
                'total_count' => $service->total(),
                'items' => $service->getCollection()->transform(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => "{$item->invoice_connection_name} / Amount: {$item->total_credit}",
                        'full_data' => $item->toArray()  
                    ];
                }),
            ]);
        }
        else {
    
            return view('ERP.accounting.list_connection.connection_invoice');
        }
    }


}


