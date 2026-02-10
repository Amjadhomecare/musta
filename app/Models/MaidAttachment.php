<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaidAttachment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function maidInfo()
    {   
        return $this->belongsTo(MaidsDB::class, 'maid_name', 'name');
    }

}
