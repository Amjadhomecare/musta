<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $fillable = [
        'customer_signature_url',
        'staff_signature_url',
        'customer_name',
        'maid_name',
        'checked',
        'note',
        'created_by',
        'updated_by',
    ];
}
