<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'person',
        'expire_date',
        's3_url',
        'note',
        'created_by',
    ];

    // Treat `expire_date` as a Carbon date instance
    protected $casts = [
        'expire_date' => 'date',
    ];
}
