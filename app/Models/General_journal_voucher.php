<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class General_journal_voucher extends Model
{
    use HasFactory;
     
    protected $guarded = [];

    public function p4Relation()
    {   
        return $this->belongsTo(Category4Model::class, 'contract_ref', 'Contract_ref');
    }

     public function p1Relation()
    {   
        return $this->belongsTo(Package1::class, 'contract_ref', 'ref');
    }

    public function maidRelation()
    {   
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }

        public function accountLedger()
    {
        return $this->belongsTo(All_account_ledger_DB::class,'ledger_id');
    }

 
    public function creditNoteTargets()
    {
        return $this->hasMany(self::class, 'refCode', 'creditNoteRef');
    }


     public function setMaidNameAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['maid_id'] = null;
            return;
        }

        $maid = MaidsDB::where('name', $value)->first();

        $this->attributes['maid_id'] = $maid?->id; 
    }


    public function setAccountAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['ledger_id'] = null;
            return;
        }

        $ledger = All_account_ledger_DB::where('ledger', $value)->first();

        if (! $ledger) {
            Log::error("No ledger found for account: {$value}");
            throw new \InvalidArgumentException("No ledger found for account: {$value}");
        }

        $this->attributes['ledger_id'] = $ledger->id;
    }

public static function calculateCustomerClosingBalance($customer)
{
    // Pull just the ID; returns null if not found
    $ledgerId = All_account_ledger_DB::where('ledger', $customer)->value('id');
    if (!$ledgerId) {
        Log::error("No ledger found for customer: {$customer}");
        return "No ledger found";
    }

    // Sum debit/credit safely
    $result = self::selectRaw('
            SUM(CASE WHEN type = "debit"  THEN amount ELSE 0 END) AS dr,
            SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) AS cr
        ')
        ->where('ledger_id', $ledgerId)
        ->first();

    $dr = (float)($result->dr ?? 0);
    $cr = (float)($result->cr ?? 0);

    return $dr - $cr;
}

public static function calculateCustomerBalanceByLedgerId($ledgerId)
        {

    // Sum debit/credit safely
    $result = self::selectRaw('
            SUM(CASE WHEN type = "debit"  THEN amount ELSE 0 END) AS dr,
            SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) AS cr
        ')
        ->where('ledger_id', $ledgerId)
        ->first();

    $dr = (float)($result->dr ?? 0);
    $cr = (float)($result->cr ?? 0);

    return $dr - $cr;
}

    public static function calculateInvoiceBalance($inv_ref) {
        $totals = self::selectRaw('
                SUM(CASE WHEN refCode = ? AND type = "debit" THEN amount ELSE 0 END) -
                SUM(CASE WHEN receiveRef = ? AND type = "debit" THEN amount ELSE 0 END) AS balance
            ', [$inv_ref, $inv_ref])->value('balance');
    
        return $totals ?? 0;
    }


    

    public static function queryByrefCode($ref) {
        $records = self::where('refCode', $ref)->get();
        return $records;
    }
    
       
    public function customerInfo()
    {    
        return $this->belongsTo(Customer::class,'ledger_id', 'ledger_id');
    }


     public static function latestCat4InvoiceAndContract($customer , $ref){

        $lastInv = self::where('ledger_id', $customer )
        ->where('contract_ref' , $ref)
        ->where('voucher_type' , 'Invoice Package4')
        ->where('type','debit')
        ->latest()
        ->first();

        return $lastInv;

    }

  
    public static function latestCat1Contract($customer , $ref){
        $con1 = self::where('ledger_id', $customer )
        ->where('contract_ref' , $ref) 
        ->where('voucher_type' , 'Invoice Package1')
        ->where('type','debit')
        ->latest()
        ->first();
        return $con1;
    }

       public function maidInformation()
    {   
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }


      /*
    |--------------------------------------------------------------------------
    | >>> NEW: Amount-change audit hook (writes to jv_logs) <<<
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::updating(function (General_journal_voucher $voucher) {
            // Compare amounts at 2 decimals (accounting precision)
            $old = (float) ($voucher->getOriginal('amount') ?? 0);
            $new = (float) ($voucher->amount ?? 0);

            if (bccomp($old, $new, 2) === 0) {
                return; // no change in amount => no log
            }

            // Resolve denormalized names *at change time*
            $accountName = null;
            try {
                $accountName =
                    optional($voucher->accountLedger)->ledger
                    ?? optional(All_account_ledger_DB::find($voucher->ledger_id))->ledger;
            } catch (\Throwable $e) {}

            $maidName = null;
            try {
                // prefer your primary relation name
                $maidName =
                    optional($voucher->maidRelation)->name
                    ?? optional(MaidsDB::find($voucher->maid_id))->name;
            } catch (\Throwable $e) {}

            // Create the log row
            \App\Models\JvLog::create([
                'voucher_id'    => $voucher->id,
                'ref_code'      => $voucher->refCode ?? null,
                'voucher_type'  => $voucher->voucher_type ?? null,
                'line_type'     => $voucher->type ?? null,

                'ledger_id'     => $voucher->ledger_id ?? null,
                'account_name'  => $accountName,

                'maid_id'       => $voucher->maid_id ?? null,
                'maid_name'     => $maidName,

                'notes'         => $voucher->notes ?? null,

                'amount_before' => $old,
                'amount_after'  => $new,

                'changed_by'    => Auth::user()->name ?? ($voucher->updated_by ?? 'system'),
                'changed_at'    => now(),
            ]);
        });
    }


}
