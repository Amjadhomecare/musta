<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\General_journal_voucher;
use App\Models\MaidsDB;
use App\Models\categoryOne;
use App\Models\maidReturnCat1;
use App\Models\Customer;
use App\Models\Credit_memo;
use App\Models\Arrival;
use App\Models\release;
use App\Models\ReturnedMaid;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Category4Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use DataTables;
use Auth;
use PDF;
use App\Services\S3FileService;




class complainCntl extends Controller
{
    public function formCreditMemo (){

        return view('ERP.complaint.form_credit_memo');
    }

    public function getContractDetailsCat1($contractRef) {
        $contract = categoryOne::with(['returnInfo', 'maidInfo:id,name', 'customerInfo:id,name'])
        ->where('contract_ref', $contractRef)->first();

        return response()->json([
            'customer' => $contract->customerInfo->name,
            'maid' => $contract->maidInfo->name,
            'started_date' => $contract->started_date,
            'return_date' => $contract['returnInfo']?->returned_date,
            'type' => $contract->category
            
        ]);
    }

   // URL /ajax-cat4/
    public function getContractDetailsCat4($contractRef) {
        $contract = Category4Model::with(['returnInfo', 'maidInfo:id,name', 'customerInfo:id,name'])
        ->where('Contract_ref', $contractRef)->first();

        return response()->json([
            'customer' => $contract->customerInfo->name,
            'maid' => $contract->maidInfo->name,
            'started_date' => $contract->date,
            'return_date' => $contract['returnInfo']?->returned_date,
            'type' => $contract->category
            
        ]);
    }

    public function storeCreditMemo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'contract_ref' => 'string|unique:credit_memos',
            'note' => 'required|string',
            'amount_received' => 'required|integer',
            'amount_deduction' => 'required|integer',
            'amount_salary' => 'required|integer',
            'customer' => 'required|string',
            'maid' => 'required|string',
            'started_date' => 'required|date',
            'returned_date' => 'required|date',
            'refunded_amount' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
  
    
        $creditMemo = new Credit_memo();
        $creditMemo->memo_ref  = Str::random(8);
        $creditMemo->date = Carbon::now();
        $creditMemo->contract_ref = $request->contract_ref_p1 ?? $request->contract_ref_p4 ;
        $creditMemo->contract_type = $request->category;
        $creditMemo->note = $request->note;
        $creditMemo->customer = $request->customer;
        $creditMemo->maid = $request->maid;
        $creditMemo->started_date = $request->started_date;
        $creditMemo->returned_date = $request->returned_date;
        $creditMemo->amount_received = $request->amount_received;
        $creditMemo->amount_deduction = $request->amount_deduction;
        $creditMemo->amount_for_maid = $request->amount_salary;
        $creditMemo->refunded_amount = $request->refunded_amount;
        $creditMemo->created_by	 = Auth::user()->name; 
  
        $creditMemo->save();

        return response()->json(['status' => 'success', 'message' => 'Credit Memo created successfully!'], 200);

    }//end method


    public function getAllCreditMemo(Request $request)
    {   
        if ($request->ajax()) {
         
            $data = Credit_memo::latest('id')->get();
            
            return  DataTables::of($data)
                    ->addIndexColumn()
                  
                    ->make(true);
        }
    } // end method


    public function generateCreditMemoPDF($id)
     {
        $data = Credit_memo::findOrFail($id);
    
        $pdf = PDF::loadView('ERP.complaint.pdf_credit_memo', compact('data'));
        return $pdf->stream('credit_memo.pdf');
      }
    

    public function arrivalList(){
       

        return view('ERP.complaint.arrival_list');
    }  

// url ajax-maid/{name}
    public function ajaxGetMaidInfo($id) {
        $maid = MaidsDB::where('id', $id)->first();
    
        return response()->json([
            'nationality' => $maid->nationality,
            'agent' => $maid->agency
          
        ]);
    }//end method



