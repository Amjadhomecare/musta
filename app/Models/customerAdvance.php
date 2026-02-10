<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customerAdvance extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function customerInfo(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function maidInfo(){
        return $this->belongsTo(MaidsDB::class,'maid_id');
    }
}


