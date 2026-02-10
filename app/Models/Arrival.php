<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arrival extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function getPendingCount()
    {
        return static::where('status', 0)->count();
    }

    public function maidInfo()
    {    
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }
}