public function storeMaidArrive(Request $request)
{
    $request->validate([
        'maid_id' => [
            'required',
            'integer',
            Rule::exists('maids_d_b_s', 'id')->where(function ($query) {
                $query->where('maid_status', 'pending');
            }),
            'unique:arrivals,maid_id',
        ],
        'nationality' => 'required|string',
        'agent'       => 'required|string',
    ]);

    $arrival = new Arrival();
    $arrival->maid_id     = (int) $request->maid_id;
    $arrival->nationality = $request->nationality;
    $arrival->agent       = $request->agent;
    $arrival->note        = $request->note ?? 'No note';
    $arrival->created_by  = Auth::user()->name;
    $arrival->save();

    return back()->with([
        'message'    => 'New arrival added successfully',
        'alert-type' => 'success',
    ]);
}

    public function pendingArrivalList(){
         
        $ledgers = All_account_ledger_DB::where('group','maid agent')->get();

        return view('ERP.accounting.pending_arrival',compact('ledgers'));

    }

     
    public function getPendingArrivalForApproving(Request $request)
    {   
        if ($request->ajax()) {

            $data = Arrival::with('maidInfo')->where('status',0)->get();

            return  DataTables::of($data)
                    ->addIndexColumn()
                    ->editColumn('maid_status', function ($row) {
                        return '<a href="' . url("/page/maid-finance/{$row->maidInfo->name}") . '" target="_blank">' . $row->maidInfo->maid_status . '</a>';
                    })
                  
                    ->rawColumns(['maid_status' ])
                  
                    ->make(true);
        }
    } // end method

    // url /update-maid-to-approve
    public function updateMaidToApprove (Request $request){

    DB::transaction(function () use ($request) {  

        $randomRefNumber= 'PO_'.Str::random(5); 

        $maidStatusList = Arrival::where('maid_id' , $request->maid_id )->first();
        $maid = MaidsDB::where('id', $request->maid_id )->first();

        General_journal_voucher::create([
            "date" => Carbon::now(),
            "refNumber" => 0,
            "refCode" => $randomRefNumber,
            "voucher_type" => "New arrival",
            "type" => "debit",
            "maid_name" => $request->maid_name,
            "notes" => $request->note ,
            "account" => $request->dh ? 'PRIVATE' : 'MAIDS' ,
            "amount" => $request->cost,
            "created_by" => Auth::user()->name,
            "created_at" => Carbon::now()
        ]);
    
        General_journal_voucher::create([
            "date" => Carbon::now(),
            "refNumber" => 0,
            "refCode" => $randomRefNumber,
            "voucher_type" => "New arrival",
            "type" => "credit",
            "maid_name" => $request->maid_name,
            "notes" => $request->note ,
            "account" => $request->agent_acc,
            "amount" => $request->cost,
            "created_by" => Auth::user()->name,
            "created_at" => Carbon::now()
        ]);
         
         
        $maidStatusList->status = 1;
        $maidStatusList->updated_at = Carbon::now();
        $maidStatusList->updated_by  = Auth::user()->name;
        $maidStatusList->save();

       if($request->dh ==='on'){
        $maid->maid_status = 'private';
        $maid->save();
        }else {
            $maid->maid_status = 'approved';
            $maid->save();               
        }
      });

        return response()->json([
            'status' => 'success',
            'message' => 'Maid approved successfully!',
            
        ], 201);
    
    }

    public function listReturnCat4(){

        return view('ERP.complaint.return.return_cat4');
    }

    // URL add/return/action
  public function storeMaidReturnRecordCntl(Request $request)
{
    $request->validate([
        'maidName'    => ['required', 'string'],
        'contractRef' => ['required', 'string'],
        'customer'    => ['required', 'string'],
        'reason'      => ['nullable', 'string'],
        'phone'       => ['nullable'], // boolean-like
    ]);

    try {
        $maidname   = MaidsDB::where('name', $request->maidName)->first();
        $contract   = Category4Model::where('Contract_ref', $request->contractRef)->first();
        $customerID = Customer::where('name', $request->customer)->value('id');

        if (!$maidname) {
            return response()->json(['message' => 'Maid not found', 'alert-type' => 'error'], 404);
        }
        if (!$contract) {
            return response()->json(['message' => 'Contract not found', 'alert-type' => 'error'], 404);
        }
        if (!$customerID) {
            return response()->json(['message' => 'Customer not found', 'alert-type' => 'error'], 404);
        }
        if ($maidname->maid_status === 'approved') {
            return response()->json(['message' => 'Maid Already In the Office', 'alert-type' => 'warning'], 200);
        }
        if ((int)$contract->contract_status === 0) {
            return response()->json(['message' => 'Can not return twice', 'alert-type' => 'warning'], 200);
        }

        // Write changes atomically
        DB::transaction(function () use ($request, $maidname, $contract, $customerID) {
            ReturnedMaid::create([
                'returned_date' => Carbon::now(),
                'packagetype'   => 'Category 4',
                'maid_id'       => $maidname->id,
                'contract'      => $request->contractRef,
                'customer_id'   => $customerID,
                'reason'        => $request->reason,
                'latest_invoce' => 'gggg',
                'created_by'    => Auth::user()->name,
                'created_at'    => Carbon::now(),
            ]);

            $maidname->maid_status = 'approved';
            $contract->contract_status = 0;

            $contract->save();
            $maidname->save();
        });

// ===== Send SMS through hcnextmeta.com relay =====
$sms_payload = null;

// Load survey link from config (defined in config/services.php)
$surveyBase = trim(config('services.survey_link', ''));

if (!empty($surveyBase) && !empty($request->phone)) {

    // Normalize to 9715XXXXXXXX
    $digits = preg_replace('/\D+/', '', (string) $request->phone);

    // Handle common UAE formats
    if (\Str::startsWith($digits, '009715') && strlen($digits) === 14) {
        $digits = '971' . substr($digits, 5);
    } elseif (\Str::startsWith($digits, '05') && strlen($digits) === 10) {
        $digits = '971' . substr($digits, 1);
    } elseif (\Str::startsWith($digits, '5') && strlen($digits) === 9) {
        $digits = '971' . $digits;
    }

    // Validate final format (must be 9715XXXXXXXX)
    if (\Str::startsWith($digits, '9715') && strlen($digits) === 12) {

        $surveyBase = rtrim($surveyBase, '/');
        $surveyUrl  = "{$surveyBase}/maid/{$maidname->id}?customer={$customerID}";
        $text = "Dear customer, your maid {$maidname->name} has been returned under Contract {$request->contractRef}. Thank you. Please rate her here: {$surveyUrl}";

        $relayUrl = 'https://hcnextmeta.com/api/relay/sms';

        try {
            $resp = \Http::timeout(15)->post($relayUrl, [
                'text'   => $text,
                'number' => $digits,
            ]);

            $json = $resp->json() ?? [];
            $sms_payload = [
                'sms_ok'     => (strtolower($json['Success'] ?? '') === 'true') || (bool)($json['ok'] ?? false),
                'sms_number' => $digits,
                'sms_msg'    => $json['Message'] ?? ($json['message'] ?? 'Unknown'),
                'sms_uuid'   => $json['MessageUUID'] ?? null,
            ];
        } catch (\Throwable $e) {
            $sms_payload = [
                'sms_ok'  => false,
                'sms_err' => $e->getMessage(),
            ];
        }

    } else {
        $sms_payload = [
            'sms_ok'  => false,
            'sms_err' => 'Invalid UAE mobile format',
            'raw'     => $request->phone,
        ];
    }

        } else {
            if (empty($surveyBase)) {
                \Log::info('Survey link not configured; skipping SMS.');
            } elseif (empty($request->phone)) {
                \Log::info('No phone provided in request; skipping SMS.');
            }
        }

        $resp = [
            'message'    => 'Maid Returned Successfully',
            'alert-type' => 'info',
        ];
        if ($sms_payload !== null) {
            $resp = array_merge($resp, $sms_payload);
        }

        return response()->json($resp, 200);

    } catch (\Exception $e) {
        return response()->json([
            'message'    => 'An error occurred: ' . $e->getMessage(),
            'alert-type' => 'error'
        ], 500);
    }
}
    
    public function storeMaidReturnCat1RecordCntl(Request $request) {
        DB::beginTransaction();  

    
        try {
            $maidname = MaidsDB::where('name', $request->maidName)->first();
            $contract = categoryOne::where('contract_ref', $request->contractRef)->first();
            $randomRefNumber = "par_" . Str::random(6);
            $customerID = Customer::where('name', $request->customer)->value('id');
    
            if (!$maidname) {
                return response()->json([
                    'message' => 'Maid not found',
                    'alert-type' => 'error'
                ], 404);
            }
    
            if ($maidname->maid_status == 'approved') {
                return response()->json([
                    'message' => 'Maid Already In the Office',
                    'alert-type' => 'warning'
                ], 200);
            }
    
            if ($contract->contract_status == '0') {
                return response()->json([
                    'message' => 'Cannot return twice',
                    'alert-type' => 'warning'
                ], 200);
            }
    
   
            if ($request->amount_for_com > 0) {
                General_journal_voucher::create([
                    'date' => Carbon::now(),
                    'refCode' => $randomRefNumber,
                    'refNumber' => 0,
                    'voucher_type' => 'invoice',
                    'type' => 'credit',
                    'maid_name' => $request->maidName,
                    'account' => 'PARTIAL_DEDCUTION',
                    'amount' => $request->amount_for_com,
                    'notes' => 'partial deduction',
                    'created_by' => Auth::user()->name,
                ]);
            }
    
            if ($request->amount_for_maid > 0) {
                General_journal_voucher::create([
                    'date' => Carbon::now(),
                    'refCode' => $randomRefNumber,
                    'refNumber' => 0,
                    'voucher_type' => 'invoice',
                    'type' => 'credit',
                    'maid_name' => $request->maidName,
                    'account' => 'P1_MAID_SALARY',
                    'amount' => $request->amount_for_maid,
                    'notes' => 'partial deduction',
                    'pre_connection_name' => "no connection",
                    'created_by' => Auth::user()->name,
                ]);
            }
    
            if ($request->amount_for_maid || $request->amount_for_com > 0) {
                General_journal_voucher::create([
                    'date' => Carbon::now(),
                    'refCode' => $randomRefNumber,
                    'refNumber' => 0,
                    'voucher_type' => 'invoice',
                    'type' => 'debit',
                    'maid_name' => $request->maidName,
                    'account' => $request->customer,
                    'invoice_balance' => $request->amount_for_maid + $request->amount_for_com,
                    'amount' => $request->amount_for_maid + $request->amount_for_com,
                    'notes' => 'partial deduction',
                    'pre_connection_name' => "no connection",
                    'created_by' => Auth::user()->name,
                ]);
            }
    
            maidReturnCat1::create([
                'returned_date' => Carbon::now(),
                'packagetype' => "category 1",
                'maid_id' => $maidname->id,
                'contract' => $request->contractRef,
                'customer_id' => $customerID,
                'reason' => $request->reason,
                'created_by' => Auth::user()->name,
                'created_at' => Carbon::now()
            ]);
    
            // Update maid and contract status
            $maidname->maid_status = 'approved';
            $contract->contract_status = 0;
            $contract->maid_passport = $request->passport_status ? $request->passport_status . Auth::user()->name . now() : 'No record';

    
            $contract->save();
            $maidname->save();
    
            DB::commit();
    
            return response()->json([
                'message' => 'Maid Package One Returned Successfully',
                'alert-type' => 'info'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();  
    
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
                'alert-type' => 'error'
            ], 500);
        }
    }


    public function updatePassportStatus(Request $request)
{
    $request->validate([
        'ref_code'      => 'required|string',
        'maid_passport' => 'required|string',
    ]);

    CategoryOne::where('contract_ref', $request->ref_code)
        ->update([
            'maid_passport' => $request->maid_passport . ' ' . Auth::user()->name . ' ' . now(),
        ]);

    return response()->json(['success' => true]);
}



