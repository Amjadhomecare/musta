<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\UpcomingInstallmentImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;


class blkUpCntler extends Controller
{
     /**
     * Handle bulk import of maids.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        // Validate the file input
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,txt',
        ]);

        try {
            // Wrap the import process in a transaction
            DB::transaction(function () use ($request) {
                Excel::import(new UpcomingInstallmentImport, $request->file('file'));
            });

            // Return success response
            return back()->with('success', 'imported successfully!');
        } catch (ValidationException $e) {
            // Handle validation errors
            $failures = $e->failures();

            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            return back()->withErrors($errorMessages);
        } catch (\Exception $e) {
            // Handle other exceptions
            return back()->withErrors(['error' => 'An error occurred during the import: ' . $e->getMessage()]);
        }
    }
}
