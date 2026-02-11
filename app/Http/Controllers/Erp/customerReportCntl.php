<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\General_journal_voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\Customer;
use App\Models\categoryOne;
use App\Models\maidReturnCat1;
use App\Models\Category4Model;
use App\Models\Pre_connection_invoiceDB;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use DataTables;
use Auth;
use App\Models\customerAttach;
use Illuminate\Support\Facades\DB;
use App\Models\UpcomingInstallment;
use App\Models\customerAdvance;
use App\Models\registerComplaint;
use App\Models\Signature;

class customerReportCntl extends Controller
{

            // /cus-comp/{name}
            public function pageCusComplaint($name) {
                return view('ERP.customers.customer_complaint', compact('name'));
            }
        
           // url /cus-comp-list/{name}
            public function tableComplaint(Request $request , $name){
                if ($request->ajax()) {

                    $customerId = Customer::where('name', $name)->value('id');
                   $complaints = registerComplaint::with(['maidRelation:id,name' , 'customerRelation:id,name'])
                                ->orderBy('created_at', 'desc')
                                ->where('customer_id', $customerId);

                  return DataTables::of($complaints)
                          
                          ->addIndexColumn()
                          ->addColumn('action_taken', function ($row) {
          
                              $actions = json_decode($row->action_taken, true) ?? []; 
                              $listItems = '';
              
                              foreach ($actions as $action) {
                                  $listItems .= '<li>' . htmlspecialchars($action) . '</li>';
                              }
              
                              return '<ul class="action-list">' . $listItems . '</ul>';
                          })

                           ->addColumn('maid_name', function ($row) {
                                    return $row->maidRelation?->name ?? '-';
                                })
                           ->addColumn('customer_name', function ($row) {
                                    return $row->customerRelation?->name ?? '-';
                                })

                          ->filterColumn('maid_name', function ($q, $keyword) {
                              $q->whereHas('maidRelation', function ($sub) use ($keyword) {
                                  $sub->where('name', 'like', "%{$keyword}%");
                              });
                          })
                          ->filterColumn('customer_name', function ($q, $keyword) {
                              $q->whereHas('customerRelation', function ($sub) use ($keyword) {
                                  $sub->where('name', 'like', "%{$keyword}%");
                              });
                          })
                          ->addColumn('action', function ($row) {
                              return '<button class="btn btn-blue edit-notify-btn" data-id= " ' .$row->id. '"  >Action</button> 
                                    ';
                                    
                          })
          
                          ->rawColumns(['action_taken', 'action'])
                          ->make(true);
          
                };
              }
              
