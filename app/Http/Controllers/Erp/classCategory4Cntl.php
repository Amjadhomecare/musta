<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use App\Models\Category4Model;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use App\Models\UpcomingInstallment;
use App\Models\Customer;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Log;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Stripe\Subscription;

class classCategory4Cntl extends Controller
{

    
public function getCategory4Invoics(Request $request)
{
    try {
         
         $query = DB::table('general_journal_vouchers as gjv')
        ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'gjv.maid_id')
        ->leftJoin('all_account_ledger__d_b_s as a', 'a.id', '=', 'gjv.ledger_id')
        ->select(
            'gjv.id',
            'gjv.date',
            'gjv.created_at',
            'gjv.refCode',
            'gjv.contract_ref',
            'a.ledger as account',
            DB::raw('m.name as maid_name'), 
            'gjv.pre_connection_name',
            'gjv.amount',
            'gjv.invoice_balance',
            'gjv.notes',
            'gjv.receiveRef',
            'gjv.creditNoteRef',
            DB::raw("
                CASE
                    WHEN gjv.invoice_balance = 0 THEN 'Paid'
                    WHEN gjv.amount - gjv.invoice_balance = 0 THEN 'Pending'
                    WHEN gjv.amount > gjv.invoice_balance THEN 'Partial'
                    ELSE 'Unknown'
                END AS payment_status
            ")
        )
        ->where('gjv.voucher_type', 'Invoice Package4')
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
            return '<button id="payment-modal-inv" type="button" class="btn btn-outline-warning btn-sm open-modal-btn payment-modal" 
                                        data-id="'.$row->id.'"
                                   >Add Payment</button>
                    <button id="credit-note-inv" type="button" class="btn btn-outline-warning  btn-sm btn-credit-note" 
                            data-idForCustomer="'.$row->id.'" 
                            data-refCode="'.$row->refCode.'">Credit Note</button>

                              <button type="button" class="btn btn-sm btn-outline-warning btn-sm btn-apply-credit" 
    
                                     data-payment="'.$row->id.'" 
                                   ">Apply credit</button>
                            
                            ';
            }
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
                return '<a target="_blank" href="/get/invoice/cat4/'.$row->refCode.'">'.$row->refCode.'</a>';
            })

            ->editColumn('contract_ref', function ($row) {
                return '<a target="_blank" href="/category4/contract-bycontract/'.$row->contract_ref.'">'.$row->contract_ref.'</a>';
            })

            ->editColumn('maid_name', function ($row) {
                return '<a target="_blank" href="/maid-report/'.$row->maid_name.'">'.$row->maid_name.'</a>';
            })
       
            ->editColumn('receiveRef', function ($row) {
                return $row->receiveRef ? '<a target="_blank" href="/receipt/'.$row->receiveRef.'">'.$row->receiveRef.'</a>' : '<p>No data</p>';
            })
            ->editColumn('account', function ($row) {
                return $row->account ? '<a target="_blank" href="/customer/report/'.$row->account.'">'.$row->account.'</a>' : '<p>No data</p>';
            })

            ->filterColumn('maid_name', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })  
            ->filterColumn('account', function ($query, $keyword) {
                $query->where('a.ledger', 'like', "%{$keyword}%");
            })

            
            ->rawColumns(['action', 'payment_status', 'refCode', 'contract_ref', 'receiveRef', 'account','maid_name'])

            ->make(true);

        return $data;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in ajaxAllTypingInvoicesCntl:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}
 // end method

    public function viewAddingCategory4Contract()
     { 
        $today=date('Y-m-d'); 

        $randomRefNumber = Str::random(5);
        $maids = MaidsDB::where('maid_status', 'approved')
        ->whereNull('maid_booked')
        ->get();
  
        return view('ERP.cat4.add_cat_4_contract' ,compact( 'maids' ,'today' , 'randomRefNumber'));
    }//end method Ok



    public function storeCategory4ContractCntl(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'contract_date' => 'required|date',
            'contract_ref' => 'required|string',
            'selected_customer' => 'required|string',
            'selected_maid' => 'required|string',
            'amount' => 'required|array|min:1', 
            'amount.*' => 'required|numeric',
            'cat4date' => 'required|array|min:6',
            'cat4date.*' => 'required|date',
            'note' => 'nullable|array',
            'cheque' => 'nullable|array',
        ], [
            'cat4date.min' => 'it should be 6 Installments Or more',
        ]);
        // Find maid by name

        $cutomerId = Customer::where('name', $validated['selected_customer'])->first()->id;

        $validated['stripe_payment'] = $request->has('stripe_payment') ? 'on' : null;

        $maid = MaidsDB::where('name', $request->selected_maid)->firstOrFail();

        if ($maid->maid_status !== 'approved') {
            return redirect()->back()->withErrors(['maid' => 'The selected maid must have an approved status to proceed.'])->withInput();
        }
    
        try {
          
            DB::transaction(function () use ($validated, $maid , $cutomerId) {
              
    
                Category4Model::create([
                    'date' => Carbon::parse($validated['contract_date'])->format('Y-m-d'),
                    'category' => 'category4',
                    'Contract_ref' => $validated['contract_ref'],
                    'customer_id' => $cutomerId,
                    'maid_id' => $maid->id,
                    "extra"=>  null,
                    'created_by' => Auth::user()->name,
                    'created_at' => now(),
                ]);
    
           
                foreach ($validated['amount'] as $i => $amount) {
                    UpcomingInstallment::create([
                        'contract' => $validated['contract_ref'],
                        'customer_id' =>  $cutomerId,
                        'accrued_date' => Carbon::parse($validated['cat4date'][$i])->format('Y-m-d'),
                        'amount' => $amount,
                        'note' => $validated['note'][$i] ?? null,
                        'cheque' => $validated['cheque'][$i] ?? null,
                        'created_by' => Auth::user()->name,
                    ]);
                }
    
                $maid->update(['maid_status' => 'hired']);

         
            });
    
            $notification = [
                'message' => 'New Category 4 Contract Added Successfully',
                'alert-type' => 'success',
            ];
    
        } catch (\Exception $e) {
      
            Log::error('Error storeCategory4ContractCntl: ' . $e->getMessage(), [
                'stackTrace' => $e->getTraceAsString()
            ]);
    
    
            $notification = [
                'message' => 'Error: ' . $e->getMessage(),
                'alert-type' => 'error',
            ];
        }
    
      
        return redirect()->back()->with($notification);
    }
 
    private function createStripeSetup($customerName, $maidName, $amount)
        {
    Stripe::setApiKey(config('services.stripe.secret'));

    try {

        $product = \Stripe\Product::create([
            'name' => $maidName,
        ]);

      
        $price = \Stripe\Price::create([
            'unit_amount' => $amount * 100, 
            'currency' => 'aed', 
            'recurring' => ['interval' => 'month'], 
            'product' => $product->id,
        ]);

       
        $paymentLink = \Stripe\PaymentLink::create([
            'line_items' => [[
                'price' => $price->id,
                'quantity' => 1,
            ]],
        ]);

        return $paymentLink->url; 
    } catch (\Exception $e) {
        Log::error('Error creating Stripe setup: ' . $e->getMessage());
        throw new \Exception('Failed to create Stripe setup: ' . $e->getMessage());
    }
}

    


    public function viewCategory4UpcomingCntl(){

         return view('ERP.cat4.viewDateAccruedInvoices');                                     

    }//endMethod

    public function viewAccruedDateCat4Cntl(Request $request)
    {
        $dates = explode(' to ', $request->date_range);
        $dateFrom = $dates[0] ?? null;
        $dateTo = $dates[1] ?? null;
    
        // Corrected query with relationship
        $cat4Ref = UpcomingInstallment::whereHas('contractRef', function ($query) {
                                                $query->where('contract_status', 1);
                                            })
                                ->where('invoice_status', 0)
                                ->whereBetween('accrued_date', [$dateFrom, $dateTo])
                                ->get();
    
        return view('ERP.cat4.selectedDateAccruedInvoices', compact('cat4Ref'));                                     
    }//end method
       

    public function viewAllCat4Cntl(Request $request){
    
        return view('ERP.cat4.allCategory4Contracts');
    }//endMethod


    /// url category4/data
