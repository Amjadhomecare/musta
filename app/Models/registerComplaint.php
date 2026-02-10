<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class registerComplaint extends Model
{
    use HasFactory;
    protected $guarded = [];

      public function customerRelation()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function maidRelation()
    {
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }



    public static function getComplainByCustomer($name)
    {
        $customerId = Customer::where('name', $name)->value('id');
        return static::where('customer_id', $customerId)->count();
    }
  

}
