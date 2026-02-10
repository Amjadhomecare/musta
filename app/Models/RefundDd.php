<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundDd extends Model
{
  

    const STATUS_REQUESTED = 0;
    const STATUS_APPROVED = 1;


    protected $fillable = [
        'dd_id',
        'amount',
        'note',
        'status',
        'created_by',
        'update_by',
    ];


    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_APPROVED => 'Approved',
            default => 'Unknown',
        };
    }   

    public function directDebit()
    {
        return $this->belongsTo(DirectDebit::class, 'dd_id');
    }   

    /**
     * Get the count of pending refunds (status = requested)
     */
    public static function getPendingCount()
    {
        return self::where('status', self::STATUS_REQUESTED)->count();
    }

}
