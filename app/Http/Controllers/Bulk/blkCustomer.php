<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Imports\CustomersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class blkCustomer extends Controller
{
    /**
     * Handle the import of customers from an Excel file.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
       
            DB::transaction(function () use ($request) {

                Excel::import(new CustomersImport, $request->file('file'));
            });

 
            return back()->with('success', 'Customers and account ledgers imported successfully!');
        } catch (ValidationException $e) {
        
            $failures = $e->failures();

            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            return back()->withErrors($errorMessages);
        } catch (\Exception $e) {
          
            return back()->withErrors(['error' => 'An error occurred during the import: ' . $e->getMessage()]);
        }
    }
}
