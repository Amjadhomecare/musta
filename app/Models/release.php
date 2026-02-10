<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class release extends Model
{
    use HasFactory;

    public static function getPendingCount()
    {
        return static::where('status', 0)->count();
    }

    public function maidInfo()
    {    
        return $this->belongsTo(MaidsDB::class, 'name', 'name');
    }
}
