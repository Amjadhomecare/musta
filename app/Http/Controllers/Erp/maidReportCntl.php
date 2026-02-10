<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use App\Models\Category4Model;
use App\Models\categoryOne;
use App\Models\maidReturnCat1;
use App\Models\All_account_ledger_DB;
use App\Models\General_journal_voucher;
use App\Models\maid_doc_expiry;
use App\Models\MaidAttachment;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Models\AdvanceAndDedcutiotMaids;
use App\Models\ApplyVisa;
use App\Models\ApplyVisaStatusLog;




class maidReportCntl extends Controller
{


   //  /jv/maid'
 
   public function makeJV(Request $request)
{
    $validatedData = $request->validate([
        'maid_name' => 'required|exists:maids_d_b_s,name',
        'date' => 'required|date',
        'amount' => 'required|numeric',
        'account_debit' => 'required|string',
        'account_credit' => 'required|string',
        'voucher_type' => 'required|string',
        'notes' => 'nullable|string',
    ]);

    $random = Str::random(8);

    try {
        $result = DB::transaction(function () use ($validatedData, $random) {
           
            $refNumber = 0;
            if ($validatedData['voucher_type'] === 'Payment Voucher') {
                $maxRefNumber = General_journal_voucher::where('voucher_type', 'Payment Voucher')
                    ->max('refNumber');
                $refNumber = $maxRefNumber ? $maxRefNumber + 1 : 1;
            }

            // Create debit entry
            $debit = General_journal_voucher::create([
                'date' => $validatedData['date'],
                'amount' => $validatedData['amount'],
                'refNumber' => $refNumber,
                'notes' => $validatedData['notes'],
                'type' => 'debit',
                'account' => $validatedData['account_debit'],
                'voucher_type' => $validatedData['voucher_type'],
                'refCode' => $random,
                'maid_name' => $validatedData['maid_name'],
                'created_by' => Auth::user()->name,
                'created_at' => Carbon::now(),
            ]);

            // Create credit entry
            $credit = General_journal_voucher::create([
                'date' => $validatedData['date'],
                'amount' => $validatedData['amount'],
                'refNumber' => $refNumber,
                'notes' => $validatedData['notes'],
                'type' => 'credit',
                'account' => $validatedData['account_credit'],
                'voucher_type' => $validatedData['voucher_type'],
                'refCode' => $random,
                'maid_name' => $validatedData['maid_name'],
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

// URL /pl/{name}
public function POrL($name)
{
    $maid = MaidsDB::where('name', $name)->firstOrFail();

    // ── Per-ledger (exclude Accounts Receivable from Ledger Breakdown) ──
    $perLedger = General_journal_voucher::join('all_account_ledger__d_b_s as al', 'al.id', '=', 'general_journal_vouchers.ledger_id')
        ->where('general_journal_vouchers.maid_id', $maid->id)
        // Exclude common variants; add more if needed
        ->whereNotIn('al.class', ['Account Receivable', 'Accounts Receivable'])
        ->select([
            'general_journal_vouchers.ledger_id',
            'al.ledger',
            DB::raw('al.`class` as class'),
        ])
        ->selectRaw("
            SUM(
                CASE
                    WHEN al.`class` = 'Expenses' AND general_journal_vouchers.type = 'debit'  THEN -general_journal_vouchers.amount
                    WHEN al.`class` = 'Expenses' AND general_journal_vouchers.type = 'credit' THEN  general_journal_vouchers.amount
                    WHEN al.`class` = 'Revenue'  AND general_journal_vouchers.type = 'credit' THEN  general_journal_vouchers.amount
                    WHEN al.`class` = 'Revenue'  AND general_journal_vouchers.type = 'debit'  THEN -general_journal_vouchers.amount
                    ELSE
                        CASE
                            WHEN general_journal_vouchers.type = 'credit' THEN general_journal_vouchers.amount
                            ELSE -general_journal_vouchers.amount
                        END
                END
            ) as signed_total
        ")
        ->groupBy('general_journal_vouchers.ledger_id', 'al.ledger', 'class')
        ->orderBy('class')->orderBy('al.ledger')
        ->get();

    // ── Summary rollups (ORIGINAL profit logic) ──
    $summary = General_journal_voucher::join('all_account_ledger__d_b_s as al', 'al.id', '=', 'general_journal_vouchers.ledger_id')
        ->where('general_journal_vouchers.maid_id', $maid->id)
        ->selectRaw("
            SUM(
                CASE
                    WHEN al.`class` = 'Revenue'  AND general_journal_vouchers.type = 'credit' THEN  general_journal_vouchers.amount
                    WHEN al.`class` = 'Revenue'  AND general_journal_vouchers.type = 'debit'  THEN -general_journal_vouchers.amount
                    ELSE 0
                END
            ) as total_revenue,
            SUM(
                CASE
                    WHEN al.`class` = 'Expenses' AND general_journal_vouchers.type = 'debit'  THEN -general_journal_vouchers.amount
                    WHEN al.`class` = 'Expenses' AND general_journal_vouchers.type = 'credit' THEN  general_journal_vouchers.amount
                    ELSE 0
                END
            ) as total_expenses,
            SUM(
                CASE
                    WHEN al.`class` NOT IN ('Revenue','Expenses') THEN
                        CASE
                            WHEN general_journal_vouchers.type = 'credit' THEN general_journal_vouchers.amount
                            ELSE -general_journal_vouchers.amount
                        END
                    ELSE 0
                END
            ) as total_other
        ")
        ->first();

    $profit = ($summary->total_revenue ?? 0) + ($summary->total_expenses ?? 0);
    $summary->net_all = ($summary->total_revenue ?? 0) + ($summary->total_expenses ?? 0) + ($summary->total_other ?? 0);

    // ── NEW: Total P4 counting days KPI ──
    $pkg4CountingDays = DB::table('category4_models as c')
        ->leftJoin('returned_maids as r', 'r.contract', '=', 'c.Contract_ref')
        ->where('c.maid_id', $maid->id)
        ->selectRaw("COALESCE(SUM(DATEDIFF(COALESCE(r.returned_date, CURDATE()), c.date)), 0) as total_counting_days")
        ->value('total_counting_days');

    // ── NEW: Per-contract P4 summary table ──
    $pkg4Contracts = DB::table('category4_models as c')
        ->leftJoin('returned_maids as r', 'r.contract', '=', 'c.Contract_ref')
        ->where('c.maid_id', $maid->id)
        ->selectRaw("
            c.Contract_ref     as contract_ref,
            c.date             as contract_date,
            r.returned_date    as returned_date,
            DATEDIFF(COALESCE(r.returned_date, CURDATE()), c.date) AS counting_days
        ")
        ->groupBy('c.maid_id', 'c.Contract_ref', 'c.date', 'r.returned_date')
        ->orderBy('c.date', 'asc')
        ->get();

    return view('ERP.maids.pl', compact(
        'name', 'maid', 'perLedger', 'summary', 'profit',
        'pkg4CountingDays', 'pkg4Contracts'
    ));
}

// attach/maid/doc-expiry
public function  addMaidDocExpiry(Request $request)
{
    $validatedData = $request->validate([
        'maid_id' => 'required|exists:maids_d_b_s,id',
        'labor_card_expiry' => 'nullable|date',
        'passport_expiry' => 'nullable|date',
        'visa_expiry' => 'nullable|date',
        'eid_expiry' => 'nullable|date'
    ]);

 
    $maidDocExpiry = maid_doc_expiry::updateOrCreate(

        ['maid_id' => $validatedData['maid_id']],
        
        [
            'labor_card_expiry' => $validatedData['labor_card_expiry'] ?? null,
            'passport_expiry' => $validatedData['passport_expiry'] ?? null,
            'visa_expiry' => $validatedData['visa_expiry'] ?? null,
            'eid_expiry' => $validatedData['eid_expiry'] ?? null,
            'created_by' => Auth::user()->name, 
            'updated_by' => Auth::user()->name
        ]
    );

    return response()->json([
        'success' => true,
        'message' => $maidDocExpiry->wasRecentlyCreated
            ? 'Maid document expiry details created successfully.'
            : 'Maid document expiry details updated successfully.',
        'data' => $maidDocExpiry
    ], 200);
}


    // URL /maid-report/p4/{name} this for package one // Category4Model
    public function maidReportP4($name)
    {
        $maid = MaidsDB::where('name', $name)->first();
  
        return view('ERP.maids.maid_report_p4', compact('name', 'maid'));
    }


   public function ProSteps(Request $request, $name)
{
    // Find maid by name (404 if not found)
    $maid = MaidsDB::where('name', $name)->firstOrFail();

    // Latest ApplyVisa record for this maid
    $visa = ApplyVisa::where('maid_id', $maid->id)
        ->latest('id')
        ->first();

    // If no application yet, render with empty data
    if (!$visa) {
        return view('ERP.maids.maid_steps_p4', [
            'name'          => $name,
            'maid'          => $maid,
            'visa'          => null,
            'steps'         => collect(),
            'logs'          => collect(),
            'statusMap'     => [],
            'current'       => null,
            'serviceLabel'  => '—',
            'visaNote'      => '—',
            'visaDate'      => '—',
            'currentLabel'  => '—',
            'latestStatusLabel' => '—',
        ]);
    }

    // ===== Status dictionaries =====
    $fullMap = [
        0  => 'Created',
        1  => 'Pending',
        2  => 'Missing document',
        3  => 'Contract done',
        4  => 'Labor insurance done',
        5  => 'Work permit done',
        6  => 'Entry permit done',
        7  => 'Change status done',
        8  => 'Medical done',
        9  => 'EID done',
        10 => 'Visa stamp done',
        11 => 'Rejected',
   
    ];

    // Specialized compact flows
    $renewalMap      = [0 => 'Created', 1 => 'Pending', 13 => 'Renewal Done'];
    $cancellationMap = [0 => 'Created', 1 => 'Pending', 12 => 'Cancellation Done'];
    $abscondingMap   = [0 => 'Created', 1 => 'Pending', 14 => 'Absconding Done'];

    // Decide map by service
    // (service codes you used: 0=Visa renewal, 2=New visa, 3=Cancellation, 4=Absconding, 5=Other)
    switch ((int) $visa->service) {
        case 0:
            $statusMap = $renewalMap;
            break;
        case 3:
            $statusMap = $cancellationMap;
            break;
        case 4:
            $statusMap = $abscondingMap;
            break;
        default:
            $statusMap = $fullMap;
            break;
    }

    // Only fetch logs for this ApplyVisa and statuses we care about
    $wantedStatuses = array_keys($statusMap);

    $logs = ApplyVisaStatusLog::where('apply_visa_id', $visa->id)
        ->whereIn('status', $wantedStatuses)
        ->orderBy('created_at', 'asc')
        ->get();

    // Last log per status (if multiple entries for same status, take last)
    $reached = $logs->groupBy('status')->map->last();

    // Current (by timeline): highest reached status key in the chosen map
    $current = $reached->isEmpty()
        ? 0
        : $reached->keys()->max();

    // Steps for the visual stepper
    $steps = collect($wantedStatuses)->map(function ($s) use ($statusMap, $reached, $current) {
        $log = $reached->get($s);
        return [
            'value'     => $s,
            'label'     => $statusMap[$s],
            'reached'   => (bool) $log,
            'current'   => ($s === $current),
            'rejected'  => false, // not used in compact flows
            'timestamp' => $log?->created_at?->format('Y-m-d H:i'),
            'by'        => $log?->created_by,
            'comment'   => $log?->comment,
        ];
    });

    // Service label / helpers
    $serviceMap = [
        0 => 'Visa renewal',
        2 => 'New visa',
        3 => 'Cancellation',
        4 => 'Absconding',
        5 => 'Other',
    ];
    $serviceLabel = $serviceMap[$visa->service] ?? ('Service '.$visa->service);
    $visaNote     = $visa->note ?: '—';
    $visaDate     = optional($visa->date)->format('Y-m-d') ?? '—';

    // Labels:
    // - currentLabel: derived from timeline (logs)
    // - latestStatusLabel: the authoritative latest status saved on ApplyVisa (what you asked to display)
    $currentLabel = $statusMap[$current] ?? '—';

    $latestStatusValue = $visa->status; // could be null
    // Prefer the chosen map’s label; if not found, fall back to the full map; else '—'
    $latestStatusLabel = isset($latestStatusValue)
        ? ($statusMap[$latestStatusValue] ?? ($fullMap[$latestStatusValue] ?? '—'))
        : '—';

    return view('ERP.maids.maid_steps_p4', compact(
        'name',
        'maid',
        'visa',
        'steps',
        'logs',
        'statusMap',
        'current',
        'serviceLabel',
        'visaNote',
        'visaDate',
        'currentLabel',      
        'latestStatusLabel' 
    ));
}



  // URL /maid-report/{} this for package one
  public function maidReport($name)
  {
      $maid = MaidsDB::where('name', $name)->first();
      return view('ERP.maids.maid_report', compact('name', 'maid'));
  }
  

    // url /doc/maid
public function maidDocumentReport($name)
{
    // Get maid by name
    $maid = DB::table('maids_d_b_s')
        ->select('*')
        ->where('name', $name)
        ->first();

    if ($maid) {
        // Attach attachments property manually
        $maid->maidAttachment = DB::table('maid_attachments')
            ->select('*')
            ->where('maid_id', $maid->id)
            ->orderByDesc('id')
            ->get();
    }

    return view('ERP.maids.maid_doc', compact('maid', 'name'));
}

     // url  /payroll/history/{name}
public function maidPayRollHistory($name)
{
    $maid = DB::table('maids_d_b_s')
         ->select('*') 
        ->where('name', $name)
        ->first();

    if (! $maid) {
        abort(404, 'Maid not found.');
    }

    $maidPayRoll = DB::table('pay_maid_payrolls')
        ->where('maid_id', $maid->id)
        ->orderBy('accrued_month', 'asc')
        ->orderBy('id', 'desc')
        ->get();

    return view('ERP.maids.maid_payroll', [
        'maid'        => $maid,
        'maidPayRoll' => $maidPayRoll,
        'name'        => $name,
    ]);
}


// Url /maid-doc-expiry/{id}
public function showMaidDocExpiry($id)
{
    $maid = MaidsDB::query()
        ->with('maidDocExpiry')
        ->findOrFail($id);

    $name = $maid->name;

    $applyVisas = ApplyVisa::query()
        ->where('maid_id', $maid->id) 
        ->orderByDesc('created_at')
        ->get([
            'id','date','service','status','managment_approval','note',
            'document','comments','created_by','updated_by','created_at','updated_at'
        ]);

    return view('ERP.maids.maid_doc_expiry', compact('maid','name','applyVisas'));
}


   // /page/maid-finance{name}
    public function pageMaidFinance($name){
        $maid = MaidsDB::query()
        ->where('name', $name)
        ->first(); 

        return view('ERP.maids.maid_finance',compact('maid','name'));
    }

// page/maid/invoices/{name}
   public function pageMaidInvoice($name){
    $maid = MaidsDB::where('name', $name)->first();
    $date= date('Y-m-d');
    $cashAndBank = All_account_ledger_DB::where('group' , 'cash equivalent')->get();
            
    return view('ERP.maids.invoices' , compact('name','cashAndBank','date','maid' ) );

}     


// URL /payroll-note/{}
public function maidAdvanceOrDeduction($name)
{
    $maid = MaidsDB::where('name', $name)->first();

    return view('ERP.maids.payroll_note', compact('name', 'maid'));
}

  // /p1/contract/{name}
  public function p1ContractsMaid($name, Request $request)
  {

    $maid = MaidsDB::where('name', $name)->first();
      try {
          $query = categoryOne::with(['customerInfo'])
          ->select([
              'id',
              'contract_ref',
              'invoice_ref',
              'customer_id',
              'maid_id',
              'amount',
              'signature',
              'contract_status',
              'started_date',
              'created_at',
              'created_by'
          ])
          ->where('maid_id', $maid->id)
          ->with(['returnInfo', 'customerInfo:id,name', 'maidInfo:id,name'])
          ->orderBy('created_at', 'DESC');



          if ($request->has('min_date') && $request->min_date != '') {
              $query->whereDate('created_at', '>=', $request->min_date);
          }
  
          if ($request->has('max_date') && $request->max_date != '') {
              $query->whereDate('created_at', '<=', $request->max_date);
          }
  
          return DataTables::eloquent($query)
              ->editColumn('contract_ref', function ($row) {
                  return '<a target="_blank" href="' . url("/get/contract/summary/{$row->contract_ref}") . '">' . $row->contract_ref . '</a>';
              })
                  ->filterColumn('customer_name', function($query, $keyword) {
                    $query->whereHas('customerInfo', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                    
              ->addColumn('invoice_ref', function ($row) {
                  return '<a target="_blank" href="' . url("/get/invoice/cat1/{$row->invoice_ref}") . '">' . $row->invoice_ref . '</a>';
              })
              ->addColumn('customer_name', function ($row) {
                  return '<a href="' . url("customer/report/{$row->customerInfo->name}") . '" target="_blank">' . $row->customerInfo->name . '</a>';
              })
              ->editColumn('maid', function ($row) {
                  return '<a href="' . url("/maid-report/{$row->maid}") . '" target="_blank">' . $row->maid . '</a>';
              })
              ->addColumn('returned_date', function ($row) {
                  return $row->returnInfo ? $row->returnInfo->returned_date : 'N/A'; 
              })
              ->addColumn('reason', function ($row) {
                return $row->returnInfo ? $row->returnInfo->reason: 'N/A'; 
            })
            ->addColumn('actions', function ($row) {
                $contractActions = "<a href='" . url("/get/full/categoryone-contract/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Contract</a>";
                $transferLetter = "<a href='" . url("/transfer/letter-p1/{$row->contract_ref}") . "' target='_blank' class='dropdown-item'>Transfer letter</a>";
                $ministryReturnLetter = "<a href='" . url("/ministry-return/{$row->id}") . "' target='_blank' class='dropdown-item'>Ministry return Letter</a>";
             
                $signButton = $row->signature === 'No signature' ?

                    "<a href='" . url("/sign/cat1/{$row->id}") . "' class='dropdown-item'>Sign</a>" :
                    "<span class='dropdown-item disabled'>Sign available</span>";

                $deleteButton = $row->signature !== 'No signature' ?
                    "<button type='button' class='dropdown-item delete-sign' data-id='{$row->id}'>Delete sign</button>" :
                    "<span class='dropdown-item disabled'>Delete sign</span>";

                $returnButton = $row->contract_status === 0 ?
                    "<p class='dropdown-item'>Returned</p>" :
                    "<button type='button' class='dropdown-item open-modal-btn' data-bs-toggle='modal' data-bs-target='#return-modal' data-maid='{$row->maidInfo->name}' data-contractref='{$row->contract_ref}' data-customer='{$row->customerInfo->name}' data-started_date='{$row->started_date}'>Return</button>";

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
                        </ul>
                    </div>';
            })
              ->rawColumns(['contract_ref', 'invoice_ref', 'customer_name', 'actions', 'maid','reason','returned_date'])
              ->addIndexColumn()
              ->make(true);
      } catch (\Exception $e) {
          Log::error('Error in getContractsTableCustomerReport:', ['error' => $e->getMessage()]);
          return response()->json(['error' => 'Internal Server Error'], 500);
      }
  }
  


  // p4/contracts/maid/{name}
    public function p4ContractMaids($name,Request $request){
        $maid = MaidsDB::where('name', $name)->first();
        try {
        $query = Category4Model::query()
        ->with(['returnInfo', 'customerInfo'])
        ->where('maid_id', $maid->id)
        ->orderBy('created_at', 'DESC');
        if ($request->has('min_date') && $request->min_date != '') {
            $query->whereDate('created_at', '>=', $request->min_date);
        }

        if ($request->has('max_date') && $request->max_date != '') {
            $query->whereDate('created_at', '<=', $request->max_date);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->addColumn('returned_date', function ($row) {
                return $row->returnInfo ? $row->returnInfo->returned_date : 'N/A'; 
            })
            ->addColumn('reason', function ($row) {
            return $row->returnInfo ? $row->returnInfo->reason: 'N/A'; 
        })  

        
        ->addColumn('working_days', function ($row) {
            
            $startDate = Carbon::parse($row->date);
        
            $endDate = $row->returnInfo ? Carbon::parse($row->returnInfo->returned_date) : Carbon::today();
        
            $daysDifference = $startDate->diffInDays($endDate);
        
            return '<a>' . $daysDifference . ' days</a>';
        })
        

            
        ->editColumn('customer', function ($row) {
            return '<a href="' . url("customer/report/{$row->customerInfo->name}") . '" target="_blank">' . $row->customerInfo->name . '</a>';
        })

        ->filterColumn('customer', function($query, $keyword) {
            $query->whereHas('customerInfo', function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        })

    
        ->editColumn('maid', function ($row) {
            return '<a href="' . url("/maid-report/{$row->maid}") . '" target="_blank">' . $row->maid . '</a>';
        })

        ->editColumn('Contract_ref', function ($row) {
            return '<a href="' . url("/category4/contract-bycontract/{$row->Contract_ref}") . '" target="_blank">' . $row->Contract_ref . '</a>';
        })


        ->addColumn('action', function ($row) {
            $actionButtons = '<div class="dropdown">
                <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    Action
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">';

            // Contract status 0: Returned
            if ($row->contract_status === 0) {
                $actionButtons .= '
                    <li><p class="dropdown-item text-muted">Returned</p></li>
                    <li><a target="__blank" href="/copy/upcoming/' . $row->Contract_ref . '" class="dropdown-item"><i class="fa fa-copy"></i> Copy</a></li>
       
                    <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                    <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                    <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>

                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-bs-toggle="modal" data-bs-target="#start_date_modal" 
                                data-id="' . $row->id . '"><i class="fa fa-clock"></i>Edit Start date</button></li>
                                
                    ';
            } else {
                // Contract is not returned
                $actionButtons .= '
                    <li><button type="button" class="dropdown-item open-modal-btn" data-bs-toggle="modal" data-bs-target="#return_modal" data-maid="' . $row->maidInfo->name . '" data-contractref="' . $row->Contract_ref . '" data-customer="' . $row->customerInfo->name . '"><i class="fa fa-undo"></i> Return</button></li>
                    <li><a target="__blank" href="/edit-upcoming-installment/' . $row->Contract_ref . '" class="dropdown-item"><i class="fa fa-cog"></i> Edit</a></li>
                    <li><a href="' . url('/category4/contract-bycontract/' . $row->Contract_ref) . '" target="_blank" class="dropdown-item"><i class="fa fa-file-contract"></i> Contract</a></li>
                    <li><a href="' . url('/get/full-contract-cat4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Full Contract</a></li>
                    <li><a href="' . url('transfer-leter-p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-file"></i> Transfer letter</a></li>

                        <li><button type="button" class="dropdown-item edit-date-open-modal-btn" data-bs-toggle="modal" data-bs-target="#start_date_modal" 
                                data-id="' . $row->id . '"><i class="fa fa-clock"></i>Edit Start date</button></li>
                    ';
                
                if ($row->signature === 'No signature') {
                    $actionButtons .= '
                        <li><a href="' . url('/sign/p4/' . $row->id) . '" target="_blank" class="dropdown-item"><i class="fa fa-signature"></i> Sign</a></li>';
                } else {
                    $actionButtons .= '
                        <li><button type="button" class="dropdown-item delete-sign" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete Signature</button></li>';
                }
            }

            $actionButtons .= '</ul></div>';
            return $actionButtons;
        })
        ->rawColumns(['action', 'customer', 'maid' , 'Contract_ref','working_days'])
        ->make(true);

        }catch (\Exception $e) {
            Log::error('Error in getContractsTableCustomerReport:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }   

public function maidFinanceReport(Request $request, $name)
{
    try {
  
        $maid = MaidsDB::where('name', $name)->firstOrFail();
        $query = $maid->maidAccount()->with('accountLedger:id,ledger')->orderBy('created_at', 'desc');

        if ($request->filled('min_date')) {
            $query->whereDate('created_at', '>=', $request->min_date);
        }

        if ($request->filled('max_date')) {
            $query->whereDate('created_at', '<=', $request->max_date);
        }

     

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return optional($row->created_at)->format('Y-m-d');
            })

            ->filterColumn('account_ledger.ledger', function ($query, $keyword) {
                $query->whereHas('accountLedger', function ($q) use ($keyword) {
                    $q->where('ledger', 'like', "%{$keyword}%");
                });
            })
            ->make(true);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'Maid not found'], 404);
    } catch (\Exception $e) {
        Log::error('Error in maidFinanceReport:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}


    
  // maid/invoices/{name}       
  public function maidInvoices(Request $request , $name)
  {   try {  
    
     $maid = MaidsDB::where('name', $name)->firstOrFail();

            $query = DB::table('general_journal_vouchers')
                ->leftJoin('all_account_ledger__d_b_s as all_account_ledger', 'general_journal_vouchers.ledger_id', '=', 'all_account_ledger.id')
                ->select(
                    'general_journal_vouchers.id as id',   
                    'general_journal_vouchers.created_at',
                    'general_journal_vouchers.contract_ref',
                    'general_journal_vouchers.refCode',
                    'all_account_ledger.ledger as account', 
                    'general_journal_vouchers.voucher_type',
                    'general_journal_vouchers.pre_connection_name',
                    'general_journal_vouchers.amount',
                    'general_journal_vouchers.invoice_balance',
                    'general_journal_vouchers.notes',
                    'general_journal_vouchers.receiveRef',
                    'general_journal_vouchers.creditNoteRef',
                    'general_journal_vouchers.created_by',
                    DB::raw('
                        CASE
                            WHEN general_journal_vouchers.invoice_balance = 0 THEN "Paid"
                            WHEN general_journal_vouchers.amount - general_journal_vouchers.invoice_balance = 0 THEN "Pending"
                            WHEN general_journal_vouchers.amount > general_journal_vouchers.invoice_balance THEN "Partial"
                            ELSE "Unknown"
                        END as payment_status
                    ')
                )
                ->whereIn('general_journal_vouchers.voucher_type', ['Invoice Package1', 'Invoice Package4', 'invoice']) 
                ->where('maid_id', $maid->id)
                ->where('type', 'debit')
                ->orderBy('general_journal_vouchers.created_at', 'desc');

                            

       
          if ($request->has('min_date') && $request->min_date != '') {
              $query->whereDate('created_at', '>=', $request->min_date);
          }
  
          if ($request->has('max_date') && $request->max_date != '') {
              $query->whereDate('created_at', '<=', $request->max_date);
          }
       
      
          $data = DataTables::of($query)
              ->addColumn('action', function ($row) {

           if (auth()->user()->group === 'accounting') {
                  return '
                         <button type="button" class="btn btn-sm btn-outline-warning btn-sm open-modal-btn" 
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

                                  <button type="button" class="btn btn-sm btn-outline-warning btn-sm btn-apply-credit" 
  
                                   data-payment="'.$row->id.'" 
                                 ">Apply credit</button>
                                 
                                 '; }
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
              ->filterColumn('account', function ($query, $keyword) {
                  $query->where('all_account_ledger.ledger', 'like', "%{$keyword}%");
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



  
// /dedction-maid/{name}
public function dataTableAdvanceOrDedction(Request $request, string $name)
{
    try {
        $maidId = DB::table('maids_d_b_s')->where('name', $name)->value('id');
        if (!$maidId) {
            return response()->json(['error' => 'Maid not found.'], 404);
        }

        $qb = DB::table('advance_and_dedcutiot_maids as adm')
            ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'adm.maid_id')
            ->where('adm.maid_id', $maidId)
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
                DB::raw('m.name as maid'),  
            ])
            ->orderByDesc('adm.created_at');

        return DataTables::of($qb)
            ->addIndexColumn()
            ->editColumn('date', fn($row) =>
                $row->date ? Carbon::parse($row->date)->format('F Y') : '')
            ->editColumn('created_at', fn($row) =>
                $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : '')
            ->editColumn('updated_at', fn($row) =>
                $row->updated_at ? Carbon::parse($row->updated_at)->format('Y-m-d') : '')
            ->make(true);

    } catch (\Throwable $e) {
        report($e);
        return response()->json(['error' => 'Something went wrong!'], 500);
    }
    
}


}
