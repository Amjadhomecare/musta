<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetWorkLink extends Model
{
    protected $fillable = [
        'customer_id',
        'maid_id',
        'gateway_reference',
        'order_reference',
        'outlet_ref', 
        'expiry_date',
        'transaction_type',
        'amount_value',
        'self_url',
        'payment_url',
        'email_data_url',
        'resend_url',
        'skip_email_notification',
        'status',
        'paid_at',
        'note',
        'raw_request',
        'raw_response',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'paid_at' => 'datetime',
        'skip_email_notification' => 'boolean',
        'raw_request' => 'array',
        'raw_response' => 'array',
        'status' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING  = 0;
    const STATUS_PAID     = 1;
    const STATUS_FAILED   = 2;
    const STATUS_EXPIRED  = 3;
    const STATUS_CANCELED = 4;
    const STATUS_REFUNDED = 5;

    public function getStatusTextAttribute()
{
    return match ($this->status) {
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_PAID     => 'Paid',
        self::STATUS_FAILED   => 'Failed',
        self::STATUS_EXPIRED  => 'Expired',
        self::STATUS_CANCELED => 'Canceled',
        self::STATUS_REFUNDED => 'Refunded',
        default               => 'Unknown',
    };
}

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // ✔ Correct Customer relationship
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // ✔ Correct MaidsDB relationship
    public function maid()
    {
        return $this->belongsTo(MaidsDB::class, 'maid_id');
    }
}