public function ajaxListReturnCat4(Request $request)
{
    if ($request->ajax()) {

        $data = DB::table('returned_maids as rm')
            ->leftJoin('category4_models as c4', 'c4.Contract_ref', '=', 'rm.contract')
            ->leftJoin('customers as cu', 'cu.id', '=', 'rm.customer_id')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'rm.maid_id')
            ->select([
                'rm.id',
                'rm.approval',
                'rm.created_at',
                'rm.contract',
                'rm.reason',
                'rm.created_by',
                'rm.updated_by',
                'c4.date as c4_date',
                'c4.extra as category4_extra',
                'c4.note as category4_note',
                'cu.name as customer_name',
                'cu.ledger_id as customer_ledger_id',
                'm.name as maid_name',
            ]);

        if ($request->filled('min_date')) {
            $data->whereDate('rm.created_at', '>=', $request->min_date);
        }

        if ($request->filled('max_date')) {
            $data->whereDate('rm.created_at', '<=', $request->max_date);
        }

        $data->orderBy('rm.created_at', 'DESC');

        return DataTables::of($data)
            ->addColumn('cont4', fn($row) =>
                '<a>' . ($row->c4_date ?? '-') . '</a>'
            )
            ->editColumn('customer_name', fn($row) =>
                '<a href="' . url("/customer/report/p4/{$row->customer_name}") . '" target="_blank">' . $row->customer_name . '</a>'
            )
            ->editColumn('maid_name', fn($row) =>
                '<a href="' . url("/maid-report/p4/{$row->maid_name}") . '" target="_blank">' . $row->maid_name . '</a>'
            )
            ->editColumn('contract', fn($row) =>
                '<a href="' . url("/category4/contract-bycontract/{$row->contract}") . '" target="_blank">' . $row->contract . '</a>'
            )
            ->editColumn('created_at', fn($row) =>
                \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s')
            )
            ->addColumn('closing_balance', fn($row) =>
                $row->customer_ledger_id
                    ? '<a href="/customer/soa/' . $row->customer_name . '" target="_blank">' .
                        General_journal_voucher::calculateCustomerBalanceByLedgerId($row->customer_ledger_id) .
                        '</a>'
                    : '-'
            )

            ->filterColumn('maid_name', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where('cu.name', 'like', "%{$keyword}%");
            })
            ->addColumn('latest_invoice_date_cat4', function ($row) {
                $latestInvoice = General_journal_voucher::latestCat4InvoiceAndContract($row->customer_ledger_id, $row->contract);
                if ($latestInvoice) {
                    return '<a href="/customer/soa/' . $row->customer_name . '" target="_blank">' .
                        $latestInvoice->date . " | " .
                        $latestInvoice->amount . " | " .
                        $latestInvoice->refCode . " | " .
                        $latestInvoice?->creditNoteRef .
                        '</a>';
                }
                return '-';
            })
            ->rawColumns(['cont4','customer_name', 'maid_name', 'contract', 'closing_balance', 'latest_invoice_date_cat4'])
            ->addIndexColumn()
            ->make(true);
    }
}


    public function bulkUpdateApprovalReturnCat4(Request $request)
        {
            $ids = $request->input('ids');
         
            ReturnedMaid::whereIn('id', $ids)->update(['approval' => 'approved',
            'updated_by'=>Auth::user()->name

        
        ]);
            return response()->json(['success' => 'Approvals updated successfully.']);

        }//End method




    public function listReturnCat1(){

            return view('ERP.complaint.return.return_cat1');
         }//End Method 
         

