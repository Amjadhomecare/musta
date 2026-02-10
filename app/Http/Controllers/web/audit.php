<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\General_journal_voucher;

class audit extends Controller
{
    public function  allJvApi(){
        
    $dataValidate = request()->validate(['date_start' => 'required|date',
    
    'date_end' => 'required|date']);

        $jv = General_journal_voucher::whereBetween('date', [$dataValidate['date_start'], $dataValidate['date_end']])->get();
       
        return response()->json($jv);
    }
}
