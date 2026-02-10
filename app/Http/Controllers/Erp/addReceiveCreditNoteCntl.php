<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Log;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use App\Models\categoryOne;
use App\Models\MaidsDB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Mail\PaymentThankYouMail;
use Illuminate\Support\Facades\Mail;


class addReceiveCreditNoteCntl extends Controller
{

    //URL /receive-payment
    public function receivedFromCntl(Request $request) {
    
        $response = ['status' => 'error', 'message' => 'Something Went Wrong, Cannot save the Payment'];
    
        try {
          
            $validatedData = $request->validate([
                'transactionID' => 'required|exists:general_journal_vouchers,id',
                'date' => 'required|date',
                'maidName' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:255',
                'receivedFromLedger' => 'required|string|max:255',
                'amountReceived' => 'required|numeric|min:0',
                'invRef' => 'required|string|max:255',
                'customerName' => 'required|string|max:255',
            ]);
            
       

            DB::beginTransaction();
    
            $randomRefNumber = 'RV_'.Str::random(6);
    
       
            $transaction = General_journal_voucher::findOrFail($request->transactionID);
    
       
            if ($request->amountReceived > $transaction->invoice_balance) {
                
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Amount received cannot be more than the invoice balance'
                ], 400);
            }
            General_journal_voucher::create([
                "date" => $request->date,
                "refNumber" => 0,
                "refCode" => $randomRefNumber,
                "voucher_type" => "Receipt Voucher",
                "type" => "debit",
                "maid_name" => $request->maidName ?? "No data",
                "notes" => $request->note,
                "account" => $request->receivedFromLedger,
                "amount" => $request->amountReceived,
                "receiveRef" => $request->invRef,
                "created_by" => Auth::user()->name,
                "created_at" => Carbon::now()
            ]);
    
  
            General_journal_voucher::create([
                "date" => $request->date,
                "refNumber" => 0,
                "refCode" => $randomRefNumber,
                "voucher_type" => "Receipt Voucher",
                "type" => "credit",
                "maid_name" => "No data",
                "notes" => $request->note,
                "account" => $validatedData['customerName'],
                "amount" => $request->amountReceived,
                "receiveRef" => $request->invRef,
                "created_by" => Auth::user()->name,
                "created_at" => Carbon::now()
            ]);
    
     
            $transaction->receiveRef = $randomRefNumber;
            $transaction->notes = $request->note;
            $transaction->invoice_balance = $transaction->invoice_balance - $request->amountReceived;
    
            if ($transaction->save()) {
                DB::commit(); 


                $response = ['status' => 'success', 'message' => 'Payment successfully done!'];
            } else {
                DB::rollBack(); // Roll back the transaction
            }
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            // Log the error or handle it as per your needs
            DB::rollBack(); // Ensure to roll back on error
            $response['message'] = $e->getMessage();
        }
    
        // Return the response
        return response()->json($response, $response['status'] === 'success' ? 201 : 500);
    }
    
    

// route '/get-credit-note-data'

    public function getCreditNoteData(Request $request) {

        $refCode = $request->input('refCode');
        $data = General_journal_voucher::with(['maidRelation' , 'accountLedger'])
        ->where('refCode' , $refCode)->get();
        return response()->json($data);
    } //end the method