           public function tableNotification(Request $request , $name){
            if ($request->ajax()) {

                $customerId = Customer::where('name', $name)->value('id');
               
              $complaints = registerComplaint::orderBy('created_at', 'desc')
                            ->where('customer_id', $customerId);

              return DataTables::of($complaints)
                      
                      ->addIndexColumn()
                      ->addColumn('action_taken', function ($row) {
      
                          $actions = json_decode($row->action_taken, true) ?? []; 
                          $listItems = '';
          
                          foreach ($actions as $action) {
                              $listItems .= '<li>' . htmlspecialchars($action) . '</li>';
                          }
          
                          return '<ul class="action-list">' . $listItems . '</ul>';
                      })
                      ->addColumn('action', function ($row) {
                          return '<button class="btn btn-blue edit-notify-btn" data-id= " ' .$row->id. '"  >Action</button>
                                  <button class="btn btn-danger delete-notify-btn" data-id= " ' .$row->id. '"  >Delete</button>';
                      })
      
                      ->rawColumns(['action_taken', 'action'])
                      ->make(true);
      
            };
          }

   
   // /customer/jv 
    public function makeCustomerJV(Request $request)
    {
        $validatedData = $request->validate([
            'maid_name' => 'nullable|string',
            'post_type' => 'required|string|in:debit,credit',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'customer_name' => 'required|exists:customers,name',
            'other_account' => 'required|string',
            'voucher_type' => 'required|string',
            'notes' => 'nullable|string',
        ]);
    
        $random = Str::random(8);

    
        try {
            $result = DB::transaction(function () use ($validatedData, $random) {
                $debitOrCredit = $validatedData['post_type'] === 'debit' ? 'credit' : 'debit';
               
                $refNumber = 0;
                if ($validatedData['voucher_type'] === 'Payment Voucher') {
                    $maxRefNumber = General_journal_voucher::where('voucher_type', 'Payment Voucher')
                        ->max('refNumber');
                    $refNumber = $maxRefNumber ? $maxRefNumber + 1 : 1;
                }
    
                // Create customer entry
                $debit = General_journal_voucher::create([
                    'date' => $validatedData['date'],
                    'amount' => $validatedData['amount'],
                    'refNumber' => $refNumber,
                    'notes' => $validatedData['notes'],
                    'type' => $validatedData['post_type'],
                    'account' => $validatedData['customer_name'],
                    'voucher_type' => $validatedData['voucher_type'],
                    'refCode' => $random,
                    'maid_name' => $validatedData['maid_name'] ?? 'No maid',
                    'created_by' => Auth::user()->name,
                    'created_at' => Carbon::now(),
                ]);
    
                // Create other entry
                $credit = General_journal_voucher::create([
                    'date' => $validatedData['date'],
                    'amount' => $validatedData['amount'],
                    'refNumber' => $refNumber,
                    'notes' => $validatedData['notes'],
                    'type' =>   $debitOrCredit ,
                    'account' => $validatedData['other_account'],
                    'voucher_type' => $validatedData['voucher_type'],
                    'refCode' => $random,
                    'maid_name' => $validatedData['maid_name'] ?? 'No maid',
                    'created_by' => Auth::user()->name,
                    'created_at' => Carbon::now(),
                ]);
    
                return ['debit' => $debit, 'credit' => $credit];
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Journal voucher created successfully.',
                'data' => $result,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    // /customer/report/{name}
    public function getCustomerp1Report($name) {
        return view('ERP.customers.customer_report', compact('name'));
    }
   
    // /customer/report/p4/{name}
    public function getCustomerp4Report($name) {
        return view('ERP.customers.customer_report_p4', compact('name'));
    }

        // /installment-p4-make/{name}
    public function pageInstallment($name) {
            return view('ERP.customers.customer_installment', compact('name'));
        }
    


    // /customer/make/p1/{name}
    public function pageMakeP1($name) {
        
        $today=date('Y-m-d'); 
     $trialEnd = (new \DateTime($today))->add(new \DateInterval('P7D'))->format('Y-m-d');
        $maids = MaidsDB::where('maid_status', 'approved')
        ->whereNull('maid_booked')
        ->get();
        $selectConnection = Pre_connection_invoiceDB::where('group','category1')->select('invoice_connection_name')->groupBy('invoice_connection_name')->get();
        $twoYearsLater = (new \DateTime($today))->add(new \DateInterval('P2Y'))->format('Y-m-d');
  
        return view('ERP.customers.generate_p1', compact('name','maids','today','selectConnection','twoYearsLater','trialEnd'));
    }

     // /customer/make/p4/{name}
    public function pageMakeP4($name){

        $today=date('Y-m-d'); 
        $randomRefNumber = Str::random(5);
        $maids = MaidsDB::where('maid_status', 'approved')
        ->whereNull('maid_booked')
        ->get();

        return view('ERP.customers.generate_p4',compact('name','today','randomRefNumber','maids'));
    }
    
   // page/invoices/{name}
   public function pageCustomerInvoice($name){

    $date= date('Y-m-d');
    $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();
            
    return view('ERP.customers.invoices' , compact('name','cashAndBank','date' ) );

   }     
   
   // page/customer/attachment/{name}
   public function pagecustomerAttachment($name){
       
      return view('ERP.customers.attachment',compact('name'));
   }

   // customer/attach{$name}
public function tableAttachment($name, Request $request)
{
    try {
        // Build with Query Builder only
        $query = DB::table('customer_attaches as ca')
            ->join('customers as c', 'c.id', '=', 'ca.customer_id')
            ->where('c.name', $name)
            ->select([
                'ca.id',
                'c.name as customer_name',
                'ca.note',
                'ca.file_path',
                'ca.created_at',
                'ca.created_by',
            ])
            ->orderBy('ca.created_at', 'desc');

        return DataTables::of($query)
            ->addColumn('file_path', function ($row) {
                $href = e($row->file_path);
                $label = e($row->note ?? 'View');
                return '<a target="_blank" href="'.$href.'" style="color: red; text-decoration: none;">'.$label.'</a>';
            })
            ->rawColumns(['file_path'])
            ->addIndexColumn()
            ->make(true);

    } catch (\Throwable $e) {
        Log::error('Error in tableAttachment:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}



    public function getContractsTableCustomerReport(Request $request, $name)
{
    try {
        $customer = Customer::where('name', $name)->first();
        $query = categoryOne::with(['returnInfo', 'maidInfo:id,name' , 'customerInfo:id,name'])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('min_date')) {
            $query->whereDate('created_at', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $query->whereDate('created_at', '<=', $request->max_date);
        }

        return DataTables::eloquent($query)
            ->editColumn('contract_ref', function ($row) {
                return '<a target="_blank" href="' . url("/get/contract/summary/{$row->contract_ref}") . '" style="color: red; text-decoration: none;">' . e($row->contract_ref) . '</a>';
            })
            ->editColumn('invoice_ref', function ($row) {
                return '<a target="_blank" href="' . url("/get/invoice/cat1/{$row->invoice_ref}") . '" style="color: red; text-decoration: none;">' . e($row->invoice_ref) . '</a>';
            })
            ->addColumn('id', function ($row) {
                return '<a href="' . url("customer/report/{$row->customer}") . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->customer) . '</a>';
            })
            ->editColumn('maid', function ($row) {
                $maidName = $row->maidInfo?->name ?? '';
                if ($maidName === '') return '';
                return '<a href="' . url("/maid-report/" . $maidName) . '" target="_blank" style="color: red; text-decoration: none;">' . e($maidName) . '</a>';
            })

            ->filterColumn('maid', function ($q, $keyword) {
                $q->whereHas('maidInfo', function ($sub) use ($keyword) {
                    $sub->where('name', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('date_return', function ($row) {
                return $row->returnInfo?->returned_date ?? 'No Return Info';
            })
            ->addColumn('reason', function ($row) {
                return $row->returnInfo?->reason ?? 'No Return Info';
            })
            ->addColumn('actions', function ($row) {
                $maidName = $row->maidInfo?->name ?? '';
                $customerName = $row->customerInfo?->name ?? '';
             
                $hasPassport = Signature::where('customer_name', $customerName)
                        ->where('maid_name', $maidName)
                        ->exists() ? 'yes' : 'no';

                   $passportVal = CategoryOne::where('contract_ref', $row->contract_ref)->value('maid_passport') ?? '';

                    $editPassportBtn = "
                        <button type='button'
                                class='dropdown-item open-passport-btn'
                                data-bs-toggle='modal'
                                data-bs-target='#passport-modal'
                                data-refcode='{$row->contract_ref}'
                                data-passport='" . e($passportVal) . "'>
                            Edit passport status
                        </button>
                    ";
                    $editContract = "
                        <button type='button' class='dropdown-item edit-modal-btn'
                                data-bs-toggle='modal'
                                data-bs-target='#edit-modal'
                                data-id='" . e($row->id ?? '') . "'
                                data-started_date='" . e($row->started_date ?? '') . "'
                                data-contractref='" . e($row->contract_ref ?? '') . "'
                                data-end_date='" . e($row->ended_date ?? '') . "'
                                data-return_date='" . e($row->returnInfo?->returned_date ?? '') . "'
                                data-return-note='" . e($row->returnInfo?->reason ?? '') . "'
        
                                >Edit contract</button>";

                               

                                        

                $contractActions = "<a href='" . url("/get/full/categoryone-contract/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Contract</a>";
                $transferLetter = "<a href='" . url("/transfer/letter-p1/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Demo Agreement letter</a>";
                $ministryReturnLetter = "<a href='" . url("/ministry-return/{$row->id}") . "' target='_blank' class='dropdown-item'>Ministry return Letter</a>";
                $signButton = $row->signature === 'No signature'
                    ? "<a href='" . url(env('sign_contract_p1') . "/{$row->id}") . "' target='_blank' class='dropdown-item'><i class='fa fa-signature'></i> Sign</a>"
                    : "<span class='dropdown-item disabled'>Sign available</span>";
                $deleteButton = $row->signature !== 'No signature'
                    ? "<button type='button' class='dropdown-item delete-sign' data-id='{$row->id}'>Delete sign</button>"
                    : "<span class='dropdown-item disabled'>Delete sign</span>";
        
                $returnButton = $row->contract_status === 0
                        ? "<p class='dropdown-item'>Returned</p>"
                        : "<button type='button' class='dropdown-item open-modal-btn'
                                data-bs-toggle='modal'
                                data-bs-target='#return-modal'
                                data-maid='" . e($maidName) . "'
                                data-contractref='{$row->contract_ref}'
                                data-customer='" . e($customerName) . "'
                                data-started_date='{$row->started_date}'        
                                data-haspassport='{$hasPassport}'>Return</button>";
                                
          


                return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light-primary dropdown-toggle" type="button" id="dropdownMenuButton' . $row->id . '" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $row->id . '">
                            ' . $returnButton . '
                            ' . $contractActions . '
                            ' . $transferLetter . '
                            ' . $signButton . '
                            ' . $deleteButton . '
                            ' . $ministryReturnLetter . '
                            ' . $editPassportBtn . '
                            ' . $editContract . '

                           
                        </ul>
                    </div>';
            })
            ->rawColumns(['contract_ref', 'invoice_ref', 'id', 'actions', 'maid', 'reason', 'date_return'])
            ->addIndexColumn()
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error in getContractsTableCustomerReport:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}


// post p1-update
public function p1Update(Request $request)
{
    $request->validate([
        'contract_id'   => 'required|exists:category_ones,id',
        'started_date'  => 'required|date',
        'ended_date'    => 'nullable|date',
        'return_date' => 'nullable|date',
        'reason'        => 'nullable|string|max:2000',
    ]);

    Log::info($request->all());

    if (Auth::user()->group !== "accounting") {
        return response()->json([
            'error'   => true,
            'message' => 'Only accountant allowed to edit contract'
        ], 422);
    }

    try {
        $contract = CategoryOne::with('returnInfo')
            ->findOrFail($request->input('contract_id'));

        // ✅ Update contract
        $contract->update([
            'started_date' => $request->input('started_date'),
            'ended_date'   => $request->input('ended_date'),
            'updated_by'   => Auth::user()->name,
        ]);
        // ✅ Update return info (only if exists)
        if ($contract->returnInfo && ($request->filled('return_date') || $request->filled('reason'))) {
            $contract->returnInfo->update([
                'returned_date' => $request->input('return_date'),
                'reason'        => $request->input('reason'),
                'updated_by'    => Auth::user()->name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contract updated successfully!',
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update contract.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



// /p4/customer/{name}
public function p4ContractReport(Request $request, $name)
{
    try {
        $customer = Customer::where('name', $name)->first();
        $query = Category4Model::with([
                'returnInfo',
                'maidInfo:id,name,nationality', 
                'customerInfo:id,name,phone'
            ])
            ->select([
                'id',
                'customer_id',
                'maid_id',       
                'Contract_ref',
                'signature',
                'contract_status',
                'date',          
                'created_at',
                'created_by',
                'updated_by',
                'extra',
                'note',
           
            ])
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('min_date')) {
            $query->whereDate('created_at', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $query->whereDate('created_at', '<=', $request->max_date);
        }

        return DataTables::eloquent($query)
            ->addColumn('created_at', function ($row) {
                return optional($row->created_at)->format('Y-m-d H:i:s');
            })
            ->addColumn('customer', function ($row) {
                return '<a href="' . url("customer/report/{$row->customer}") . '" target="_blank">' . e($row->customer) . '</a>';
            })
            ->editColumn('maid', function ($row) {
                $maidName = $row->maidInfo?->name ?? '';
                if ($maidName === '') return '';
                return '<a href="' . url('/maid-report/' . $maidName) . '" target="_blank">' . e($maidName) . '</a>';
            })
            ->filterColumn('maid', function ($q, $keyword) {
                $q->whereHas('maidInfo', function ($sub) use ($keyword) {
                    $sub->where('name', 'like', "%{$keyword}%");
                });
            })
             ->filterColumn('nationality', function ($q, $keyword) {
                $q->whereHas('maidInfo', function ($sub) use ($keyword) {
                    $sub->where('nationality', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('contract', function ($row) {
                return '<a href="' . url("/category4/contract-bycontract/{$row->Contract_ref}") . '" target="_blank">' . e($row->Contract_ref) . '</a>';
            })
            ->addColumn('date_return', function ($row) {
                return $row->returnInfo?->returned_date ?? 'No Return Info';
            })
            ->addColumn('reason', function ($row) {
                return $row->returnInfo?->reason ?? 'No Return Info';
            })
            ->addColumn('nationality', function ($row) { 
               return $row->maidInfo->nationality;
            })

            ->addColumn('working_days', function ($row) {
                // Use `date`; if empty, fallback to `created_at`
                $startBase = $row->date ?: $row->created_at;
                if (!$startBase) return '<a>—</a>';

                $startDate = Carbon::parse($startBase);
                $endDate   = $row->returnInfo ? Carbon::parse($row->returnInfo->returned_date) : Carbon::today();
                $days      = $startDate->diffInDays($endDate);

                return '<a>' . $days . ' days</a>';
            })
            ->addColumn('extra', function ($row) {
                return e($row->extra ?? '');
            })
            ->addColumn('note', function ($row) {
                return e( $row->note ?? '');
            })
            ->addColumn('action', function ($row) {
                $maidName = $row->maidInfo?->name ?? '';
                $customerName = $row->customerInfo?->name ?? '';
                $phone = $row->customerInfo?->phone ?? '';
                $btns = '<div class="dropdown">
                    <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        Action
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

                if ((int)$row->contract_status === 0) {
                    $btns .= '
                        <li><p class="dropdown-item text-muted">Returned</p></li>
                        <li><button type="button" class="dropdown-item open-comp-modal-btn" data-bs-toggle="modal" data-bs-target="#comp_modal" data-maid="' . e($maidName) . '" data-contractref="' . e($row->Contract_ref) . '" data-customer="' . e($customerName) . '"><i class="fa fa-book"></i> Add complaint</button></li>
                        <li><a target="__blank" href="/copy/upcoming/' . e($row->Contract_ref) . '" class="dropdown-item"><i class="fa fa-copy"></i> Copy</a></li>
                        <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                        <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                        <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>
                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-bs-toggle="modal" data-bs-target="#start_date_modal" data-id="' . $row->id . '"><i class="fa fa-clock"></i> Edit Start date</button></li>
                    ';
                } else {
                    $btns .= '
                        <li><button type="button" class="dropdown-item open-modal-btn" data-bs-toggle="modal" data-bs-target="#return_modal" data-maid="' . e($maidName) . '" data-contractref="' . e($row->Contract_ref) . '" data-customer="' . e($customerName) . '" data-phone="' . e($phone) . '"><i class="fa fa-undo"></i> Return</button></li>
                        <li><button type="button" class="dropdown-item open-comp-modal-btn" data-bs-toggle="modal" data-bs-target="#comp_modal" data-maid="' . e($maidName) . '" data-contractref="' . e($row->Contract_ref) . '" data-customer="' . e($customerName) . '"><i class="fa fa-book"></i> Add complaint</button></li>
                        <li><a target="__blank" href="/edit-upcoming-installment/' . e($row->Contract_ref) . '" class="dropdown-item"><i class="fa fa-cog"></i> Edit</a></li>
                        <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                        <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                        <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>
                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-bs-toggle="modal" data-bs-target="#start_date_modal" data-id="' . $row->id . '"><i class="fa fa-clock"></i> Edit Start date</button></li>
                    ';

                    if ($row->signature === 'No signature') {
                        $btns .= '
                            <li><a href="' . url(env('sign_contract') . "/" . $row->id) . '" target="_blank" class="dropdown-item">
                                <i class="fa fa-signature"></i> Sign
                            </a></li>';
                    } else {
                        $btns .= '
                            <li><button type="button" class="dropdown-item delete-sign" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete Signature</button></li>';
                    }
                }

                $btns .= '</ul></div>';
                return $btns;
            })
            ->filterColumn('maid', function ($q, $keyword) {
                $q->whereHas('maidInfo', function ($sub) use ($keyword) {
                    $sub->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action','customer','maid','contract','date_return','working_days'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error in p4ContractReport:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

        // /customer/soa/{name}
public function getCustomerSOA(string $name) {

            $customer = DB::table('customers')
                ->select('ledger_id', 'name', 'id')
                ->where('name', $name)
                ->first()?->ledger_id;

            $balance = General_journal_voucher::calculateCustomerBalanceByLedgerId($customer);
            return view('ERP.customers.soa', compact('name', 'balance' ));
        }

        //soa/customer{name}
public function soa($name, Request $request)
{
    try {
        // 1) Find the customer's ledger_id
        $customer = DB::table('customers')
            ->select('ledger_id')
            ->where('name', $name)
            ->first();

        if (!$customer || !$customer->ledger_id) {
            return response()->json([
                'error' => 'No ledger found for this customer'
            ], 404);
        }

        $ledgerId = (int) $customer->ledger_id;

        // 2) Pull rows for that ledger with SQL-computed running balance
        //    (MySQL 8+ / MariaDB 10.2+)
        $rows = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('all_account_ledger__d_b_s as l', 'gjv.ledger_id', '=', 'l.id')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'gjv.maid_id')
            ->where('gjv.ledger_id', $ledgerId)
            ->selectRaw("
                gjv.id,
                gjv.date,
                gjv.ledger_id,
                l.ledger as account,
                m.name as maid_name,
                gjv.notes,
                gjv.amount,
                gjv.pre_connection_name,
                gjv.refCode,
                gjv.voucher_type,
                gjv.created_at,
                gjv.creditNoteRef,
                CASE WHEN gjv.type = 'credit' THEN gjv.amount ELSE 0 END AS credit,
                CASE WHEN gjv.type = 'debit'  THEN gjv.amount ELSE 0 END AS debit,
                SUM(CASE WHEN gjv.type = 'debit' THEN gjv.amount ELSE -gjv.amount END)
                    OVER (
                        PARTITION BY gjv.ledger_id
                        ORDER BY gjv.date, gjv.id
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                    ) AS running_balance
            ")
            ->orderBy('gjv.date', 'asc')
            ->orderBy('gjv.id', 'asc')
            ->get();
            
        return DataTables::of($rows)
            ->addIndexColumn()
            ->editColumn('refCode', function ($row) {
                $ref = e($row->refCode);
                return '<a href="/view/jv/selected/' . $ref . '" target="_blank">' . $ref . '</a>';
            })
            ->addColumn('running_balance', function ($row) {
                return number_format((float)$row->running_balance, 2);
            })
            ->rawColumns(['refCode'])
            ->make(true);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'An error occurred while processing the request: ' . $e->getMessage()
        ], 500);
    }
}

// customer/invoices/{name}
public function customerInvoices(Request $request, $name)
{
    try {
        // 1) Resolve customer's ledger_id from their name
        $ledgerId = DB::table('customers')
            ->where('name', $name)
            ->value('ledger_id');

        if (!$ledgerId) {
            return response()->json(['error' => 'No ledger_id found for this customer.'], 404);
        }

        // 2) Build query by ledger_id
        $query = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'gjv.maid_id')
            ->leftJoin('all_account_ledger__d_b_s as l', 'gjv.ledger_id', '=', 'l.id')
            ->select(
                'gjv.id',
                'gjv.date',
                'gjv.created_at',
                'gjv.voucher_type',
                'gjv.contract_ref',
                'gjv.refCode',
                'l.ledger as account',
                'l.ledger as ledger_name',
                DB::raw('m.name as maid_name'),
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
            ->where('gjv.ledger_id', $ledgerId)
            ->where('gjv.type', 'debit')
            ->whereIn('gjv.voucher_type', ['Invoice Package1', 'Invoice Package4', 'invoice', 'Typing Invoice'])
            ->orderBy('gjv.date', 'desc');

        // 3) Optional date filters (use correct alias)
        if ($request->filled('min_date')) {
            $query->whereDate('gjv.created_at', '>=', $request->min_date);
        }
        if ($request->filled('max_date')) {
            $query->whereDate('gjv.created_at', '<=', $request->max_date);
        }

        // 4) DataTables
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                if (auth()->user()->group === 'accounting') {
                    return '
                        <button type="button" class="btn btn-sm btn-light-primary open-modal-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#typing-payment-modal"
                                data-id="'.e($row->id).'"
                                data-customer="'.e($row->ledger_name).'"
                                data-invoice="'.e($row->refCode).'"
                                data-note="'.e($row->notes).'">Add Payment</button>
                        <button type="button" class="btn btn-light-primary btn-sm btn-credit-note"
                                data-bs-toggle="modal"
                                data-bs-target="#typing-credit-note-modal"
                                data-idForCustomer="'.e($row->id).'"
                                data-refCode="'.e($row->refCode).'">Credit Note</button>
                        <button type="button" class="btn btn-sm btn-light-primary btn-apply-credit"
                                data-payment="'.e($row->id).'">Apply credit</button>
                    ';
                }
                return '';
            })
            ->filterColumn('maid_name', function ($q, $keyword) {
                $q->where('m.name', 'like', "%{$keyword}%");
            })

            ->filterColumn('account', function ($q, $keyword) {
                $q->where('l.ledger', 'like', "%{$keyword}%");
            })
            ->editColumn('created_at', function ($row) {
                return \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
            })
            ->editColumn('payment_status', function ($row) {
                $statusClass = match ($row->payment_status) {
                    'Paid'    => 'badge bg-success',
                    'Pending' => 'badge btn-light-primary text-dark',
                    'Partial' => 'badge bg-info text-dark',
                    default   => 'badge bg-secondary',
                };
                return '<span class="'.$statusClass.'">'.e($row->payment_status).'</span>';
            })
            ->editColumn('refCode', function ($row) {
                return '<a href="/view/jv/selected/' . e($row->refCode) . '" target="_blank">' . e($row->refCode) . '</a>';
            })
            ->editColumn('voucher_type', function ($row) {
                $url = match ($row->voucher_type) {
                    'Invoice Package1' => '/get/invoice/cat1/' . $row->refCode,
                    'Invoice Package4' => '/get/invoice/cat4/' . $row->refCode,
                    'Typing Invoice'   => '/invoice/typing/' . $row->refCode,
                    'invoice'          => '/no-contract-invoice/' . $row->refCode,
                    default            => '/view/jv/selected/' . $row->refCode,
                };
                return '<a href="' . e($url) . '" target="_blank">' . e($row->voucher_type) . '</a>';
            })
            ->editColumn('receiveRef', function ($row) {
                return $row->receiveRef
                    ? '<a target="_blank" href="/receipt/'.e($row->receiveRef).'">'.e($row->receiveRef).'</a>'
                    : '<p>No data</p>';
            })
            ->rawColumns(['action','voucher_type','payment_status','refCode','receiveRef'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error in customerInvoices:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}
    // url /installment-p4/{name}
      public function datatableinstallment(Request $request, $name) {
            try {
               
                $customer = Customer::where('name', $name)->first();
                $query = UpcomingInstallment::whereHas('contractRef', function ($query) {
                    $query->where('contract_status', 1);
                }) 
                ->where('invoice_status', 0)
                ->where('customer_id', $customer->id)
                ->with(['contractRef.maidInfo:id,name,salary','customerInfo:id,name'])
                ->orderBy('accrued_date', 'asc');
    
                            if ($request->has('min_date') && $request->min_date != '') {
                                $query->whereDate('accrued_date', '>=', $request->min_date);
                            }
                    
                            if ($request->has('max_date') && $request->max_date != '') {
                                $query->whereDate('accrued_date', '<=', $request->max_date);
                            }
        
                return DataTables::of($query)
                    ->addColumn('maid_name', function ($row) {
                        return '<a href="' . url("/maid-report/{$row->contractRef->maidInfo->name}") . '" target="_blank" style="color: red; text-decoration: none;">' . $row->contractRef->maidInfo->name . '</a>';
                    })
    
                    ->editColumn('contract', function ($row) {
                        return '<a href="' . url("/edit-upcoming-installment/{$row->contract}") . '" target="_blank" style="color: red; text-decoration: none;">' . $row->contract. '</a>';
                    })
               
                    ->addColumn('meta', function ($row) {
                        return '<button class="generate-invoice  btn btn-secondary btn-sm" 
                            data-amount="' . $row->amount . '"
                            data-salary="' . $row->contractRef->maidInfo->salary . '"
                            data-customer="' . $row->customerInfo->name. '"
                            data-date="' . $row->accrued_date . '"
                            data-maid="' . $row->contractRef->maidInfo->name. '"
                            data-note="' . $row->note . '"
                            data-cheque="' . $row->cheque . '"
                            data-contract="' . $row->contract . '"
                            data-id ="' . $row->id . '"   
                            >
                            Invoice
                        </button>';
                    })
    
                    ->addColumn('custom', function ($row) {
                        return '<button class="customized-invoice  btn btn-secondary btn-sm"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#custom-modal" 
                                    data-id ="' . $row->id . '"        
                                    >
                                    customized
                        </button>';
                    })
    
                    
                    ->rawColumns(['maid_name','meta','custom','contract'])
                    ->make(true);
        
            } catch (\Exception $e) {
                Log::error('Error in from server:', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Internal Server Error'], 500);
            }
        }


        //adv-customer/{name}
        public function tableCustomerAdv(Request $request , $name)
        {
            if ($request->ajax()) {
                $customerId = Customer::where('name', $name)->value('id');
                $query = customerAdvance::with('customerInfo')
                ->where('customer_id', $customerId);

                if ($request->has('min_date') && $request->min_date != '') {
                    $query->whereDate('created_at', '>=', $request->min_date);
                }
        
                if ($request->has('max_date') && $request->max_date != '') {
                    $query->whereDate('created_at', '<=', $request->max_date);
                }
                
                $query->orderBy('created_at', 'desc');
    
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('customer', function ($row) {
                        return '<a href="' . url("/customer/report/p4/{$row->customer}") . '" target="_blank">' . $row->customer . '</a>';
                    })
                    ->editColumn('maid', function ($row) {
                        return '<a href="' . url("/maid-report/{$row->maid}") . '" target="_blank">' . $row->maid . '</a>';
                    })
                    ->editColumn('ref', function ($row) {
                        return '<a href="' . url("/receipt/{$row->ref}") . '" target="_blank">' . $row->ref . '</a>';
                    })
                    ->addColumn('phone_number', function ($row) {
                        return '<a href="' . url("/customer/soa/{$row->customer}") . '" target="_blank">' . $row->customerInfo->phone . '</a>';
                    })
                    
                    ->addColumn('action', function ($row) {
                        if (auth()->user()->group === 'accounting') {
                        return '<button type="button" class="btn btn-primary btn-sm receive-advance-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#receive-advance-modal" 
                                    data-id="' . $row->id . '">Receive Advance</button>';
                        }
                    })
           
    
          
                
                    ->rawColumns(['action', 'customer', 'maid', 'phone_number', 'ref'])
                    ->make(true);
            }

            return view('ERP.customers.adv', compact('name'));
        }
        
        
        
}


  

