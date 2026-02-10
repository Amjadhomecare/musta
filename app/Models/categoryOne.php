<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class categoryOne extends Model
{ 
    use HasFactory;

    protected $guarded = [];

    public static function customerActivep1($customer)
    {
        $customerId = Customer::where('name', $customer)->value('id');
        return self::where('contract_status', 1)
                   ->where('customer_id', $customerId)
                   ->count();
    }

    public function maidInfo()
    {   
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }

    public function returnInfo()
    {   
        return $this->belongsTo(maidReturnCat1::class, 'contract_ref', 'contract');
    }

    public function customerInfo()
    {   
        return $this->belongsTo(Customer::class, 'customer_id');
    }




}