// URL /store-credit-note-data
    public function creditNoteFromTypingCntl(Request $request){
        try {
            $randomRefNumber = 'cr_'.Str::random(6);
            $customerIDinDataBase = General_journal_voucher::findOrFail($request->refCode[0]);

               if ($customerIDinDataBase->creditNoteRef !== "No Data") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error Credit note is existed for this invoice'
                ], 400);
            }

            $accounts = $request->input('account');
            $accountTypes = $request->input('accountType');
            $accountAmounts = $request->input('accountAmount');

                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($accountTypes as $index => $type) {
                    if ($type === "debit") {
                        $totalDebit += $accountAmounts[$index];
                    } else if ($type === "credit") {
                        $totalCredit += $accountAmounts[$index];
                    }
                }
                // Check if totals match
                if ($totalDebit !== $totalCredit) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error Total debit and credit amounts not match'
                    ], 400);
                }


            if(is_array($accounts) && count($accounts) > 0){
                foreach ($accounts as $index => $account) {
                    $validator = Validator::make([
                        'account' => $accounts[$index],
                        'type' => $accountTypes[$index],
                        'amount' => $accountAmounts[$index],

                    ], [
                        'account' => 'required|string',
                        'type' => 'required|string',
                        'amount' => 'required|string',

                    ]);

                    if ($validator->fails()) {

                        return response()->json([
                            'status' => 'error',
                            'message' => 'Validation errors',
                            'errors' => $validator->errors()
                        ], 422);
                    }

                    if($accountTypes[$index] ==="credit"){
                        $accountTypes[$index] = "debit";

                    }else{

                        $accountTypes[$index] = "credit";
                    }


                    General_journal_voucher::create([
                        'date' => Carbon::today()->toDateString(),
                        'refCode' => $randomRefNumber,
                        'refNumber' => 0,
                        'voucher_type' => 'Credit note',
                        'account' => $accounts[$index],
                        'type' => $accountTypes[$index],
                        'amount' => $accountAmounts[$index],


                    ]);
                }
            }

            $customerIDinDataBase->creditNoteRef = $randomRefNumber;

            if ($customerIDinDataBase->save())
                return response()->json(['status' => 'success', 'message' => 'Credit Note successfully processed'], 201);

            return response()->json(['status' => 'error', 'message' => 'Something Went Wrong, Cannot save the Payment'], 500);
        } catch (\Exception $ex) {
            return response(['success' => false, 'message' => __($ex->getMessage())]);
        }
    }//End method

    
// URL /maids-payment
    public function receivedFromMaidsSalesCntl(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'transactionID' => 'required|exists:general_journal_vouchers,id',
            'amountReceived' => 'required|numeric|min:0',
            'date' => 'required|date',
            'maidName' => 'required|string|max:255',
            'note' => 'nullable|string',
            'receivedFromLedger' => 'required|string|max:255',
            'customerName' => 'required|string|max:255',
            'refInv' => 'required|string|max:255',
        ]);
    
        // Fetch the transaction by ID
        $transaction = General_journal_voucher::findOrFail($validated['transactionID']);
    
        // Check if the received amount exceeds the invoice balance
        if ($validated['amountReceived'] > $transaction->invoice_balance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Amount received cannot be more than the invoice balance'
            ], 400);
        }

        // Use a database transaction to ensure atomicity
        DB::transaction(function () use ($validated, $transaction) {

    
            
            $randomRefNumber = 'RV_'.Str::random(6);
    
            // Update the transaction
            $transaction->receiveRef = $randomRefNumber;
            $transaction->invoice_balance -= $validated['amountReceived'];
            $transaction->save();
    
            // Create the debit entry
            General_journal_voucher::create([
                'date' => $validated['date'],
                'refNumber' => 0,
                'refCode' => $randomRefNumber,
                'voucher_type' => 'Receipt Voucher',
                'type' => 'debit',
                'maid_id' => $transaction['maid_id'] ?? null,
                'notes' => $validated['note'],
                'account' => $validated['receivedFromLedger'],
                'amount' => $validated['amountReceived'],
                'receiveRef' => $validated['refInv'],
                'created_by' => Auth::user()->name,
                'created_at' => Carbon::now(),
            ]);
    
            // Create the credit entry
            General_journal_voucher::create([
                'date' => $validated['date'],
                'refNumber' => 0,
                'refCode' => $randomRefNumber,
                'voucher_type' => 'Receipt Voucher',
                'type' => 'credit',
                'maid_id' => $transaction['maid_id'] ?? null,
                'notes' => $validated['note'],
                'account' => $validated['customerName'],
                'amount' => $validated['amountReceived'],
                'receiveRef' => $validated['refInv'],
                'created_by' => Auth::user()->name,
                'created_at' => Carbon::now(),
            ]);
        });
    
        return response()->json([
            'status' => 'success',
            'message' => 'Payment successfully done!'
        ], 201);
    }