public function getCategory4Contracts(Request $request)
{
    if ($request->ajax()) {

        $query = DB::table('category4_models as c4')
            ->leftJoin('maids_d_b_s as m', 'c4.maid_id', '=', 'm.id')
            ->leftJoin('customers as cu', 'c4.customer_id', '=', 'cu.id')
            ->select(
                'c4.id',
                'c4.date',
                'c4.created_at',
                'c4.Contract_ref',
                'c4.contract_status',
                'c4.signature',
                'c4.created_by',
                'm.name as maid_name',
                'cu.phone as customer_phone',
                'cu.name as customer',
            );

        if ($request->filled('min_date')) {
            $query->whereDate('c4.date', '>=', $request->min_date);
        }

        if ($request->filled('max_date')) {
            $query->whereDate('c4.date', '<=', $request->max_date);
        }

        if ($request->has('no_image') && $request->no_image == 'true') {
            $query->whereNull('cu.idImg');
        }

        $query->orderBy('c4.created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('customer', function ($row) {
                return '<a href="' . url("/customer/report/p4/{$row->customer}") . '" target="_blank">' . $row->customer . '</a>';
            })

            ->filterColumn('maid', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })

            ->editColumn('maid', function ($row) {
                return '<a href="' . url("/maid-report/p4/{$row->maid_name}") . '" target="_blank">' . $row->maid_name . '</a>';
            })

            ->editColumn('Contract_ref', function ($row) {
                return '<a href="' . url("/category4/contract-bycontract/{$row->Contract_ref}") . '" target="_blank">' . $row->Contract_ref . '</a>';
            })

            ->addColumn('phone', function ($row) {
                return '<a href="' . url("customer/soa/{$row->customer}") . '" target="_blank">' . $row->customer_phone . '</a>';
            })

            ->filterColumn('phone', function ($query, $keyword) {
                $query->where('cu.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('cu.name', 'like', "%{$keyword}%");
            })

            ->addColumn('action', function ($row) {
                $actionButtons = '<div class="dropdown">
                    <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Action
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                if ($row->contract_status === 0) {
                    $actionButtons .= '
                        <li><p class="dropdown-item text-muted">Returned</p></li>
                        <li><button type="button" class="dropdown-item open-comp-modal-btn" data-bs-toggle="modal" data-bs-target="#comp_modal" data-maid="' . $row->maid_name . '" data-contractref="' . $row->Contract_ref . '" data-customer="' . $row->customer . '"><i class="fa fa-book"></i> Add complaint</button></li>
                        <li><a target="__blank" href="/copy/upcoming/' . $row->Contract_ref . '" class="dropdown-item"><i class="fa fa-copy"></i> Copy</a></li>
                        <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                        <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                        <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>
                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-id="' . $row->id . '"><i class="fa fa-clock"></i>Edit Start date</button></li>
                    ';
                } else {
                    $actionButtons .= '
                        <li><button type="button" class="dropdown-item open-modal-btn" data-bs-toggle="modal" data-bs-target="#return_modal" data-maid="' . $row->maid_name . '" data-contractref="' . $row->Contract_ref . '" data-customer="' . $row->customer . '"><i class="fa fa-undo"></i> Return</button></li>
                        <li><button type="button" class="dropdown-item open-comp-modal-btn" data-bs-toggle="modal" data-bs-target="#comp_modal" data-maid="' . $row->maid_name . '" data-contractref="' . $row->Contract_ref . '" data-customer="' . $row->customer . '"><i class="fa fa-book"></i> Add complaint</button></li>
                        <li><a target="__blank" href="/edit-upcoming-installment/' . $row->Contract_ref . '" class="dropdown-item"><i class="fa fa-cog"></i> Edit</a></li>
                        <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                        <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                        <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>
                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-id="' . $row->id . '"><i class="fa fa-clock"></i>Edit Start date</button></li>
                    ';

                    if ($row->signature === 'No signature') {
                        $actionButtons .= '
                            <li><a href="' . url(env('sign_contract') . "/" . $row->id) . '" target="_blank" class="dropdown-item">
                                <i class="fa fa-signature"></i> Sign
                            </a></li>';
                    } else {
                        $actionButtons .= '
                            <li><button type="button" class="dropdown-item delete-sign" data-id="' . $row->id . '">
                                <i class="fa fa-trash"></i> Delete Signature
                            </button></li>';
                    }
                }

                $actionButtons .= '</ul></div>';
                return $actionButtons;
            })
            ->rawColumns(['action', 'customer', 'maid', 'Contract_ref', 'phone'])
            ->make(true);
    }

    return view('ERP.cat4.allCategory4Contracts');
}


