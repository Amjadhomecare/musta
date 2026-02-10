<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdvanceAndDedcutiotMaids extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function getNoteDeductionAllowanceByMaidMonthYear($maid, $month,$year)
    {

    
    
        $record = self::where('maid', $maid)
                      ->whereYear('date' , $year)
                      ->whereMonth('date', $month)
                      ->first();
    
 
        // Return only the deduction amount, or NULL if no record is found
        return $record ? $record : null;
    }
    


}
