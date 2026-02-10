<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WiredTransfer extends Model
{
    protected $table = 'wired_transfers';

    const PENDING = 0;
    const COMPLETED = 1;
    const unknown = 2;
    const not_found = 3;
 
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::unknown => 'Unknown',
            self::not_found => 'Not Found',
            default => 'Unknown',
        };
    }  

    protected $fillable = [
        'customer_id',
        'url',
        'status',
        'amount_value',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount_value' => 'decimal:2',
        'status' => 'integer',
    ];

    /**
     * Relationship to Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