public function viewContract4Summary($id){

    $conDetails = Category4Model::findOrFail($id)->get();
    
 return view('ERP.cat4.template.contract_summary4' , compact('conDetails'));
}//End method

public function viewContractSummary4ByContractRef($contract){

    $conDetails = Category4Model::where('contract_ref',$contract)->get();
    $account =  General_journal_voucher::where('ledger_id' , $conDetails[0]->customerInfo->ledger_id)
                                                ->where('voucher_type' , 'Invoice Package4')
                                                ->where('type' , 'debit')
                                                ->get();
   
 return view('ERP.cat4.template.contract_summary4' , compact('conDetails' , 'account'));
}//End method

public function viewAllInvoicesCat4(){  
    $date =date('Y-m-d');
    $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();
 return view( 'ERP.cat4.allCategory4Invoices' , compact('date','cashAndBank') );
}//End method



public function viewCate4Invoice($refCode){

     $invDetails = General_journal_voucher::with(['accountLedger','customerInfo'])
                              ->where('refCode',$refCode)->get();
     $rv = General_journal_voucher::where('receiveRef',$refCode)->where('type' , 'debit')->get();
     $customerClosingBalance = $invDetails->map(function ($item) {
     $item->closing_balance = General_journal_voucher::calculateCustomerClosingBalance($item?->customerInfo?->name);
     return $item;
 });
 return view('ERP.cat4.template.invoiceCat4Format' , compact('invDetails','customerClosingBalance','rv'));
}//End method


