<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class maidsFilter extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function maidsDB()
    {
        return $this->belongsTo(MaidsDB::class, 'maid_id', 'id');
    }
}
