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
use Illuminate\Validation\Rule;

class installmentCntl extends Controller
{
    public function pageInstallment(){

        return view('ERP.installment.page_installment');
     
    }

    // installment/{$id}
    public function getById($id){
        $data = UpcomingInstallment::with(['contractRef.maidInfo:id,name,salary','customerInfo:id,name'])->findOrFail($id);

        return response()->json($data);
    }

    // url /table/installment
public function datatableinstallment(Request $request)
{
    try {
        $query = DB::table('upcoming_installments as ui')
            ->leftJoin('category4_models as c4', 'ui.contract', '=', 'c4.Contract_ref')
            ->leftJoin('maids_d_b_s as m', 'c4.maid_id', '=', 'm.id')
            ->leftJoin('customers as cust', 'ui.customer_id', '=', 'cust.id')
            ->where('ui.invoice_status', 0)
            ->where('c4.contract_status', 1)
            ->when($request->filled('min_date'), fn($q) => $q->whereDate('ui.accrued_date', '>=', $request->min_date))
            ->when($request->filled('max_date'), fn($q) => $q->whereDate('ui.accrued_date', '<=', $request->max_date))
            ->select(
                'ui.id',
                'ui.accrued_date',
                'ui.amount',
                'cust.name as customer',
                'ui.contract',
                'ui.note',
                'ui.cheque',
                'ui.invoice_status',
                'ui.created_by',
                'm.name as maid_name',
                'm.maid_type',
                'm.salary',
                'cust.phone'
            )
            ->orderBy('ui.created_at', 'asc');

        return DataTables::of($query)
            ->addColumn('select', fn ($row) =>
                '<input type="checkbox" class="row-select" value="' . e($row->id) . '">'
            )
            ->editColumn('maid_name', fn ($row) =>
                $row->maid_name
                    ? '<a href="' . url('/maid-report/' .$row->maid_name) . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->maid_name) . '</a>'
                    : ''
            )
            ->editColumn('maid_type', fn ($row) =>
                $row->maid_type
                    ? '<a href="' . url('/maid-report/' . $row->maid_name ) . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->maid_type) . '</a>'
                    : ''
            )
            ->editColumn('contract', fn ($row) =>
                '<a href="' . url('/edit-upcoming-installment/' . e($row->contract)) . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->contract) . '</a>'
            )
            ->editColumn('customer', fn ($row) =>
                '<a href="' . url('/customer/report/' . e($row->customer)) . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->customer) . '</a>'
            )
            ->addColumn('meta', fn ($row) =>
                '<button class="generate-invoice btn btn-secondary btn-sm"
                    data-amount="'   . e($row->amount)     . '"
                    data-salary="'   . e($row->salary)     . '"
                    data-customer="' . e($row->customer)   . '"
                    data-date="'     . e($row->accrued_date) . '"
                    data-maid="'     . e($row->maid_name)  . '"
                    data-note="'     . e($row->note)       . '"
                    data-cheque="'   . e($row->cheque)     . '"
                    data-contract="' . e($row->contract)   . '"
                    data-id="'       . e($row->id)         . '">
                    Invoice
                 </button>'
            )
            ->addColumn('custom', fn ($row) =>
                '<button class="customized-invoice btn btn-secondary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#custom-modal"
                    data-id="' . e($row->id) . '">
                    customized
                 </button>'
            )
            ->editColumn('phone', fn ($row) =>
                '<a href="' . url('/customer/report/' . e($row->customer)) . '" target="_blank" style="color: red; text-decoration: none;">' . e($row->phone ?? '') . '</a>'
            )
            ->filterColumn('phone', fn ($q, $kw) =>
                $q->where('cust.phone', 'like', "%{$kw}%")
            )
            ->filterColumn('maid_name', fn ($q, $kw) =>
                $q->where('m.name', 'like', "%{$kw}%")
            )
            ->filterColumn('maid_type', fn ($q, $kw) =>
                $q->where('m.maid_type', 'like', "%{$kw}%")
            )
            ->filterColumn('customer', fn ($q, $kw) =>
                $q->where('cust.name', 'like', "%{$kw}%")
            )
            ->rawColumns(['select', 'maid_name', 'maid_type', 'customer', 'meta', 'custom', 'contract', 'phone'])
            ->make(true);

    } catch (\Exception $e) {
        Log::error('Error in from server:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}

  // URL /installment/store
  public function storeInstallmentInvoice(Request $request)
  {
            $validatedData = $request->validate([
            'amount'   => 'required|numeric|min:100',
            'salary'   => 'required|numeric|min:0',
            'customer' => 'required|string',
            'date'     => 'required|date',
            'maid'     => [
                'required',
                'string',
                Rule::exists('maids_d_b_s', 'name'),
            ],
            'id'       => 'required|exists:upcoming_installments,id',
            'contract' => 'required'
        ]);

        if ($validatedData['amount'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice amount must be greater than zero.',
            ], 422);
        }

        if ($validatedData['amount'] < $validatedData['salary']) {
            return response()->json([
                'success' => false,
                'message' => 'Salary of maid cannot be more than the invoice amount.',
          ], 422);
      }
  
      $totalInvoiceWithoutVat = ($validatedData['amount'] - $validatedData['salary']) / 1.05;
  
      $randomRefNumber = 'inv4_' . Str::random(8);
  

      DB::beginTransaction();
  
      try {
     
          General_journal_voucher::create([
              'type' => "debit",
              'refNumber' => 0,
              'account' => $validatedData['customer'],
              'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
              'refCode' => $randomRefNumber,
              "voucher_type" => "Invoice Package4",
              'maid_name' => $validatedData['maid'],
              "notes" => $request->note . " " . $request->cheque,
              "amount" => $validatedData['amount'],
              "invoice_balance" => $validatedData['amount'],
              "contract_ref" =>$validatedData['contract'],
              "created_by" => Auth::user()->name,
              "created_at" => Carbon::now()
          ]);

          General_journal_voucher::create([
              'type' => "credit",
              "account" => "P4_MAIDS_PAYROLL",
              'refNumber' => 0,
              'refCode' => $randomRefNumber,
              "voucher_type" => "Invoice Package4",
              'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
              'maid_name' => $validatedData['maid'],
              "notes" => $request->note . " " . $request->cheque,
              "amount" => $validatedData['salary'],
              "contract_ref" =>$validatedData['contract'],
              "created_by" => Auth::user()->name,
              "created_at" => Carbon::now()
          ]);
  

          if ($totalInvoiceWithoutVat > 0) {
     
              General_journal_voucher::create([
                  'type' => "credit",
                  "account" => "P4_REVENUE",
                  'refNumber' => 0,
                  'refCode' => $randomRefNumber,
                  "voucher_type" => "Invoice Package4",
                  'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                  'maid_name' => $validatedData['maid'],
                  "notes" => $request->note . " " . $request->cheque,
                  "amount" => $totalInvoiceWithoutVat,
                  "contract_ref" =>$validatedData['contract'],
                  "created_by" => Auth::user()->name,
                  "created_at" => Carbon::now()
              ]);
  
        
              General_journal_voucher::create([
                  'type' => "credit",
                  "account" => "VAT",
                  'refNumber' => 0,
                  'refCode' => $randomRefNumber,
                  "voucher_type" => "Invoice Package4",
                  'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                  'maid_name' => $validatedData['maid'],
                  "notes" => $request->note . " " . $request->cheque,
                  "contract_ref" =>$validatedData['contract'],
                  "amount" => $totalInvoiceWithoutVat * 0.05,
                  "created_by" => Auth::user()->name,
                  "created_at" => Carbon::now()
              ]);
          }
  
          UpcomingInstallment::where('id', $validatedData['id'])->update(['invoice_status' => 1 , 
          'invoice'=>$randomRefNumber,
          'updated_by'=>Auth::user()->name ,
         'updated_at'=>  Carbon::now() ,

        ]);


          DB::commit();
  
          return response()->json([
              'success' => true,
              'message' => 'Added successfully.',
          ], 201);
  
      } catch (\Exception $e) {

          DB::rollBack();
  
          return response()->json([
              'success' => false,
              'message' => 'An error occurred while processing your request.',
              'error' => $e->getMessage(),
          ], 500);
      }
  }




public function bulkStoreInstallments(Request $request)
{
    $data = $request->validate([
        'ids'   => 'required|array|min:1|max:100',
        'ids.*' => 'exists:upcoming_installments,id',
    ]);

    DB::beginTransaction();
    try {
        $installments = UpcomingInstallment::with(['contractRef.maidInfo'])
            ->whereIn('id', $data['ids'])
            ->where('invoice_status', 0)
            ->lockForUpdate()
            ->get();

        foreach ($installments as $row) {
            $amount    = $row->amount;
            $salary    = $row->contractRef->maidInfo->salary;
            $date      = $row->accrued_date;
            $maid      = $row->contractRef->maidInfo->name;
            $note      = $row->note;
            $cheque    = $row->cheque;
            $customer  = $row->contractRef->customerInfo->name;
            $contract  = $row->contract;

            if ($amount < $salary) {
                continue;
            }

            $totalInvoiceWithoutVat = ($amount - $salary) / 1.05;
            $ref = 'inv4_' . Str::random(8);

            // Debit: customer
            $customerLedger = All_account_ledger_DB::where('ledger', $customer)->first();
            if (!$customerLedger) {
                throw new \Exception("Ledger not found for account: {$customer}");
            }

            General_journal_voucher::create([
                'type'            => 'debit',
                'account'         => $customer,
                'ledger_id'       => $customerLedger->id,
                'date'            => $date,
                'refNumber'       => 0,
                'refCode'         => $ref,
                'voucher_type'    => 'Invoice Package4',
                'maid_name'       => $maid,
                'notes'           => trim("$note $cheque"),
                'amount'          => $amount,
                'invoice_balance' => $amount,
                'contract_ref'    => $contract,
                'created_by'      => Auth::user()->name,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Credit: P4_MAIDS_PAYROLL
            $payrollLedger = All_account_ledger_DB::where('ledger', 'P4_MAIDS_PAYROLL')->first();
            if (!$payrollLedger) {
                throw new \Exception("Ledger not found for account: P4_MAIDS_PAYROLL");
            }

            General_journal_voucher::create([
                'type'         => 'credit',
                'account'      => 'P4_MAIDS_PAYROLL',
                'ledger_id'    => $payrollLedger->id,
                'refNumber'    => 0,
                'refCode'      => $ref,
                'voucher_type' => 'Invoice Package4',
                'date'         => $date,
                'maid_name'    => $maid,
                'notes'        => trim("$note $cheque"),
                'amount'       => $salary,
                'contract_ref' => $contract,
                'created_by'   => Auth::user()->name,
                'created_at'   => now(),
                'updated_at'   => now()
            ]);

            // Credit: P4_REVENUE and VAT (use create instead of insert)
            if ($totalInvoiceWithoutVat > 0) {
                foreach (['P4_REVENUE', 'VAT'] as $account) {
                    $ledger = All_account_ledger_DB::where('ledger', $account)->first();
                    if (!$ledger) {
                        throw new \Exception("Ledger not found for account: {$account}");
                    }

                    $amountToInsert = $account === 'P4_REVENUE'
                        ? $totalInvoiceWithoutVat
                        : $totalInvoiceWithoutVat * 0.05;

                    General_journal_voucher::create([
                        'type'         => 'credit',
                        'account'      => $account,
                        'ledger_id'    => $ledger->id,
                        'refNumber'    => 0,
                        'refCode'      => $ref,
                        'voucher_type' => 'Invoice Package4',
                        'date'         => $date,
                        'maid_name'    => $maid,
                        'notes'        => trim("$note $cheque"),
                        'amount'       => $amountToInsert,
                        'contract_ref' => $contract,
                        'created_by'   => Auth::user()->name,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            $row->update([
                'invoice_status' => 1,
                'invoice'        => $ref,
                'updated_by'     => Auth::user()->name,
                'updated_at'     => now(),
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Bulk invoices created.',
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate bulk invoices.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    // url store/customize

    public function storeCustomized(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'contract_ref' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'net_profit' => 'required|numeric|min:0',
            'customer' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'maid'   => [
                'required',
                'string',
                Rule::exists('maids_d_b_s', 'name'),
            ],
            'id_ins' => 'required|exists:upcoming_installments,id',
           
    
        ]);

        if (intval($validatedData['amount']) !== intval($validatedData['net_profit']) + intval($validatedData['salary'])) {
            return response()->json([
                'success' => false,
                'message' => 'Debit And Credit not balanced',
            ], 422);
        }
        
        if (intval($validatedData['amount']) < intval($validatedData['salary'])) {
            return response()->json([
                'success' => false,
                'message' => 'Salary of maid cannot be more than the invoice amount.',
            ], 422);
        }
        
    
        $totalInvoiceWithoutVat = ( $validatedData['net_profit']) / 1.05;
    
        $randomRefNumber = 'inv4_' . Str::random(8);
    
  
        DB::beginTransaction();
    
        try {
       
            General_journal_voucher::create([
                'type' => "debit",
                'refNumber' => 0,
                'account' => $validatedData['customer'],
                'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                'refCode' => $randomRefNumber,
                "voucher_type" => "Invoice Package4",
                'maid_name' => $validatedData['maid'],
                "notes" => $request->note . " " . $request->cheque,
                "amount" => $validatedData['amount'],
                "contract_ref" =>$validatedData['contract_ref'],
                "invoice_balance" => $validatedData['amount'],
                "created_by" => Auth::user()->name,
                "created_at" => Carbon::now()
            ]);

            if (intval($validatedData['salary']> 0) ) {
  
            General_journal_voucher::create([
                'type' => "credit",
                "account" => "P4_MAIDS_PAYROLL",
                'refNumber' => 0,
                'refCode' => $randomRefNumber,
                "voucher_type" => "Invoice Package4",
                'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                "contract_ref" =>$validatedData['contract_ref'],
                'maid_name' => $validatedData['maid'],
                "notes" => $request->note . " " . $request->cheque,
                "amount" => $validatedData['salary'],
                "created_by" => Auth::user()->name,
                "created_at" => Carbon::now()
            ]);
        };
  
            if ($totalInvoiceWithoutVat > 0) {
       
                General_journal_voucher::create([
                    'type' => "credit",
                    "account" => "P4_REVENUE",
                    'refNumber' => 0,
                    'refCode' => $randomRefNumber,
                    "voucher_type" => "Invoice Package4",
                    'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                    "contract_ref" =>$validatedData['contract_ref'],
                    'maid_name' => $validatedData['maid'],
                    "notes" => $request->note . " " . $request->cheque,
                    "amount" => $totalInvoiceWithoutVat,
                    "created_by" => Auth::user()->name,
                    "created_at" => Carbon::now()
                ]);
    
          
                General_journal_voucher::create([
                    'type' => "credit",
                    "account" => "VAT",
                    'refNumber' => 0,
                    'refCode' => $randomRefNumber,
                    "voucher_type" => "Invoice Package4",
                    'date' => Carbon::parse($validatedData['date'])->format('Y-m-d'),
                    "contract_ref" =>$validatedData['contract_ref'],
                    'maid_name' => $validatedData['maid'],
                    "notes" => $request->note . " " . $request->cheque,
                    "amount" => $totalInvoiceWithoutVat * 0.05,
                    "created_by" => Auth::user()->name,
                    "created_at" => Carbon::now()
                ]);
            }
    
      
            UpcomingInstallment::where('id', $validatedData['id_ins'])->update(['invoice_status' => 1 , 
            'updated_by'=>Auth::user()->name ,
            'invoice'=>$randomRefNumber,
             'updated_at'=>  Carbon::now() ,
        
        ]);
        
  
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Added successfully.',
            ], 201);
    
        } catch (\Exception $e) {
  
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



        
 public function EditUpcomingInstallmentCntl ($ref_contract){

    $editUpcomingInstallment = UpcomingInstallment::where('contract',$ref_contract)->get();
    $customer_name = Category4Model::where('Contract_ref',$ref_contract)->first();
    return view('ERP.cat4.edit_upcoming_installment', compact('editUpcomingInstallment','customer_name'));

}//End method




public function updateUpcomingInstallments(Request $request)
{
try {
$installmentsData = $request->get('installments');

foreach ($installmentsData as $id => $data) {
 if ($id == 'new') {
     foreach ($data as $newData) {
         // Merge 'created_by' with the new installment data
         $newData['created_by'] = Auth::user()->name;
         
         // Create the new installment with all data
         UpcomingInstallment::create($newData);
     }
 } else {
     $installment = UpcomingInstallment::find($id);
     if ($installment) {
      
         $installment->update($data);
     }
 }
}

$notification = array(
 'message' => 'Contract updated Successfully',
 'alert-type' => 'success'
);
return redirect()->back()->with($notification);

} catch (\Exception $e) {
return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
}
}



public function deleteUpcomingInstallment($id) {
try {
$record = UpcomingInstallment::findOrFail($id);
$record->delete();

$notification = array(
 'message' => 'Deleted Successfully',
 'alert-type' => 'success'
);

return redirect()->back()->with($notification);
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
$notification = array(
 'message' => 'Somthing Wrong',
 'alert-type' => 'error'
);

return redirect()->back()->with($notification);
}

}//End Method

// url copy/upcoming/{id}
public function copyUpcomingInstallmentCntl($ref_contract)
{

    $maids = MaidsDB::where('maid_status', 'approved')
                        ->whereNull('maid_booked')
                        ->get();


                    $randomRefNumber = Str::random(5);

                    $contract = Category4Model::where('Contract_ref' ,$ref_contract )->first();

                    $copyUpcomingInstallment = UpcomingInstallment::where('invoice_status', 0)
                            ->where('contract', $ref_contract)
                            ->get();


                            $today = Carbon::now()->format('Y-m-d');


                            return view('ERP.cat4.copyUpcomingContract', compact('contract','copyUpcomingInstallment', 'randomRefNumber', 'maids', 'today'));
     }




        public function joinNewMaidCategory4ContractCntl(Request $request)
        {

            $request->validate([
                'contract_date' => 'required|date',
                'selected_customer' => 'required|string',
                'selected_maid' => 'required|string|exists:maids_d_b_s,name', 
                'installments' => 'required|array|min:6',
                'installments.*.accrued_date' => 'required|date',
                'installments.*.note' => 'nullable|string|max:255',
                'installments.*.cheque' => 'nullable|numeric',
                'installments.*.amount' => 'required|numeric|min:0',
            ], [
                'selected_maid.exists' => 'The selected maid does not exist or is invalid.',
                'installments.required' => 'The installments data is required.',
                'installments.array' => 'The installments data must be a valid array.',
                'installments.*.accrued_date.required' => 'Each installment must have an accrued date.',
                'installments.*.amount.required' => 'Each installment must have an amount specified.',
                'installments.min' => 'Each installment must have 6 installments or more.',
            ]);
        
            try {
     
                DB::beginTransaction();
        
                $installmentsData = $request->installments;
                $maid = MaidsDB::where('name', $request->selected_maid)->first();
                $customer = Customer::where('name', $request->selected_customer)->first();

                $oldContractRef =  Category4Model::with('returnInfo')
                     ->where('Contract_ref', $request->old_contract_ref)->first();

                if ($oldContractRef->note !== 'No note')     
                {
                    return redirect()->back()
                        ->withErrors(['contract' => 'The contract aleady have a replacement contract'])
                        ->withInput();
                }


                if ($oldContractRef && $oldContractRef->returnInfo && $oldContractRef->returnInfo->returned_date) {
                    $returnedDate =  Carbon::parse($oldContractRef->returnInfo->returned_date);
                    $newContractDate = Carbon::parse($request->contract_date);

                    if ($returnedDate->diffInDays($newContractDate) > 3) {
                        return redirect()->back()
                            ->withErrors(['contract_date' => 'The new contract date must be within 3 days of the returned date.'])
                            ->withInput();
                    }
                }     
        
                if ($maid->maid_status !== 'approved') {
                    return redirect()->back()
                        ->withErrors(['maid' => 'The selected maid must have an approved status to proceed.'])
                        ->withInput();
                }
        
   
                $maid->maid_status = 'hired';


        
  
                Category4Model::create([
                    'date' => date('Y-m-d', strtotime($request->contract_date)),
                    'Contract_ref' => "Join_" . $request->new_contract_ref,
                    'category' => 'category4',
                    'customer_id' => $customer->id,
                    'maid_id' =>  $maid->id,
                    'extra' => $oldContractRef->maid . "|" . $oldContractRef->Contract_ref . "| Return on " . $oldContractRef->returnInfo->returned_date,
                    'created_by' => Auth::user()->name,
                ]);

                $oldContractRef->update([
                        'note' => sprintf(
                            'Join_%s - %s - %s',
                            $request->new_contract_ref,
                            date('Y-m-d', strtotime($request->contract_date)),
                            $request->selected_maid
                        ),
                    ]);


                foreach ($installmentsData as $installment) {
                    UpcomingInstallment::create([
                        'accrued_date' => $installment['accrued_date'],
                        'customer_id' => $customer->id,
                        'note' => $installment['note'],
                        'cheque' => $installment['cheque'],
                        'contract' => "Join_" . $installment['newContractRef'],
                        'amount' => $installment['amount'],
                        'created_by' => Auth::user()->name,
                    ]);
                }

                $maid->save();
                DB::commit();
        
                return redirect()->to('/all/category4/contract')->with([
                    'message' => 'New Category 4 Contract Added Successfully',
                    'alert-type' => 'success',
                ]);
            } catch (\Exception $e) {
           
                DB::rollBack();

                Log::error('Error in joinNewMaidCategory4ContractCntl: ' . $e->getMessage(), [
                    'stackTrace' => $e->getTraceAsString(),
                ]);
        
                return redirect()->back()
                    ->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])
                    ->withInput();
            }
        }
        

    
}