public function fullContractView($ref)
{
    $con4 = Category4Model::findOrFail($ref);
   

    return view('ERP.cat4.template.cat4_full_contract', compact('con4'));
}

public function fullContractArabicView($ref)
{
    $con4 = Category4Model::findOrFail($ref);
   

    return view('ERP.cat4.template.arabic_contract_p4', compact('con4'));
}

// url transfer-leter-p4/{id}
public function p4_transfer_letter($ref){

    $conDetails = Category4Model::findOrFail($ref);
  
    return view('ERP.cat4.template.p4_transfer_letter' , compact('conDetails'));

}



public function viewSignPageP4($id)
{
    $p4Data = Category4Model::with(['customerInfo', 'maidInfo'])->findOrFail($id);

    // Build a lightweight view-model so the Blade can reuse the same names as your transfer letter
    $conDetails = (object) [
        'customerInfo' => $p4Data->customerInfo,
        'maidInfo'     => $p4Data->maidInfo,
        'created_at'   => $p4Data->created_at,
        'signature'    => $p4Data->signature ?? null, // if you store customer signature later
    ];

    return view('ERP.cat4.signature_p4', compact('p4Data', 'conDetails'));
}


public function saveSignatureP4(Request $request)
{
    $request->validate([
        'signature' => 'required',
        'id' => 'required|exists:category4_models,id'
    ]);

    if ($request->input('signature')) {
        $signatureRecord =  Category4Model::find($request->id); 
        if ($signatureRecord->signature == 'No signature') { 
            if ($signatureRecord) {
                $disk = 'beta';
                $signature_url = $signatureRecord->signature;
                $normalized_url = str_replace("\\", "/", $signature_url);
                $urlParts = parse_url($normalized_url);
                $pathParts = explode('/', ltrim($urlParts['path'], '/'), 3);
                $key = end($pathParts);
                $bucket = 'nextmetaerp';
                
                // Check if the file exists on S3
                if (Storage::disk($disk)->exists($key, $bucket)) {
                    // Delete the file from S3
                    $deleteSignature = Storage::disk($disk)->delete($key, $bucket);

                    if (!$deleteSignature) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Signature not found'
                        ], 404);
                    }
                }
            
                // Decode the Base64 string
                $base64Image = $request->input('signature');
                $imageData = explode(',', $base64Image)[1]; // Remove the prefix
                $decodedData = base64_decode($imageData);

                // Save the signature in the folder 'ahlia_p1signature'
                $folder = 'ahlia_p4signature/'; // Define the folder name
                $fileName = $folder . uniqid('signature_') . '.png'; // Include folder in the file path

                $path = Storage::disk($disk)->put($fileName, $decodedData);

                if (!$path) {
                    Log::error("File upload to S3 failed.");
                    return response()->json(['message' => 'File upload to S3 failed'], 500);
                }

                $signatureUrl = Storage::disk($disk)->url($fileName);
                $signatureRecord->signature = $signatureUrl;
                $signatureRecord->save();

                return response("
                    <html>
                        <head>
                            <title>Signature Saved</title>
                            <style>
                                body { 
                                    font-family: Arial, sans-serif; 
                                    text-align: center; 
                                    background-color: #f5f5f5; 
                                    padding: 50px;
                                }
                                .box {
                                    background: white;
                                    border-radius: 10px;
                                    padding: 40px;
                                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                                    display: inline-block;
                                }
                                h2 { color: #4CAF50; }
                                p { color: #333; }
                            </style>
                        </head>
                        <body>
                            <div class='box'>
                                <h2>✅ Signature Saved Successfully!</h2>
                                <p>Your signature has been stored successfully.</p>
                                <img src='{$signatureUrl}' alt='Signature' style='max-width:300px; margin-top:20px; border:1px solid #ddd; border-radius:5px;' />
                            </div>
                        </body>
                    </html>
                ", 200)->header('Content-Type', 'text/html');
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Signature already exists'
            ], 400);
        }
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Invalid signature data'
    ], 400);
}


