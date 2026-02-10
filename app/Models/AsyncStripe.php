<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsyncStripe extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function subInfo(){
        return $this->belongsTo(AsyncSubStripe::class , 'cus_str_id','cus_id' );
    }
}
