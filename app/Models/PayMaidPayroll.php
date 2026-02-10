<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayMaidPayroll extends Model
{
    use HasFactory;

    protected $guarded = [];


    public static function hasBeenPaid($year, $month, $maidName)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return self::where('maid', $maidName)
                   ->whereBetween('accrued_month', [$startDate, $endDate])
                   ->exists();
    }// End method
}
