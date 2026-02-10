<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PayMaidPayrollsImport;

class blkPayroll extends Controller         
{
   
    public function store(Request $request)
    {
        $request->validate([
           'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        Excel::import(new PayMaidPayrollsImport, $request->file('file'));

        return back()->with('success', 'Payroll sheet processed.');
    }
}
