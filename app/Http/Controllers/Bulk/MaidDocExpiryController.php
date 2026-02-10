<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Imports\MaidDocExpiryImport;
use App\Imports\PassportDocExpiryImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MaidDocExpiryController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new MaidDocExpiryImport, $request->file('file'));

        return back()->with('success', 'Maid document expiry imported successfully.');
    }

    public function importPassportExpiry(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv',
        ]);

        try {
            Excel::import(new PassportDocExpiryImport, $request->file('file'));
            return back()->with('success', 'Passport & Visa/EID expiry imported successfully.');
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return back()->with('error', 'Invalid Excel file format. Please ensure the file is a valid .xlsx or .xls file (not CSV). Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