public function deletSignP4(Request $request)
{
    $signatureRecord =  Category4Model::find($request->id);
    if (!$signatureRecord) {
        return response()->json(['success' => false, 'message' => 'Signature record not found.'], 404);
    }

    $disk = 'beta';
    $sign_url = $signatureRecord->signature;
    $normalized_url = str_replace("\\", "/", $sign_url);
    $urlParts = parse_url($normalized_url);
    

    $pathParts = explode('/', ltrim($urlParts['path'], '/'));
    $folder = 'ahlia_p4signature/';
    $key = $folder . end($pathParts); 

    
    if (Storage::disk($disk)->exists($key)) {
       
        if (Storage::disk($disk)->delete($key)) {
         
            $signatureRecord->signature = 'No signature';
            $signatureRecord->save();

            return response()->json(['success' => true, 'message' => 'Signature deleted successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to delete file from S3.']);
        }
    } else {
        $signatureRecord->signature = 'No signature';
        $signatureRecord->save();

        return response()->json(['success' => true, 'message' => 'No file found on S3. Signature updated successfully.']);
    }
}


// p4/get/date/{id}
public function getById($id){
    $data =Category4Model::with('returnInfo')
      ->findOrFail($id);
    return response()->json(['success' => true, 'response' => $data]);
}

// update/date/p4
public function updateById(Request $request)
{
    $request->validate([
        'started_date_id' => 'required|exists:category4_models,id',
        'started_date'    => 'required|date',
        'returned_date'   => 'nullable|date',
        'reason'          => 'nullable|string',
    ]);

    if (Auth::user()->group !== "accounting") {
        return response()->json([
            'error'   => true,
            'message' => 'Only accountant allowed to edit the start date'
        ], 422);
    }

    try {
        $contract = Category4Model::with('returnInfo')
            ->findOrFail($request->input('started_date_id'));

        // ✅ Update contract date
        $contract->update([
            'date'       => $request->input('started_date'),
            'updated_by' => Auth::user()->name
        ]);

        // ✅ If returnInfo exists and returned_date was provided
        if ($contract->returnInfo && $request->filled('returned_date')) {
            $contract->returnInfo->update([
                'returned_date' => $request->input('returned_date'),
                'reason'        => $request->input('reason'),
                'updated_by'    => Auth::user()->name
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dates updated successfully!'
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update date.',
            'error'   => $e->getMessage()
        ]);
    }
}



}// End Class

