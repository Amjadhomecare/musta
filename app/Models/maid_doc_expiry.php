<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class maid_doc_expiry extends Model
{
    use HasFactory;
    
    protected $table = 'maid_doc_expiries';
    protected $guarded = [];

    public function maidInfo()
    {
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }
}
