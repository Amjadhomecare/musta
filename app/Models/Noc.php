<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noc extends Model
{
     protected $guarded = [];

     // In Noc.php model
public function maid()
{
    return $this->belongsTo(MaidsDB::class, 'maid_name', 'name');
}

public function customer()
{
   return $this->belongsTo(Customer::class, 'customer_name', 'name');

}

 protected $casts = [
        'extra_data' => 'array',
    ];

}