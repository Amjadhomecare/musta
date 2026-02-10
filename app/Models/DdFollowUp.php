<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DirectDebit;
use App\Enum\DdFollowUps;

class DdFollowUp extends Model
{
    protected $table = 'dd_follow_ups';

    protected $fillable = [
        'dd_id',
        'attempt_number',
        'follow_up_notes',
        'follow_up_status',
        'message_sent',
        'attachment',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'follow_up_status' => DdFollowUps::class,
        'message_sent' => 'array',
        'attachment' => 'array',
    ];

    public function dd()
    {
        return $this->belongsTo(DirectDebit::class, 'dd_id');
    }

    public function directDebit()
    {
        return $this->belongsTo(DirectDebit::class, 'dd_id');
    }
}
