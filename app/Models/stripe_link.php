<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stripe_link extends Model
{
    use HasFactory;
    
    protected $fillable = ['url', 'maid_name', 'customer_name', 'amount', 'status', 'note', 'created_by', 'updated_by'];
}