public function ajaxListReturnCat1(Request $request)
{
    if ($request->ajax()) {

        $data = DB::table('maid_return_cat1s as rc1')
            ->leftJoin('customers as cu', 'cu.id', '=', 'rc1.customer_id')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'rc1.maid_id')
            ->select([
                'rc1.id',
                'rc1.approval',
                'rc1.created_at',
                'rc1.contract',
                'rc1.reason',
                'rc1.created_by',
                'rc1.updated_by',
                'cu.ledger_id as customer_ledger_id',
                'cu.name as customer_name',
                'm.name as maid_name',
            ])
            ->when($request->filled('min_date'), fn($q) =>
                $q->whereDate('rc1.created_at', '>=', $request->min_date)
            )
            ->when($request->filled('max_date'), fn($q) =>
                $q->whereDate('rc1.created_at', '<=', $request->max_date)
            )
            ->orderBy('rc1.created_at', 'DESC');

        return DataTables::of($data)
            ->addIndexColumn()

            ->addColumn('latest_invoice_date_cat1', function ($item) {
                $inv  = General_journal_voucher::latestCat1Contract($item->customer_ledger_id, $item->contract);
                $date = $inv->date ?? 'No data';
                $url  = '/page/invoices/' . $item->customer_name;
                return $date !== 'No data' ? '<a href="' . $url . '" target="_blank">' . $date . '</a>' : $date;
            })

            ->addColumn('latest_invoice_amount_cat1', function ($item) {
                $inv    = General_journal_voucher::latestCat1Contract($item->customer_ledger_id, $item->contract);
                $amount = $inv->amount ?? 'No Data';
                $url    = '/page/invoices/' . $item->customer_name;
                return $amount !== 'No Data' ? '<a href="' . $url . '" target="_blank">' . $amount . '</a>' : $amount;
            })

            ->addColumn('closing_balance', function ($item) {
                $balance = General_journal_voucher::calculateCustomerClosingBalance($item->customer_name);
                $url     = '/customer/soa/' . $item->customer_name;
                return '<a href="' . $url . '" target="_blank">' . $balance . '</a>';
            })

            ->addColumn('refund', function ($item) {
                return General_journal_voucher::latestCat1Contract($item->customer_ledger_id, $item->contract)->creditNoteRef ?? 'Old ERP';
            })

            ->addColumn('invoice', function ($item) {
                return General_journal_voucher::latestCat1Contract($item->customer_ledger_id, $item->contract)->refCode ?? '';
            })

            ->addColumn('checkbox', fn($item) =>
                '<input type="checkbox" name="ids[]" class="check-item" value="' . $item->id . '">'
            )

            ->editColumn('customer_name', fn($item) =>
                '<a href="customer/report/' . $item->customer_name . '" target="_blank">' . $item->customer_name . '</a>'
            )
            ->editColumn('maid_name', fn($item) => 
                '<a href="/page/maid/invoices/' . $item->maid_name . '" target="_blank">' . $item->maid_name . '</a>'
            )
            ->editColumn('contract', fn($item) =>
                '<a href="/page/invoices/' . $item->customer_name . '" target="_blank">' . $item->contract . '</a>'
            )
            ->filterColumn('maid_name', function ($query, $keyword) {
                $query->where('m.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('customer_name', function ($query, $keyword) {
                $query->where('cu.name', 'like', "%{$keyword}%");
            })

            ->rawColumns([
                'maid_name',
                'contract',
                'checkbox',
                'customer_name',
                'latest_invoice_date_cat1',
                'latest_invoice_amount_cat1',
                'closing_balance'
            ])
            ->make(true);
    }
}
        
        public function bulkUpdateApprovalReturnCat1(Request $request)
        {
            $ids = $request->input('ids');
         
            maidReturnCat1::whereIn('id', $ids)->update(['approval' => 'approved',
        'updated_by'=>Auth::user()->name]);
            return response()->json(['success' => 'Approvals updated successfully.']);

        }//End method


        public function pageReleaseCv(){

            return view('ERP.complaint.page_maid_release');
        }

        // Store
        public function storeMaidRelease(Request $request)
        {
            $request->validate([
                'maid_id'     => 'required|exists:maids_d_b_s,id|unique:releases,maid_id',
                'nationality' => 'required|string|max:255',
                'agent'       => 'required|string|max:255',
                'new_status'  => 'required|string|max:255',
                'note'        => 'nullable|string',
            ]);

            $maid = MaidsDB::findOrFail($request->maid_id);

            if ($maid->maid_status === 'hired') {
                return back()
                    ->withErrors(['maid' => 'The maid status should not be hired.'])
                    ->withInput();
            }

            $s3Service = new S3FileService();

            $s3Service->deletePreviousFileFromR2($maid->video_link, 'r2');
            $s3Service->deletePreviousFileFromS3($maid->video_link, 'beta');

            $release = new Release();
            $release->maid_id     = $maid->id;
            $release->nationality = $request->nationality;  
            $release->agent       = $request->agent;        
            $release->new_status  = $request->new_status;
            $release->note        = $request->note ?: 'No note';
            $release->status      = 0;
            $release->created_by  = Auth::user()->name ?? 'system';
            $release->save();

            return back()->with([
                'message'    => 'New release Added Successfully',
                'alert-type' => 'success',
            ]);
        }


        public function pendingReleaseList(){
         
            $ledgers = All_account_ledger_DB::where('group','!=' ,'customer')->get();

            return view('ERP.accounting.pending_release' , compact('ledgers'));
    
        }


