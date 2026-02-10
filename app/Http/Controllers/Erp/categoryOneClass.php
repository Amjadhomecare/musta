<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\General_journal_voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\Pre_connection_invoiceDB;
use App\Models\MaidsDB;
use App\Models\categoryOne;
use App\Models\Customer;
use App\Models\maidReturnCat1;
use Carbon\Carbon;
use DataTables;
use Auth;
use Illuminate\Support\Facades\DB;


class categoryOneClass extends Controller
{
    public function viewAddingCategory1Contract()
    { 
       $today=date('Y-m-d'); 
       $maids = MaidsDB::where('maid_status', 'approved')
                        ->whereNull('maid_booked')
                        ->get();
       $selectConnection = Pre_connection_invoiceDB::where('group','category1')
                                                    ->select('invoice_connection_name')
                                                    ->groupBy('invoice_connection_name')->get();
       $twoYearsLater = (new \DateTime($today))->add(new \DateInterval('P2Y'))->format('Y-m-d');
       $trialEnd = (new \DateTime($today))->add(new \DateInterval('P7D'))->format('Y-m-d');
 
       return view('ERP.cat1.formCreateContract' ,compact('maids','today','selectConnection','twoYearsLater','trialEnd'));
   }//end method Ok


   public function fetchPerConnection($service){

              $connections = Pre_connection_invoiceDB::where('invoice_connection_name' ,$service )->get();

    return response()->json($connections);
                   

   }

   public function storeCateOneContract(Request $request)
   {  

      $request->validate([
                'selected_customer' => 'required|string',
                'maid' => 'required|string',
                'connaction' => 'required|string',
                'date_start' => 'required|date',
                'date_ended' => 'required|date|after:date_start',
                'amount' => 'required|array|min:1',
                'amount' => 'required|array',
                'total_invoice' => 'required|numeric',
                'trial_start' => 'nullable|date',
                'trial_end' => 'nullable|date|after:trial_start',
            ]);

            $sumOfAmounts = array_sum($request->amount);

            if ($sumOfAmounts != $request->total_invoice) {
                return redirect()->back()->withErrors(['amount' => 'The sum of the amounts must equal the total invoice amount.'])->withInput();
            }
      
            $maid_status = MaidsDB::where('name', $request->maid)->first(); 
            $customer = Customer::where('name', $request->selected_customer)->first();

            if (!$request->maid ||  $maid_status->maid_status !== 'approved') {
                return redirect()->back()->withErrors(['maid' => 'The selected maid must have an approved status to proceed.'])->withInput();
            }
         
       DB::transaction(function () use ($request , $customer, $maid_status) {
           $maid = MaidsDB::where('name', $request->maid)->first(); 

  
           $invoiceRef = 'inv1_' . Str::random(5);
           $contractRef = 'p1_' . Str::random(5);

          
           categoryOne::create([
               'customer_id' => $customer->id,
               'maid_id' => $maid_status->id,
               'nationality' => $maid->nationality,
               'started_date' => $request->date_start,
               'ended_date' => $request->date_ended,
               'contract_ref' => $contractRef,	
               'amount' => $request->total_invoice,   	
               'category' => 'Category one',	
               'invoice_ref' => $invoiceRef,
               'trial_start' => $request->trial_start,
               'trial_end' => $request->trial_end,
               'note' => 'no data',
               'created_by' => Auth::user()->name
           ]);
   
           $this->createJournalVoucher($invoiceRef, $contractRef, $request, 'debit');
           
           foreach ($request->amount as $index => $amount) {
               $this->createJournalVoucher($invoiceRef, $contractRef, $request, 'credit', $index);
           }
   
           $maid->update(['maid_status' => 'hired']);
       });
   
       return redirect()->back()->with([
           'message' => 'Contract category-one added successfully',
           'alert-type' => 'success'
       ]);
   }
   
