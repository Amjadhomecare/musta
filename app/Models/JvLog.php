<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JvLog extends Model
{
     protected $guarded = [];
    protected $casts = [
        'changed_at'    => 'datetime',
        'amount_before' => 'decimal:2',
        'amount_after'  => 'decimal:2',
    ];

    public function voucher()
    {
        return $this->belongsTo(General_journal_voucher::class, 'voucher_id');
    }
}
