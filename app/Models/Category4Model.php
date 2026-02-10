<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Category4Model extends Model
{
    use HasFactory;

    protected $guarded = [];

    
    public function maidInfo()
    {    
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }

    public function installmentInfo()
    {
        return $this->hasMany(UpcomingInstallment::class, 'contract', 'Contract_ref');
    }

    public function customerInfo()
    {    
        return $this->belongsTo(Customer::class, 'customer_id');
    }


    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }


    public function returnInfo()
    {   
        return $this->belongsTo(ReturnedMaid::class, 'Contract_ref', 'contract');
    }
    

    public static function customerActivep4($customer)
    {
        $customerId = Customer::where('name', $customer)->value('id');
  
        return self::where('contract_status', 1)
                   ->where('customer_id', $customerId)
                   ->count();
    }


   public static function lastContractByMaidId($maidId)
    {
        return self::where('maid_id', $maidId)
                   ->latest()
                   ->first();
    }


    public function userInfo()

    {   
        return $this->belongsTo(User::class,'created_by', 'name');
    }



}
