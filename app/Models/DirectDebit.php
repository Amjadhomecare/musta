<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DdFollowUp;

class DirectDebit extends Model
{
    // Active constants
    const ACTIVE_YES = 0;
    const ACTIVE_CANCELLED = 1;

    // Status constants
    const STATUS_CREATED = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_PENDING = 2;
    const STATUS_REJECTED = 3;
    const STATUS_RESIGN_REQUESTED = 4;

    protected $fillable = [
        'ref',
        'payment_frequency',
        'commences_on',
        'expires_on',
        'iban',
        'account_title',
        'account_type',
        'paying_bank_name',
        'paying_bank_id',
        'customer_type',
        'customer_id_no',
        'fixed_amount',
        'customer_id_type',
        'email',
        'phone',
        'extra',
        'status',
        'note',
        'created_at',
        'updated_at',
        'customer_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'commences_on'     => 'date',
        'expires_on'       => 'date',
        'fixed_amount'     => 'decimal:2',
        'extra'            => 'array',
    ];

    /**
     * Get the active status label
     */
    public function getActiveLabelAttribute()
    {
        return match($this->active) {
            self::ACTIVE_YES => 'Active',
            self::ACTIVE_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_CREATED => 'Created',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RESIGN_REQUESTED => 'Resign Requested',
            default => 'Unknown',
        };
    }

    /**
     * Get all active options
     */
    public static function getActiveOptions()
    {
        return [
            self::ACTIVE_YES => 'Active',
            self::ACTIVE_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get all status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_CREATED => 'Created',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RESIGN_REQUESTED => 'Resign Requested',
        ];
    }

       public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cancelationDd()
    {
        return $this->hasOne(CancelationDd::class, 'dd_id', 'id');
    }   
    
    public function refundDd()
    {
        return $this->hasOne(RefundDd::class, 'dd_id', 'id');
    }

    public function followUp()
    {
        return $this->hasOne(DdFollowUp::class, 'dd_id');
    }
}
