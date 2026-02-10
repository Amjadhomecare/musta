<?php
namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\General_journal_voucher;
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
use Illuminate\Support\Facades\Validator;
use App\DataTables\GeneralJournalVoucherDataTable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\JvLog;




class generalJV extends Controller
{


public function index(Request $request)
{
    $perPage = (int) $request->input('per_page', 15);
    $search  = trim((string) $request->input('search', ''));
    $start   = $request->input('start_date'); // e.g. 2025-09-01
    $end     = $request->input('end_date');   // e.g. 2025-09-21

    $q = JvLog::with('voucher');

    // Date range filter (changed_at)
    if ($start || $end) {
        $startAt = $start ? Carbon::parse($start)->startOfDay() : Carbon::minValue();
        $endAt   = $end   ? Carbon::parse($end)->endOfDay()   : Carbon::now()->endOfDay();
        $q->whereBetween('changed_at', [$startAt, $endAt]);
    }

    // Search by refCode or account
    if ($search !== '') {
        $q->where(function ($w) use ($search) {
            $w->where('ref_code', 'like', "%{$search}%")
              ->orWhere('account_name', 'like', "%{$search}%")
              ->orWhere('voucher_type', 'like', "%{$search}%");


            // If search looks numeric, also try matching ledger_id directly
            if (is_numeric($search)) {
                $w->orWhere('ledger_id', (int) $search);
            }
        });
    }

    $paginator = $q->orderByDesc('changed_at')->paginate($perPage);

    return response()->json([
        'data'         => $paginator->items(),
        'total'        => $paginator->total(),
        'per_page'     => $paginator->perPage(),
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
    ]);
}


public function addNewGeneralJVCntl(Request $request)
{
    try {
        DB::transaction(function () use ($request) {
            $this->validateDebitCreditSums($request);
            
            $randomRefNumber = Str::random(8);
            $paymentVoucher = $request->voucher_type === "Payment Voucher";
            $connectionName = $request->connection ?: "No connection";
            $countType = count($request->type);

        
            $url = null;
            if ($request->hasFile('file')) {
                $disk   = 'r2';
                $folder = 'accounting/' . now()->format('Y/m');
                $path   = $request->file('file')->store($folder, $disk);
                $url = Storage::disk($disk)->url($path);
            }

         
            for ($i = 0; $i < $countType; $i++) {
                $entryUrl = ($i === 0) ? $url : null;
                $this->createJournalVoucher($request, $i, $randomRefNumber, $paymentVoucher, $connectionName, $entryUrl);

                if ($request->vatpayable[$i] > 0) {
                    $this->createVatJournalVoucher($request, $i, $randomRefNumber, $paymentVoucher, $connectionName, $entryUrl);
                }
            }
        });

        return response([
            'success' => true,
            'message' => 'General Journal Voucher Added Successfully!'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response([
            'success' => false,
            'message' => 'Failed to save jv: ' . $e->getMessage()
        ]);
    }
}

    private function createJournalVoucher($request, $index, $randomRefNumber, $paymentVoucher, $connectionName, $url)
{
    $journalV = new General_journal_voucher();

    $journalV->date = date('Y-m-d', strtotime($request->date_jv));
    $journalV->refCode = $randomRefNumber;
    $journalV->refNumber = $paymentVoucher ? (int) $request->refNumber + 1 : 0;
    $journalV->voucher_type = $request->voucher_type;
    $journalV->type = $request->type[$index];
    $journalV->maid_name = $request->maid[$index];
    $journalV->account = $request->account[$index];
    $journalV->amount = $request->amount[$index];
    $journalV->notes = $request->notes[$index];
    $journalV->pre_connection_name = $connectionName;
    $journalV->created_by = Auth::user()->name;
    $journalV->extra = $url;

    $journalV->save();
}

private function createVatJournalVoucher($request, $index, $randomRefNumber, $paymentVoucher, $connectionName, $url)
{
    $vatJournalV = new General_journal_voucher();

    $vatJournalV->date = date('Y-m-d', strtotime($request->date_jv));
    $vatJournalV->refCode = $randomRefNumber;
    $vatJournalV->refNumber = $paymentVoucher ? (int) $request->refNumber + 1 : 0;
    $vatJournalV->voucher_type = $request->voucher_type;
    $vatJournalV->type = $request->type[$index];
    $vatJournalV->account = "VAT";
    $vatJournalV->amount = $request->vatpayable[$index];
    $vatJournalV->maid_name = $request->maid[$index];
    $vatJournalV->notes = $request->notes[$index];
    $vatJournalV->pre_connection_name = $connectionName;
    $vatJournalV->created_by = Auth::user()->name;
    $vatJournalV->extra = $url;

    $vatJournalV->save();
}


public function updateSelectedJournalEntryGroupByRefNumberAction(Request $request)
{
    $transactions = $request->input('transactions', []);

    if (!is_array($transactions) || empty($transactions)) {
        return response([
            'success' => false,
            'message' => 'No transactions provided.',
        ], 422);
    }

    // 1) Validate debit = credit
    $debitSum  = 0;
    $creditSum = 0;
    foreach ($transactions as $t) {
        $amount = is_numeric($t['amount'] ?? null) ? (float)$t['amount'] : 0;
        if (($t['type'] ?? null) === 'debit')  { $debitSum  += $amount; }
        if (($t['type'] ?? null) === 'credit') { $creditSum += $amount; }
    }
    if (bccomp($debitSum, $creditSum, 2) !== 0) {
        return response([
            'success' => false,
            'message' => 'The sum of debits does not match the sum of credits.',
        ], 422);
    }

    // 2) Optional file replacement – prepare outside the transaction to avoid double uploads on retry
    $newFileUrl = null;
    $oldFileUrl = null;

    if ($request->hasFile('file')) {
        // Find first voucher to read old file (if any)
        $firstVoucher = isset($transactions[0]['id'])
            ? General_journal_voucher::find($transactions[0]['id'])
            : null;

        $oldFileUrl = $firstVoucher?->extra;

        // Upload new file
        $disk   = 'r2';
        $folder = 'accounting/' . now()->format('Y/m');
        $path   = $request->file('file')->store($folder, $disk);
        $newFileUrl = Storage::disk($disk)->url($path);
    }

    try {
        DB::transaction(function () use ($request, $transactions, $newFileUrl, $oldFileUrl) {
            // If we uploaded a new file, delete the old one (after upload succeeds, inside txn)
            if ($newFileUrl && $oldFileUrl) {
                $path = ltrim(parse_url($oldFileUrl, PHP_URL_PATH) ?? '', '/');
                // When the bucket is on the host (typical S3 URLs), $path is just "folder/...".
                // If your URL variant includes the bucket in the path, you might need to strip it.
                if ($path) {
                    Storage::disk('r2')->delete($path);
                }
            }

            foreach ($transactions as $i => $t) {
                if (empty($t['id'])) {
                    continue;
                }

                $voucher = General_journal_voucher::find($t['id']);
                if (!$voucher) {
                    continue;
                }

                // Build the payload for update(); let model events handle derived fields
                $payload = [
                    'date'            => $request->input('date'),
                    'voucher_type'    => $request->input('voucher_type'),
                    'type'            => $t['type'] ?? null,
                    'account'         => $t['account'] ?? null,
                    'amount'          => $t['amount'] ?? 0,
                    'maid_name'       => $t['maid_name'] ?? null,      
                    'invoice_balance' => $t['invoice_balance'] ?? 0,
                    'notes'           => $t['notes'] ?? null,
                    'updated_by'      => Auth::user()->name ?? 'system',
                ];

                // File: only first row keeps the new URL; others set to null
                if ($i === 0 && $newFileUrl) {
                    $payload['extra'] = $newFileUrl;
                } elseif ($i !== 0) {
                    $payload['extra'] = null;
                }

                // IMPORTANT: use instance update() to trigger Eloquent events
                $voucher->update($payload);
            }
        });

        return response([
            'success' => true,
            'message' => 'Accounting Journal Updated Successfully!',
        ]);

    } catch (\Throwable $e) {
        return response([
            'success' => false,
            'message' => 'Failed to Edit Journal Voucher: ' . $e->getMessage(),
        ], 500);
    }
}





    
private function validateDebitCreditSums($request)
{
    $debitSum = 0.0;
    $creditSum = 0.0;

    if ($request->has('type') && is_array($request->type)) {
        $countType = count($request->type);

        for ($i = 0; $i < $countType; $i++) {
            // Always cast to float for calculation
            $amount = isset($request->amount[$i]) && is_numeric($request->amount[$i]) ? floatval($request->amount[$i]) : 0.0;
            $vatPayable = isset($request->vatpayable[$i]) && is_numeric($request->vatpayable[$i]) ? floatval($request->vatpayable[$i]) : 0.0;

            $totalAmount = $amount + ($vatPayable !== 0.0 ? $vatPayable : 0.0);

            if ($request->type[$i] === 'debit') {
                $debitSum += $totalAmount;
            } elseif ($request->type[$i] === 'credit') {
                $creditSum += $totalAmount;
            }
        }
    }
    // Log::info("Debit Sum: $debitSum, Credit Sum: $creditSum");
    // Compare with tolerance for float values
    if (abs($debitSum - $creditSum) > 0.01) {
        throw new \Exception('The sum of debits does not match the sum of credits');
    }
}


    public function viewPreConnectionAccounting(Request $request) {
        $nameOfConnection = $request->input('name_of_connection');

        $records = AccountingPreConnection::where('name_of_connection', $nameOfConnection)->get();

        return response()->json($records);
    }


  ///  this is for select 2 ajax search preconnection
    public function viewAllRegistredGeneralJVCntl(Request $request) {
        if ($request->ajax()) {
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = 30;
            $query = AccountingPreConnection::query()
                ->select('name_of_connection', DB::raw('count(*) as connection_count'))
                ->groupBy('name_of_connection');
            // Applying search filters before retrieving data
            if (!empty($search)) {
                $query->where(function ($query) use ($search) {
                    $query->where('name_of_connection', 'like', '%' . $search . '%')
                        ->orWhere('group', 'like', '%' . $search . '%')
                        ->orWhere('amount', 'like', '%' . $search . '%')
                        ->orWhere('created_by', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            }

            // Execute the query and paginate results
            $preConnection = $query->paginate($perPage, ['*'], 'page', $page);

            // Returning JSON response
            return response()->json([
                'total_count' => $preConnection->total(),
                'items' => $preConnection->getCollection()->transform(function ($item) {
                    return [
                        'id' => $item->name_of_connection,  
                        'text' => "{$item->name_of_connection} /  {$item->connection_count} record founded",  
                        'full_data' => $item->toArray() 
                    ];
                }),
            ]);
        }
        else{
            $maxrefNumber = General_journal_voucher::max('refNumber');
            $currentDate = date('Y-m-d');
            return view("ERP.accounting.all_registeredJV", compact('maxrefNumber', 'currentDate'));
        }


    } //end method

      // show all jv url = /all-jv'
public function AllRegistredGeneralJVCntl(Request $request)
{
    try {
        $query = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('maids_d_b_s as m', 'gjv.maid_id', '=', 'm.id') 
            ->leftJoin('all_account_ledger__d_b_s as a', 'gjv.ledger_id', '=', 'a.id')
            ->select(
                'gjv.id',
                'gjv.date',
                'gjv.voucher_type',
                'gjv.refCode',
                DB::raw('a.ledger as account'),
                'gjv.notes',
                'gjv.type',
                'gjv.amount',
                DB::raw('m.name as maid_name')
            );

        // Date filters
        $query->when($request->filled('min_date'), fn($q) =>
            $q->whereDate('gjv.date', '>=', $request->min_date)
        )->when($request->filled('max_date'), fn($q) =>
            $q->whereDate('gjv.date', '<=', $request->max_date)
        );

        // Optional: global search (DataTables search box)
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('gjv.refCode', 'like', "%{$search}%")
                  ->orWhere('a.ledger', 'like', "%{$search}%")
                  ->orWhere('gjv.notes', 'like', "%{$search}%")
                  ->orWhere('m.name', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return '<a target="__blank" href="/view/jv/selected/' . e($row->refCode) . '" class="btn btn-blue rounded-pill waves-effect waves-light">
                            <i class="fa fa-eye"></i> View
                        </a>
                        <a href="#" class="btn btn-blue rounded-pill waves-effect waves-light edit-jv" title="edit" data-refCode="' . e($row->refCode) . '">
                            <i class="fa fa-pencil"></i> Edit
                        </a>';
            })
            // Column-specific filters (server-side column search)
            ->filterColumn('refCode', function ($q, $keyword) {
                $q->where('gjv.refCode', 'like', "%{$keyword}%");
            })
            ->filterColumn('account', function ($q, $keyword) {
                $q->where('a.ledger', 'like', "%{$keyword}%");   
            })
            ->filterColumn('maid_name', function ($q, $keyword) {
                $q->where('m.name', 'like', "%{$keyword}%");      
            })
            ->rawColumns(['action'])
            ->make(true);

    } catch (\Throwable $e) {
        Log::error('Error in AllRegistredGeneralJVCntl', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

    // the end of all jv viwing


public function viewSelectedJournalEntryGroupByRefNumber($refnumber)
{
    $details_jv = General_journal_voucher::with([
        'accountLedger:id,ledger',   // select id + ledger only
        'maidRelation:id,name'       // select id + name only
    ])
    ->where('refCode', $refnumber)
    ->get();

    return view('ERP.accounting.details_jv_selected', compact('details_jv'));
}
// End Method

  
     // URL /view/jv/edit/{refnumberedit}
// URL /view/jv/edit/{refnumberedit}
public function editSelectedJournalEntryGroupByRefNumber($refnumberedit)
{
    // Eager-load only the columns we need from relations
    $details_jv = General_journal_voucher::query()
        ->select([
            'id','date','refCode','refNumber','voucher_type','type',
            'pre_connection_name','maid_id','ledger_id','amount','invoice_balance',
            'notes','receiveRef','creditNoteRef','contract_ref','extra',
            'created_by','updated_by','created_at','updated_at'
        ])
        ->with([
            'accountLedger:id,ledger',
            'maidRelation:id,name'
        ])
        ->where('refCode', $refnumberedit)
        ->get()
        ->map(function ($row) {
            return [
                'id'               => $row->id,
                'date'             => $row->date,
                'refCode'          => $row->refCode,
                'refNumber'        => $row->refNumber,
                'voucher_type'     => $row->voucher_type,
                'type'             => $row->type,
                'pre_connection_name' => $row->pre_connection_name,
                'maid_name'        => optional($row->maidRelation)->name,
                'account'          => optional($row->accountLedger)->ledger,
                'amount'           => $row->amount,
                'invoice_balance'  => $row->invoice_balance,
                'notes'            => $row->notes,
                'receiveRef'       => $row->receiveRef,
                'creditNoteRef'    => $row->creditNoteRef,
                'contract_ref'     => $row->contract_ref,
                'extra'            => $row->extra,
                'created_by'       => $row->created_by,
                'updated_by'       => $row->updated_by,
                'created_at'       => $row->created_at,
                'updated_at'       => $row->updated_at,
            ];
        });

    return response()->json([
        'success'    => true,
        'details_jv' => $details_jv,
    ]);
}


   
    public function viewPreConnectionGeneralJVCntl (){

        $all_ledger_name = All_account_ledger_DB::where('group','!=','customer')->get();

        return view("ERP.accounting.pre_connection_jv" , compact('all_ledger_name'));
      }//end method


    public function addNewPreConnectionGeneralJVCntl (Request $request){
            if (isset($request->type) && is_array($request->type)) {
                $countType = count($request->type);

                for ($i = 0; $i < $countType; $i++) {
                    $pre_journalV = new AccountingPreConnection();
                    $pre_journalV->group = $request->group;
                    $pre_journalV->extra = 'no data';
                    $pre_journalV->name_of_connection = $request->name_of_connection;
                    $pre_journalV->type = $request->type[$i];
                    $pre_journalV->account = $request->account[$i];
                    $pre_journalV->amount = $request->amount[$i] ;
                    $pre_journalV->notes = $request->notes[$i] ;
                    $pre_journalV->created_by = Auth::user()->name;

                    $pre_journalV->save();
                }
            } else {

                $notification = array(
                    'message' => 'Invalid request data',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification);
            }

            $notification = array(
                'message' => 'Data Saved Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }//end method


    // URL /search/ledger
    public function viewSearchStatmentAccountCntl(Request $request)
    {
        $nameOfLedger = $request->selected_ledger;
        $findingLedger = All_account_ledger_DB::where('ledger', $nameOfLedger)->value('id');
        $dateFrom = $request->date_start;
        $dateTo = $request->date_end;
    
        $openingBalance = 0;
        $results = collect();
        $totals = (object) ['totalDebit' => 0, 'totalCredit' => 0];
        $finalClosingBalance = 0;
    
        if ($nameOfLedger && $dateFrom && $dateTo) {

            $openingBalance = General_journal_voucher::with('maidRelation:id,name')
            ->where('ledger_id', $findingLedger)
                ->where('date', '<', $dateFrom)
                ->selectRaw('
                    COALESCE(SUM(case when type = "debit" then amount else 0 end), 0) - 
                    COALESCE(SUM(case when type = "credit" then amount else 0 end), 0) as openingBalance
                ')
                ->value('openingBalance') ?? 0;

            $results = General_journal_voucher::where('ledger_id', $findingLedger)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date', 'asc')
                ->get();


            $totals = General_journal_voucher::where('ledger_id', $findingLedger)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->selectRaw('
                    COALESCE(SUM(case when type = "debit" then amount else 0 end), 0) as totalDebit,
                    COALESCE(SUM(case when type = "credit" then amount else 0 end), 0) as totalCredit
                ')
                ->first();
    
     
            $finalClosingBalance = General_journal_voucher::calculateCustomerClosingBalance($nameOfLedger);
        }
    
        return view("ERP.accounting.accounts_statment_report", [
            'accountName' => $results,
            'nameOfLedger' => $nameOfLedger,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'openingBalance' => $openingBalance,
            'finalClosingBalance' => $finalClosingBalance,
            'totalDebit' => $totals->totalDebit ?? 0,
            'totalCredit' => $totals->totalCredit ?? 0,
        ]);
    }
    
    
 

  // url /add/new/ledger
    public function viewRegisterNewLedgerCntl(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = 30;

            $query = All_account_ledger_DB::query();

            if (!empty($search)) {
                $query->where('ledger', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%');
            }

            $ledger = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'total_count' => $ledger->total(),
                'items' => $ledger->map(function ($ledger) {
                    return [
                        'system_id' => $ledger->id,
                        'id' => $ledger->ledger,
                        'text' => "{$ledger->ledger} / Phone: {$ledger->note} / Closing balance: " . General_journal_voucher::calculateCustomerClosingBalance($ledger->ledger)
                    ];
                })
            ]);
        }
        else {
            return view('ERP.accounting.add_new_ledger');
        }
    } //End Method



    public function storeRegisterNewLedgerCntl(Request $request)
    {
        $validateData = $request->validate([
            'ledger_name' => 'required|max:200|unique:all_account_ledger__d_b_s,ledger',
             'amount'=> 'integer'
        ]);

        All_account_ledger_DB::create([
            'amount' => $request->amount ,
            'ledger' => strtoupper($request->ledger_name),
            'class' => $request->class_name,
            'note' => $request->note,
            'group' => $request->group_name,
            'sub_class' => $request->sub_class_name,
            'created_by'=> Auth::user()->name,
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'New Ledger Name Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    } //End Method
  public function viewTrialBalanceCntl(Request $request)
    {
        $startDate = $request->input('start_date') ?? '2018-01-01';
        $endDate = $request->input('end_date') ?? now()->toDateString();
    
        $structuredBalances = [];
    
        $accountBalances = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('all_account_ledger__d_b_s as al', 'gjv.ledger_id', '=', 'al.id')
            ->select(
                'gjv.ledger_id',
                'al.ledger',
                'al.class',
                'al.group',
                'al.ledger',
                DB::raw('SUM(CASE WHEN gjv.type = "credit" THEN gjv.amount ELSE 0 END) as total_credit'),
                DB::raw('SUM(CASE WHEN gjv.type = "debit" THEN gjv.amount ELSE 0 END) as total_debit')
            )
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('gjv.ledger_id', 'al.class', 'al.group', 'al.ledger')
            ->havingRaw('SUM(CASE WHEN gjv.type = "debit" THEN gjv.amount ELSE 0 END) != SUM(CASE WHEN gjv.type = "credit" THEN gjv.amount ELSE 0 END)')
            ->get();
    
        foreach ($accountBalances as $accountBalance) {
            $balance = $accountBalance->total_debit - $accountBalance->total_credit;
    
            $class = $accountBalance->class ?? 'Uncategorized';
            $group = $accountBalance->group ?? 'Miscellaneous';
            $ledgerName = $accountBalance->ledger;
    
            $structuredBalances[$class][$group][] = [
                'ledger' => $ledgerName,
                'balance' => $balance,
            ];
        }
    
        return view("ERP.accounting.trial_balance", compact('structuredBalances', 'startDate', 'endDate'));
    }


    public function balanceSheet(Request $request)
    {
        $startDate = '2018-01-01';
        $endDate = $request->input('end_date') ?? '2023-12-31';
    
        $structuredBalances = [];
    
        $accountBalances = DB::table('general_journal_vouchers as gjv')
            ->leftJoin('all_account_ledger__d_b_s as al', 'gjv.ledger_id', '=', 'al.id')
            ->select(
                'gjv.ledger_id',
                'al.class',
                'al.ledger as account',
                'al.sub_class',
                'al.group',
                'al.ledger',
                DB::raw('SUM(CASE WHEN gjv.type = "credit" THEN gjv.amount ELSE 0 END) as total_credit'),
                DB::raw('SUM(CASE WHEN gjv.type = "debit" THEN gjv.amount ELSE 0 END) as total_debit')
            )
            ->whereBetween('gjv.date', [$startDate, $endDate])
            ->groupBy('gjv.ledger_id', 'al.class', 'al.sub_class', 'al.group', 'al.ledger')
            ->get();
    
        $expenseTotal = 0;
        $revenueTotal = 0;
    
        foreach ($accountBalances as $accountBalance) {
            $balance = $accountBalance->total_debit - $accountBalance->total_credit;
            if ($balance == 0) continue;
    
            $class = $accountBalance->class ?? 'Uncategorized';
            $subClass = $accountBalance->sub_class ?? 'General';
            $rawGroup = $accountBalance->group ?? 'Miscellaneous';
            $groupLower = strtolower($rawGroup);
            $ledger = $accountBalance->ledger ?? $accountBalance->account;
    
            if ($class === 'Expenses') {
                $expenseTotal += $balance;
                continue;
            }
    
            if ($class === 'Revenue') {
                $revenueTotal += $balance;
                continue;
            }
    
            if ($groupLower === 'customer') {
                $structuredBalances[$class][$subClass]['Account Receivable'] =
                    ($structuredBalances[$class][$subClass]['Account Receivable'] ?? 0) + $balance;
                continue;
            }
    
            if ($groupLower === 'account payable') {
                $structuredBalances[$class][$subClass]['Account Payable'] =
                    ($structuredBalances[$class][$subClass]['Account Payable'] ?? 0) + $balance;
                continue;
            }
    
            $structuredBalances[$class][$subClass][$rawGroup][] = [
                'ledger' => $ledger,
                'balance' => $balance
            ];
        }
    
        $structuredBalances['Profit/Loss'] = $revenueTotal + $expenseTotal;
    
        return view("ERP.accounting.balance_sheet", compact('structuredBalances', 'startDate', 'endDate'));
    }
    

    public function viewPagePandL(){


        return view("ERP.accounting.income_statment");

    }



    // url '/income-statement'
    public function incomeStatement(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
    
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        // For Revenues: sum 'credit' and subtract 'debit'

        $revenues = General_journal_voucher::with('accountLedger')
            ->whereHas('accountLedger', function ($query) {
                $query->where('class', 'Revenue');
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($item) => $item->accountLedger->group ?? 'no group')
            ->map(function ($group) {
                return [
                    'total' => $group->reduce(function ($carry, $item) {
                        return $carry + ($item->type == 'credit' ? $item->amount : -$item->amount);
                    }, 0),
                    'ledgers' => $group->groupBy('ledger_id')->mapWithKeys(function ($ledgerGroup) {
                        $ledgerName = $ledgerGroup->first()->accountLedger->ledger ?? 'Unknown Ledger';
                        $total = $ledgerGroup->reduce(function ($carry, $item) {
                            return $carry + ($item->type == 'credit' ? $item->amount : -$item->amount);
                        }, 0);
                        return [$ledgerName => $total]; 
                    })
                ];
            });

        // For Expenses: sum 'debit' and subtract 'credit'
        $expenses = General_journal_voucher::with('accountLedger')
            ->whereHas('accountLedger', function ($query) {
                $query->where('class', 'Expenses');
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($item) => $item->accountLedger?->group ?? 'No group')
            ->map(function ($group) {
                return [
                    'total' => $group->reduce(function ($carry, $item) {
                        return $carry + ($item->type == 'debit' ? $item->amount : -$item->amount);
                    }, 0),
                    'ledgers' => $group->groupBy('ledger_id')->mapWithKeys(function ($ledgerGroup) {
                        $ledgerName = $ledgerGroup->first()->accountLedger->ledger ?? 'Unknown Ledger';
                        $total = $ledgerGroup->reduce(function ($carry, $item) {
                            return $carry + ($item->type == 'debit' ? $item->amount : -$item->amount);
                        }, 0);
                        return [$ledgerName => $total]; 
                    })
                ];
            });

    

        $totalRevenue = $revenues->sum('total');
        $totalExpenses = $expenses->sum('total');
  
        $netIncome = $totalRevenue - $totalExpenses;
    
     
        $data = [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
        ];
    
        return response()->json($data);
    }
    



    public function viewInvoicesPreConnectionsCntl(){

        $all_ledger_name = All_account_ledger_DB::where('group','!=','customer')->get();

        $currentDate = date('Y-m-d');

        return view('ERP.accounting.invoices_pre_connection',compact('currentDate','all_ledger_name'));
    }//End method

    public function storeInvoicesPreConnectionsCntl(Request $request)
    {
        $countType = count($request->ledger_name);

        $existingInvoiceConnection = Pre_connection_invoiceDB::where('invoice_connection_name', $request->name_of_connection)->first();
        if ($existingInvoiceConnection) {
            $notification = array(
                'message' => 'The invoice connection name already exists in the database',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }

        for ($i = 0; $i < $countType; $i++) {
            $invoiceConnetion = new Pre_connection_invoiceDB();
            $invoiceConnetion->group = $request->the_group;
            $invoiceConnetion->invoice_connection_name = $request->name_of_connection;
            $invoiceConnetion->type = $request->type[$i];
            $invoiceConnetion->ledger = $request->ledger_name[$i];
            $invoiceConnetion->amount = floatval($request->amount[$i]);
            $invoiceConnetion->total_credit = $request->total_credit;
            $invoiceConnetion->created_by = Auth::user()->name;

            $invoiceConnetion->save();
        }

        $notification = array(
            'message' => 'New invoice pre-connection inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } //End method

    public function AjaxlistAccountLedgers(Request $request)
    {
        if ($request->ajax()) {
            $ledgers = All_account_ledger_DB::where('group', '!=', 'customer')->get();
    
            return DataTables::of($ledgers)
                            ->addIndexColumn()
                            ->make(true);
        }
    }
    

    public function editLedger($id)
        {
            $ledger = All_account_ledger_DB::findOrFail($id);
            return response()->json($ledger);
        }// done the method

    public function updateLedger(Request $request)
        {
            $validatedData = $request->validate([
                'note' => 'nullable|string|max:255',
                'class' => 'required|string|max:255',
                'group' => 'required|string|max:255',
                'amount' => 'required|numeric',
                'sub_class' => 'required|string|max:255',
            ]);

            try {
                $ledger = All_account_ledger_DB::findOrFail($request->id);
                $ledger->update($validatedData);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Updated!'
                ], 201);

            } catch (\Exception $e) {
                Log::error('Error in file upload: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update maid'
                ], 500);
            }
        }



    public function AjaxlistInvoiceConnection(Request $request){

            if ($request->ajax()) {
                $data = Pre_connection_invoiceDB::all();

                return DataTables::of($data)
                                    ->addIndexColumn()
                                    ->make(true);
               }
        }

    public function checkInvoiceConnection ($name){

        $connection = Pre_connection_invoiceDB::where('invoice_connection_name', $name )->get();
        $connectionName = Pre_connection_invoiceDB::where('invoice_connection_name', $name )->first();
        $ledgers = All_account_ledger_DB::where('group','!=','customer')->get();

        return view('ERP.accounting.edit.edit_invoice_connection' ,compact('connection','connectionName','ledgers'));

    } //end method

    

    public function updateConnectionForInvoice(Request $request)
    {
       $validatedData = $request->validate([
            'items.*.id' => 'required|exists:pre_connection_invoice_d_b_s,id',
            'items.*.ledger' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric',
            'items.*.total_credit' => 'required|numeric',
            'invoice_connection_name' => 'required|string|max:255',
            'group' => 'required|string|max:255',


        ]);

        foreach ($validatedData['items'] as $item) {
            $connection = Pre_connection_invoiceDB::find($item['id']);
            if ($connection) {
                $connection->update([
                    'invoice_connection_name' => $request->invoice_connection_name,
                    'group' => $request->group,
                    'ledger' => $item['ledger'],
                    'amount' => $item['amount'],
                    'total_credit' => $item['total_credit'],
                    'updated_by' => Auth::user()->name
                ]);
            }
        }
        return redirect('list-invoice-preconnection')->with('success', 'Connections updated successfully.');
    }

    public function deleteInvConnetion($name){

               $selected = Pre_connection_invoiceDB::where('invoice_connection_name',$name)->get();

               Pre_connection_invoiceDB::destroy($selected);

               $notification = array(
                'message' => 'Data Deleted Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);

    }

    public function listConnectionJv(){
       return view('ERP.accounting.list_connection.connection_jv');
   }

    public function AjaxlistPreConnectionAcc(Request $request){

        if ($request->ajax()) {
            $data = AccountingPreConnection::all();

            return DataTables::of($data)
                                ->addIndexColumn()
                                ->make(true);
           }
    }

    public function checkPreConnectionAcc ($name){

        $connection = AccountingPreConnection::where('name_of_connection', $name )->get();
        $connectionName = AccountingPreConnection::where('name_of_connection', $name )->first();
        $ledgers = All_account_ledger_DB::where('group','!=','customer')->get();

        return view('ERP.accounting.edit.edit_pre_connection' ,compact('connection','connectionName','ledgers'));

    }
    

    public function updateConnection(Request $request)
    {
        $data = $request->validate([
            'connection_id.*' => 'nullable|exists:accounting_pre_connections,id',
            'account.*' => 'required_with:connection_id.*|string',
            'amount.*' => 'required_with:connection_id.*|numeric|min:0',
            'type.*' => 'required_with:connection_id.*|string|in:credit,debit',
            'name_of_connection' => 'required|string|max:255',
            'deleted_connection_ids.*' => 'nullable|exists:accounting_pre_connections,id',
        ]);
    
        $totalCredit = 0;
        $totalDebit = 0;
    
        // Calculate totals for validation
        if (isset($data['type'])) {
            foreach ($data['type'] as $index => $type) {
                $amount = (float) $data['amount'][$index];
                if ($type === 'credit') {
                    $totalCredit += $amount;
                } elseif ($type === 'debit') {
                    $totalDebit += $amount;
                }
            }
        }
    
        if ($totalCredit !== $totalDebit) {
            return back()->withErrors(['total' => 'Total Credit and Debit must be equal.'])->withInput();
        }
    
        // Delete connections marked for deletion
        if (!empty($data['deleted_connection_ids'])) {
            AccountingPreConnection::whereIn('id', $data['deleted_connection_ids'])->delete();
        }
    
        // Update or create connections
        if (isset($data['connection_id'])) {
            foreach ($data['connection_id'] as $index => $id) {
                $updateData = [
                    'account' => $data['account'][$index],
                    'amount' => $data['amount'][$index],
                    'type' => $data['type'][$index],
                    'name_of_connection' => $data['name_of_connection'],
                ];
    
                AccountingPreConnection::updateOrCreate(['id' => $id], $updateData);
            }
        }
    
        return redirect('list-pre-connection')->with('success', 'Connections updated successfully.');
    }
    


    public function deleteJvConnetion($name){

        $selected = AccountingPreConnection::where('name_of_connection',$name)->get();

        AccountingPreConnection::destroy($selected);

        $notification = array(
            'message' => 'Data Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    //URL all-ledger
    public function listOfLedgers(Request $request){
    if ($request->ajax()) {
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $perPage = 30;

        $query = All_account_ledger_DB::query();

        if (!empty($search)) {
            $query->where('ledger', 'like', '%' . $search . '%')
                ->orWhere('note', 'like', '%' . $search . '%');
        }

        $ledgers = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'total_count' => $ledgers->total(),
            'items' => $ledgers->map(function ($ledgers) {
                return [
                    'system_id' => $ledgers->id,
                    'id' => $ledgers->ledger,
                    'text' => "{$ledgers->ledger} / ID: {$ledgers->id} / Closing balance: " . General_journal_voucher::calculateCustomerClosingBalance($ledgers->ledger)
                ];
            })
        ]);
    }else
        return view('ERP.customers.allCustomers');
}//End Method




public function pageTrial(){

    return view('ERP.accounting.acc_balance');
}

public function TrialBalanceCntl()
{
    $uniqueAccounts = General_journal_voucher::with('accountLedger')
        ->select('account')
        ->distinct()
        ->get();  
    // Use DataTables to create a response
    return DataTables::of($uniqueAccounts)
        ->make(true);
}


}//ENd the class