// url /apply-credit
public function applyCredit(Request $request)
{
    $validated = $request->validate([
        'transactionID' => 'required|exists:general_journal_vouchers,id',
    ]);

    $transaction = General_journal_voucher::findOrFail($validated['transactionID']);

    $account_balance = General_journal_voucher::calculateCustomerBalanceByLedgerId($transaction->ledger_id);

    // If balance is negative, treat as 0
    $newBalance = $account_balance < 0 ? 0 : $account_balance;

    if ($transaction->invoice_balance < $newBalance) {
        return response()->json([
            'status' => 'error',
            'message' => 'customer does not have a credit'
        ], 401);
    }

    $transaction->invoice_balance = $newBalance;
    $transaction->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Credit applied successfully, and invoice balance set to ' . $newBalance
    ], 201);
}


// url /apply-credit-bulk
public function applyCreditBulk(Request $request)
{
    $validated = $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'integer|distinct|exists:general_journal_vouchers,id',
    ]);

    $ids = $validated['ids'];

    // Update in one query using JOIN with balance subquery
    $affected = DB::table('general_journal_vouchers as g')
        ->join(DB::raw('(
            SELECT ledger_id, GREATEST(SUM(CASE WHEN type="debit" THEN amount ELSE -amount END), 0) as new_balance
            FROM general_journal_vouchers
            GROUP BY ledger_id
        ) b'), 'b.ledger_id', '=', 'g.ledger_id')
        ->whereIn('g.id', $ids)
        ->whereColumn('g.invoice_balance', '>=', 'b.new_balance')
        ->update(['g.invoice_balance' => DB::raw('b.new_balance')]);

    return response()->json([
        'status'   => 'success',
        'updated'  => $affected,
        'requested'=> count($ids),
    ], 200);
}



    ///Route : credit-note-maidssales
