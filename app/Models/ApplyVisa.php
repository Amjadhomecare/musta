<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplyVisa extends Model
{
   
    protected $fillable = [
        'date',
        'maid_id',
        'service',
        'document',
        'note',
        'status',
        'managment_approval',
        'created_by',
        'updated_by',
        'comments'
    ];

    protected $casts = [
        'date' => 'date',
        'document' => 'array',
        'comments' => 'array',

    ];

    public function maid()
    {
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }

     public function statusLogs()
    {
        return $this->hasMany(ApplyVisaStatusLog::class, 'apply_visa_id')
            ->orderBy('created_at', 'asc');
    }
}
