<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedMaid extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function category4Model()
    {
        return $this->hasOne(Category4Model::class, 'Contract_ref', 'contract');
    }

    public function maidInfo()
    {    
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }


    public function customerInfo()
    {   
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
}
