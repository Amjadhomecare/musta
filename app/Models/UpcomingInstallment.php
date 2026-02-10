<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpcomingInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contractRef()
    {           
        return $this->belongsTo(Category4Model::class, 'contract', 'Contract_ref');
    }


    public function invoiceRef()
    {           
        return $this->belongsTo(General_journal_voucher::class, 'invoice', 'refCode');
    }

    public function customerInfo()
    {           
        return $this->belongsTo(Customer::class, 'customer_id');
    }



}
