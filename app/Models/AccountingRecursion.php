<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingRecursion extends Model
{
      protected $fillable = [
        'name',
        'ledger_id',
        'post_type',
        'recursion',
        'recursion_number',
        'amount',
        'note',
        'start_date',
        'last_run_at',
    ];

    protected $casts = [
        'recursion'        => 'integer',
        'recursion_number' => 'integer',
        'amount'           => 'decimal:2',
        'start_date'       => 'date',
        'last_run_at'      => 'datetime',
    ];

    
}