public function creditNoteFromMaidsSalesCntl(Request $request){
    $randomRefNumber = 'cr_'.Str::random(5);
    $customerIDinDataBase = General_journal_voucher::findOrFail($request->refCode[0]);


    if ($customerIDinDataBase->creditNoteRef !== "No Data") {
        return response()->json([
            'status' => 'error',
            'message' => 'Error Credit note is existed for this invoice'
        ], 400);
    }

    $accounts = $request->input('account');
    $accountTypes = $request->input('accountType');
    $accountAmounts = $request->input('accountAmount');
    $maidName = $request->input('maidName');

    

    if (!$accounts || !$accountTypes || !$accountAmounts || !$maidName) {
        return response()->json([
            'status' => 'error',
            'message' => 'Missing input data'
        ], 400);
    }

    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($accountTypes as $index => $type) {
        if ($type === "debit") {
            $totalDebit += $accountAmounts[$index];
        } else if ($type === "credit") {
            $totalCredit += $accountAmounts[$index];
        }
    }

    if ($totalDebit !== $totalCredit) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error Total debit and credit amounts not match'
        ], 400);
    }

    if (is_array($accounts) && count($accounts) > 0) {
        foreach ($accounts as $index => $account) {
            if (!isset($accounts[$index], $accountTypes[$index], $accountAmounts[$index], $maidName[$index])) {
                continue;
            }

            $validator = Validator::make([
                'account' => $accounts[$index],
                'type' => $accountTypes[$index],
                'amount' => $accountAmounts[$index],
                'maid_name' => $maidName[$index]
            ], [
                'account' => 'required|string',
                'type' => 'required|string',
                'amount' => 'required|string',
                'maid_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($accountTypes[$index] === "credit") {
                $accountTypes[$index] = "debit";
            } else {
                $accountTypes[$index] = "credit";
            }

            General_journal_voucher::create([
                'date' => Carbon::today(),
                'refCode' => $randomRefNumber,
                'refNumber' => 0,
                'voucher_type' => 'Credit note',
                'maid_name' => $maidName[$index],
                'account' => $accounts[$index],
                'type' => $accountTypes[$index],
                'notes' => $request->input('note'),
                'amount' => $accountAmounts[$index],
                'created_by' => Auth::user()->name
            ]);
        }
    }

    General_journal_voucher::where('refCode', $customerIDinDataBase->refCode)
    ->update(['creditNoteRef' => $randomRefNumber]);

    return response()->json([
        'status' => 'success',
        'message' => 'Credit Note successfully processed'
    ], 201);
}//End method


  public function viewReceiptVoucherCat1($id){

          $dr =  General_journal_voucher::where('refCode' , $id)->where('type' , 'debit')->first();
          $cr =  General_journal_voucher::where('refCode' , $id)->where('type' , 'credit')->first();
         return view('ERP.template.receipt_voucher_cat1' , compact('dr','cr'));
  }

    public function viewReceiptVoucherTyping($id){
        $allVouchers = General_journal_voucher::where('refCode', $id)
            ->orWhere('receiveRef', $id)
            ->get();

        $dataDr = $allVouchers->where('refCode', $id)->where('type', 'debit');
        $invData = $allVouchers->where('receiveRef', $id);

        return view('ERP.typing.template.receipt_voucher_typing', compact('dataDr', 'invData'));
    }




    public function cashierReceiptVoucher (){
    $date =date('Y-m-d');
    $cashAndBank = All_account_ledger_DB::where('group' , 'Current assets')->get();
    $customer = All_account_ledger_DB::where('group','CUSTOMER')->get();
    $maids = MaidsDB::all();

    return view('ERP.accounting.all_RV',compact('date','cashAndBank','customer','maids'));
}


    public function getContractDetailsForReceivePayment($contractRef) {
        $contract = categoryOne::where('contract_ref', $contractRef)->first();

        return response()->json([
            'customer' => $contract->customer,
            'maid' => $contract->maid,
            'closing_balance' => General_journal_voucher::calculateCustomerClosingBalance($contract->customer)

        ]);
    }

    public function getInvoice4RefDetailsForReceivePayment($invRef4) {
        $cat4 = General_journal_voucher::where('voucher_type','Invoice Package4')->where('type', 'debit')->where('refCode',$invRef4)->first();

        return response()->json([
            'customer' => $cat4->account,
            'maid' => $cat4->maid_name,
            'closing_balance' => General_journal_voucher::calculateCustomerClosingBalance($cat4->account)

        ]);
    }


    public function storeCashierRV (Request $request){

      DB::transaction(function () use ($request) {
        $randomRefNumber = 'RV_'.Str::random(7);

        General_journal_voucher::create([
            "date" => $request->transaction_date,
            "refNumber" => 0,
            "refCode" => $randomRefNumber,
            "voucher_type" => "Receipt Voucher",
            "type" => "debit",
            "maid_name" => $request->maid_name,
            "notes" => $request->note ,
            "account" => $request->receivedFromLedger,
            "amount" => $request->amount_to_receive,
            "created_by" => Auth::user()->name,
            "created_at" => Carbon::now()
        ]);

        General_journal_voucher::create([
            "date" => $request->transaction_date,
            "refNumber" => 0,
            "refCode" => $randomRefNumber,
            "voucher_type" => "Receipt Voucher",
            "type" => "credit",
            "maid_name" => $request->maid_name,
            "notes" => $request->note ,
            "account" => $request->customer_name,
            "amount" => $request->amount_to_receive,
            "created_by" => Auth::user()->name,
            "created_at" => Carbon::now()
        ]);

       });
        $notification = array(
            'message' => 'New receipt voucher added',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }


    public function getAllRV(Request $request)
    {
        if ($request->ajax()) {
            $query = General_journal_voucher::where('voucher_type', 'Receipt Voucher')
                                            ->where('type', 'credit');

            if (!empty($request->input('fromDate')) && !empty($request->input('toDate'))) {
                $query = $query->whereBetween('date', [$request->input('fromDate'), $request->input('toDate')]);
            }

            $data = $query->get()->map(function ($item) {
                $item->closing_balance = General_journal_voucher::calculateCustomerClosingBalance($item->account);
                return $item;
            });

            return DataTables::of($data)
                    ->addIndexColumn()
                    ->make(true);
        }
    } // end method

    public function viewReceiptVoucherCashier($id){

        $rv =  General_journal_voucher::where('refCode' , $id)->where('type' , 'credit')->first();

        $rvDr =  General_journal_voucher::where('refCode' , $id)->where('type' , 'debit')->first();

       return view('ERP.accounting.template.receipt_voucher_cashier' , compact('rv','rvDr'));
    }

}//end the class
