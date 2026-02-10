<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Imports\MaidsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class blkMaidCntl extends Controller
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
            'file' => 'required|mimes:xlsx,csv',
        ]);

        try {
            // Wrap the import process in a transaction
            DB::transaction(function () use ($request) {
                Excel::import(new MaidsImport, $request->file('file'));
            });

            // Return success response
            return back()->with('success', 'Maids imported successfully!');
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