   private function createJournalVoucher($invoiceRef, $contractRef, $request, $type, $index = null)
   {
       $voucherData = [
           'date' => now()->format('Y-m-d'),
           'refNumber' => 0,
           'refCode' => $invoiceRef,
           'voucher_type' => 'Invoice Package1',
           'pre_connection_name' => $request->connaction, 
           'type' => $type,
           'maid_name' => $request->maid,
           'account' => $type === 'debit' ? $request->selected_customer : $request->account[$index],
           'notes' => 'No data',
           'amount' => $type === 'debit' ? $request->total_invoice : $request->amount[$index],
           'invoice_balance' => $type === 'debit' ? $request->total_invoice : $request->amount[$index],
           'contract_ref' => $contractRef,
           'created_by' => Auth::user()->name,
           'created_at' => now()
       ];
   
       General_journal_voucher::create($voucherData);
   }
   

  public function viewAllCat1Cntl(Request $request){
    
        return view('ERP.cat1.allCategory1Contracts');
    }//endMethod

// URL ajax-cat1
    public function getCategory1Contracts(Request $request)
    {
        if ($request->ajax()) {
       
            $query = categoryOne::query()
            ->leftJoin('maids_d_b_s', 'maids_d_b_s.id', '=', 'category_ones.maid_id')
            ->leftJoin('customers', 'customers.id', '=', 'category_ones.customer_id')
            ->select([
                'category_ones.id',
                'category_ones.contract_ref',
                'category_ones.invoice_ref',
                'customers.name AS customer',
                'customers.phone AS phone',
                'category_ones.customer_id',
                'category_ones.maid_id',
                DB::raw('maids_d_b_s.name AS maid'),
                'category_ones.amount',
                'category_ones.signature',
                'category_ones.contract_status',
              
                'category_ones.created_at',
                'category_ones.started_date',
                'category_ones.created_by',
            ])
            ->orderBy('category_ones.started_date', 'DESC');

    
            // Apply date filters if provided
            if ($request->has('min_date') && $request->min_date != '') {
                $query->whereDate('started_date', '>=', $request->min_date);
            }
    
            if ($request->has('max_date') && $request->max_date != '') {
                $query->whereDate('started_date', '<=', $request->max_date);
            }

            return DataTables::eloquent($query)
                ->editColumn('contract_ref', function ($row) {
                    return '<a target="_blank" href="' . url("/get/contract/summary/{$row->contract_ref}") . '">' . $row->contract_ref . '</a>';
                })
                ->editColumn('invoice_ref', function ($row) {
                    return '<a target="_blank" href="' . url("/get/invoice/cat1/{$row->invoice_ref}") . '">' . $row->invoice_ref . '</a>';
                })
                ->editColumn('customer', function ($row) {
                    return '<a href="' . url("customer/report/{$row->customer}") . '" target="_blank">' . $row->customer . '</a>';
                })

                ->filterColumn('customer', function ($query, $keyword) {
                    $query->whereHas('customerInfo', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

                ->addColumn('phone', function ($row) {
                    return '<a href="' . url("customer/soa/{$row->customer}") . '" target="_blank">' . $row->customerInfo?->phone . '</a>';
                })

                ->filterColumn('phone', function ($query, $keyword) {
                    $query->whereHas('customerInfo', function ($q) use ($keyword) {
                        $q->where('phone', 'like', "%{$keyword}%");
                    });
                })  

    
                ->editColumn('maid', function ($row) {
                    return '<a href="' . url("/maid-report/{$row->maid}") . '" target="_blank">' . $row->maid . '</a>';
                })
                ->filterColumn('maid', function ($query, $keyword) {
                    $query->whereHas('maidInfo', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })

           

                ->addColumn('actions', function ($row) {
                    $contractActions = "<a href='" . url("/get/full/categoryone-contract/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Contract</a>";
                    $transferLetter = "<a href='" . url("/transfer/letter-p1/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Transfer letter</a>";
                    $ministryReturnLetter = "<a href='" . url("/ministry-return/{$row->id}") . "' target='_blank' class='dropdown-item'>Ministry return Letter</a>";
                    $agentLetter = "<a href='" . url("/agent/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>agent</a>";
                    $signButton = $row->signature === 'No signature'
                    ? "<a href='" . url(env('sign_contract_p1') . "/{$row->id}") . "' target='_blank' class='dropdown-item'>
                            <i class='fa fa-signature'></i> Sign
                        </a>"
                    : "<span class='dropdown-item disabled'>Sign available</span>";

    
                    $deleteButton = $row->signature !== 'No signature' ?
                        "<button type='button' class='dropdown-item delete-sign' data-id='{$row->id}'>Delete sign</button>" :
                        "<span class='dropdown-item disabled'>Delete sign</span>";
    
                    $returnButton = $row->contract_status === 0 ?
                        "<p class='dropdown-item'>Returned</p>" :
                        "<button type='button' class='dropdown-item open-modal-btn' data-bs-toggle='modal' data-bs-target='#return-modal' data-maid='{$row->maid}' data-contractref='{$row->contract_ref}' data-customer='{$row->customer}' data-started_date='{$row->started_date}'>Return</button>";
    
                    return '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-warning dropdown-toggle" type="button" id="dropdownMenuButton' . $row->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $row->id . '">
                                ' . $returnButton . '
                                ' . $contractActions . '
                                ' . $transferLetter . '
                                ' . $signButton . '
                                ' . $deleteButton . '
                                ' . $ministryReturnLetter. '
                                ' . $agentLetter. '
                            </ul>
                        </div>';
                })
                ->rawColumns(['contract_ref', 'invoice_ref', 'customer', 'actions', 'maid', 'phone'])
                ->addIndexColumn()
                ->make(true);
        }
    }
    


    public function categoryOneInvoicesList(){
        $date =date('Y-m-d');
        $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();
        return view('ERP.cat1.allCategory1Invoices',compact('date','cashAndBank'));
    }// End Method

   
    //Route /ajax-invoices-cat1
    public function getCategory1Invoics(Request $request)
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
                'm.name as maid_name',
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
            ->where('gjv.voucher_type', 'Invoice Package1')
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
                    return '<a target="_blank" href="/get/invoice/cat1/'.$row->refCode.'">'.$row->refCode.'</a>';
                })
    
                ->editColumn('contract_ref', function ($row) {
                    return '<a target="_blank" href="/get/contract/summary/'.$row->contract_ref.'">'.$row->contract_ref.'</a>';
                })

                ->editColumn('maid_name', function ($row) {
                    return '<a target="_blank" href="/maid-report/'.$row->maid_name.'">'.$row->maid_name.'</a>';
                })

                ->filterColumn('maid_name', function ($query, $keyword) {
                    $query->where('m.name', 'like', "%{$keyword}%");
                })
           
                ->editColumn('receiveRef', function ($row) {
                    return $row->receiveRef ? '<a target="_blank" href="/receipt/'.$row->receiveRef.'">'.$row->receiveRef.'</a>' : '<p>No data</p>';
                })
                ->editColumn('account', function ($row) {
                    return $row->account ? '<a target="_blank" href="/customer/report/'.$row->account.'">'.$row->account.'</a>' : '<p>No data</p>';
                })

        
                ->filterColumn('account', function ($query, $keyword) {
                    $query->where('a.ledger', 'like', "%{$keyword}%");
                })
     
    
                ->rawColumns(['action','payment_status', 'refCode', 'contract_ref', 'receiveRef', 'account','maid_name'])
    
                ->make(true);
    
            return $data;
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ajaxAllTypingInvoicesCntl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }



    public function viewCateoneInvoice($refCode){

        $invDetails = General_journal_voucher::where('refCode',$refCode)->first();
        $rv = General_journal_voucher::where('receiveRef',$refCode)->where('type' , 'debit')->get();

  
        return view('ERP.cat1.template.invoiceCatOneFormat' , compact('invDetails' , 'rv'));
    }//End method



    public function viewFullContract($contract){
        $conDetails = categoryOne::where('contract_ref',$contract)->first();
    return view('ERP.cat1.template.category_one_full_contract',compact('conDetails'));
    }//End method

    public function viewFullContractArabic($contract){
        $conDetails = categoryOne::where('contract_ref',$contract)->first();
    return view('ERP.cat1.template.p1_one_full_contract_arabic',compact('conDetails'));
    }//End method



    public function viewContractSummary($contract){
        $conDetails = categoryOne::where('contract_ref',$contract)->first();
    return view('ERP.cat1.template.contract_summary' , compact('conDetails'));
    }//End method


public function viewSignPageCat1($id)
{
    $cat1Data = categoryOne::with(['customerInfo', 'maidInfo'])->findOrFail($id);

    // View-model shaped like your transfer-letter variables
    $conDetails = (object)[
        'customerInfo' => $cat1Data->customerInfo,
        'maidInfo'     => $cat1Data->maidInfo,
        'started_date' => $cat1Data->started_date,
        'created_at'   => $cat1Data->created_at,
        'signature'    => $cat1Data->signature ?? null, // if already stored
        'customer'     => $cat1Data->customer,          // fallback strings
    ];

    return view('ERP.cat1.signatureCat1', compact('cat1Data', 'conDetails'));
}



    public function saveSignatureCat1(Request $request)
    {
        $request->validate([
            'signature' => 'required',
            'id' => 'required|exists:category_ones,id'
        ]);
    
        if ($request->input('signature')) {
            $signatureRecord = categoryOne::find($request->id); 
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
                                'message' => 'Maid not found'
                            ], 404);
                        }
                    }
                
                    // Decode the Base64 string
                    $base64Image = $request->input('signature');
                    $imageData = explode(',', $base64Image)[1]; // Remove the prefix
                    $decodedData = base64_decode($imageData);
    
                    // Save the signature in the folder 'ahlia_p1signature'
                    $folder = 'ahlia_p1signature/'; // Define the folder name
                    $fileName = $folder . uniqid('signature_') . '.png'; // Include folder in the file path
    
                    // Store the file on S3
                    $path = Storage::disk($disk)->put($fileName, $decodedData);
    
                    // Check if the path is empty
                    if (!$path) {
                        Log::error("File upload to S3 failed.");
                        return response()->json(['message' => 'File upload to S3 failed'], 500);
                    }
    
                    $signatureUrl = Storage::disk($disk)->url($fileName);
                    $signatureRecord->signature = $signatureUrl;
                    $signatureRecord->save();
    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Signature saved successfully'
                   
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Signature already exists'
                ], 400);
            }
        }
    }
    

    public function deletSignCat1(Request $request)
    {
        $signatureRecord = categoryOne::find($request->id);
        if (!$signatureRecord) {
            return response()->json(['success' => false, 'message' => 'Signature record not found.'], 404);
        }
    
        $disk = 'beta';
        $sign_url = $signatureRecord->signature;
        $normalized_url = str_replace("\\", "/", $sign_url);
        $urlParts = parse_url($normalized_url);
        
        // Assuming the URL contains the folder 'ahlia_p1signature', extract the path
        $pathParts = explode('/', ltrim($urlParts['path'], '/'));
        $folder = 'ahlia_p1signature/';
        $key = $folder . end($pathParts);  // Append folder to key for S3 path
    
        // Check if the file exists on S3
        if (Storage::disk($disk)->exists($key)) {
            // Delete the file from S3
            if (Storage::disk($disk)->delete($key)) {
                // Update the signature in the database
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
    
    // url /transfer/letter-p1/{contract}
    public function viewTransLetterP1($contract){
        $conDetails = categoryOne::where('contract_ref',$contract)->first();
        return view('ERP.cat1.template.transfer_letter_p1' , compact('conDetails'));
    }

  // url /agent/{contract}
    public function viewAgentContract($contract) {
        $con = categoryOne::where('contract_ref',$contract)->first();
        return view('ERP.cat1.template.agent_contract' , compact('con'));
    }   


}