public function getPendingReleaseForApproving(Request $request)
{
    if ($request->ajax()) {
        $data = DB::table('releases')
            ->leftJoin('maids_d_b_s', 'releases.maid_id', '=', 'maids_d_b_s.id')
            ->select(
                'releases.id',
                'releases.maid_id',
                'maids_d_b_s.name as maid_name',
                'maids_d_b_s.maid_status',
                'maids_d_b_s.maid_type',
                'releases.nationality',  
                'releases.agent', 
                'releases.new_status',    
                'releases.note',         
                'releases.created_by',   
                'releases.status',
                'releases.created_at'
            )
            ->where('releases.status', 0)
            ->orderBy('releases.created_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('maid_status', function ($row) {
                $maidName   = e($row->maid_name ?? '');
                $maidStatus = e($row->maid_status ?? '');
                return '<a href="' . url("/page/maid-finance/{$maidName}") . '" target="_blank">' . $maidStatus . '</a>';
            })
            ->filterColumn('maid_name', function ($query, $keyword) {
                $query->where('maids_d_b_s.name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['maid_status'])
            ->make(true);
    }
}

// /ajax-release-maid
    public function updateMaidToReleased(Request $request)
{
    DB::transaction(function () use ($request) {

        $randomRefNumber = 'd_m_' . Str::random(4);

        // 1) Get maid by ID and derive the name (keep using name in vouchers)
        $maid      = MaidsDB::findOrFail($request->maid_id);
        $maidName  = $maid->name;

        // 2) Find the release row — prefer maid_id, fallback to name for legacy rows
        $maidStatusList = Release::where('maid_id', $request->maid_id)->first();
        if (!$maidStatusList && $request->filled('maid_name')) {
            $maidStatusList = Release::where('name', $request->maid_name)->first();
        }
        if (!$maidStatusList) {
            $maidStatusList = Release::where('name', $maidName)->first();
        }

        // 3) Create vouchers (keep saving by name as before)
        General_journal_voucher::create([
            "date"          => Carbon::now(),
            "refNumber"     => 0,
            "refCode"       => $randomRefNumber,
            "voucher_type"  => "debit_memo",
            "type"          => "debit",
            "maid_name"     => $maidName,
            "notes"         => $request->note,
            "account"       => $request->agent_acc,
            "amount"        => $request->cost,
            "created_by"    => Auth::user()->name,
            "created_at"    => Carbon::now(),
        ]);

        General_journal_voucher::create([
            "date"          => Carbon::now(),
            "refNumber"     => 0,
            "refCode"       => $randomRefNumber,
            "voucher_type"  => "debit_memo",
            "type"          => "credit",
            "maid_name"     => $maidName,
            "notes"         => $request->note,
            "account"       => "MAIDS",
            "amount"        => $request->cost,
            "created_by"    => Auth::user()->name,
            "created_at"    => Carbon::now(),
        ]);

        // 4) Approve release row if found
        if ($maidStatusList) {
            $maidStatusList->status     = 1;
            $maidStatusList->updated_at = Carbon::now();
            $maidStatusList->updated_by = Auth::user()->name;
            $maidStatusList->save();
        }

        // 5) Update maid status (still by ID)
        $maid->maid_status = $request->new_status;
        $maid->save();
    });

    return response()->json([
        'status'  => 'success',
        'message' => 'Maid approved successfully!',
    ], 201);
}


        public function getReleases(Request $request)
        {
            if ($request->ajax()) {
                // Query builder version
                $data = DB::table('releases')
                    ->leftJoin('maids_d_b_s', 'releases.maid_id', '=', 'maids_d_b_s.id')
                    ->select(
                        'releases.id',
                        'releases.maid_id',
                        'maids_d_b_s.name as maid_name',
                        'maids_d_b_s.maid_type',
                        'releases.nationality',
                        'releases.agent',
                        'releases.note',
                        'releases.new_status',
                        'releases.status',
                        'releases.created_by',
                        'releases.updated_by',
                        'releases.created_at'
                    )
                    ->orderBy('releases.created_at', 'desc');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('status', function ($row) {
                        return $row->status == 1 ? 'Approved' : 'Pending';
                    })

                    ->filterColumn('maid_name', function ($query, $keyword) {
                        $query->where('maids_d_b_s.name', 'like', "%{$keyword}%");
                    })
                    ->addColumn('actions', function ($row) {
                        if ($row->status == 0) {
                            return '<button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>';
                        } else {
                            return '<button class="btn btn-warning btn-sm">Released</button>';
                        }
                    })
                    ->editColumn('maid_name', function ($row) {
                        return '<a target="_blank" href="/page/maid-finance/' . ($row->maid_name ?? '#') . '" class="edit btn-sm">'
                                . e($row->maid_name) . '</a>';
                    })
                    ->addColumn('maid_type', function ($row) {
                        return $row->maid_type ?? '';
                    })
                    ->rawColumns(['actions', 'maid_name'])
                    ->make(true);
            }

        }

            public function deleteMaidRelease($id)
            {
             
                $release =  release::findOrFail($id);

                if($release->status == 1){

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Deleted not allow!',
                        
                    ], 401);

                }
            
                $release->delete();
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Deleted successfully!',
                    
                ], 201);
            }

            // url /arrival-list
            public function getArrival(Request $request)
            {
                if ($request->ajax()) {
                    $data = DB::table('arrivals as a')
                        ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'a.maid_id')
                        ->select(
                            'a.id',
                            'a.maid_id',
                            'm.name as maid_name',
                            'a.nationality',
                            'a.agent',
                            'a.note',
                            'a.status',
                            'a.created_by',
                            'a.updated_by',
                            'a.created_at'
                        )
                        ->orderByDesc('a.created_at');

                    return DataTables::of($data)
                        ->addIndexColumn()
                        ->editColumn('status', fn($row) => $row->status == 1 ? 'Approved' : 'Pending')
                        ->addColumn('actions', function ($row) {
                            if ((int)$row->status === 0) {
                                return '<button id="delete-arrival" class="btn btn-danger btn-sm" data-id="'.$row->id.'">Delete</button>';
                            }
                            return '<button class="btn btn-warning btn-sm">Approved</button>';
                        })
                        ->filterColumn('maid_name', function ($query, $keyword) {
                            $query->where('m.name', 'like', "%{$keyword}%");
                        })
                        ->rawColumns(['actions'])
                        ->make(true);
                }
            }
        // url  /delete-arrival
            public function deletePendingArrival($id)
            {
             
                $arrival =  Arrival::findOrFail($id);

                if($arrival->status == 1){

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Deleted not allow!',
                        
                    ], 401);

                }
            
                $arrival->delete();
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Deleted successfully!',
                    
                ], 201);
            }
        
             
            // /ministry-return/{p1}
            public function ministry_return($p1){
                   $con1 = categoryOne::findOrFail($p1);
                 
                return view('ERP.complaint.return.ministry_return',compact('con1'));
            }

}