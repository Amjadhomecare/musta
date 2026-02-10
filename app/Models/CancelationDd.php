<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CancelationDd extends Model
{
    use HasFactory;

    protected $table = 'cancelation_dds';

    // Task constants
    const TASK_REFUND_ONLY = 1;
    const TASK_CANCELATION_AND_REFUND = 2;
    const TASK_CANCELATION_ONLY = 3;

    // Status constants
    const STATUS_REQUESTED = 1;
    const STATUS_PENDING = 2;
    const STATUS_APPROVED = 3;

    protected $fillable = [
        'dd_id',
        'task',
        'status',
        'note',
        'meta',
        'comment',
        'created_by',
        'update_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'comment' => 'array',
    ];

    /**
     * Get the task label
     */
    public function getTaskLabelAttribute()
    {
        return match($this->task) {
            self::TASK_REFUND_ONLY => 'Refund Only',
            self::TASK_CANCELATION_AND_REFUND => 'Cancellation and Refund',
            self::TASK_CANCELATION_ONLY => 'Cancellation Only',
            default => 'Unknown',
        };
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            default => 'Unknown',
        };
    }

    /**
     * Get all task options
     */
    public static function getTaskOptions()
    {
        return [
            self::TASK_REFUND_ONLY => 'Refund Only',
            self::TASK_CANCELATION_AND_REFUND => 'Cancellation and Refund',
            self::TASK_CANCELATION_ONLY => 'Cancellation Only',
        ];
    }

    /**
     * Get all status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    public function directDebit()
    {
        return $this->belongsTo(DirectDebit::class, 'dd_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'update_by');
    }
}
