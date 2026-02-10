<?php

namespace App\Imports;

use App\Models\MaidsDB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class MaidsImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Handle the collection of rows from the Excel file.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                // Clean and format the name: trim, remove extra spaces, and capitalize
                $row = $row->merge([
                    'name' => strtoupper(preg_replace('/\s+/', ' ', trim($row['name']))),
                ]);
    
                // Skip processing if the name is empty or null after cleaning
                if (empty($row['name'])) {
                    Log::warning('Skipped row with empty name: ' . json_encode($row));
                    continue;
                }
    
                // Check for existing record with the cleaned name
                if (MaidsDB::where('name', $row['name'])->exists()) {
                    Log::info('Duplicate name skipped: ' . $row['name']);
                    continue;
                }
    
                // Insert valid rows into the database
                MaidsDB::create([
                    'name' => $row['name'],
                    'salary' => $row['salary'],
                    'maid_status' => $row['maid_status'],
                    'maid_type' => $row['maid_type'],
                    'payment' => $row['payment'],
                    'note' => $row['note'],
                    'agency' => $row['agency'],
                    'nationality' => $row['nationality'],
                    'age' => $row['age'],
                    'created_by' => $row['created_by'],
                    'created_at' => $row['created_at'],
                ]);
            }
        });
    }
    
    /**
     * Validation rules for each row.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|unique:maids_d_b_s,name',
            'salary' => 'required|numeric',
            'maid_status' => 'required|in:pending,approved,hired',
            'nationality' => 'required|string',
            'maid_type' => 'required|in:p1,HC,Direct hire',
            'payment' => 'required|in:cash,bank',
            'age' => 'required|integer|min:18',
            'agency' => 'required|string|exists:all_account_ledger__d_b_s,ledger',
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'name.unique' => 'The maid name must be unique.',
            'salary.required' => 'The salary field is required.',
            'maid_status.in' => 'The maid status must be one of: pending, approved, hired.',
            'nationality.in' => 'required|in:Indonesia,Ethiopia,Philippines,Myanmar,Kenya,Uganda,Sri_Lanka,Tanzanian,India,Ghana,nepal,pakistan',
            'maid_type.in' => 'The maid type must be one of: p1, HC, Direct hire.',
            'payment.in' => 'The payment method must be one of: cash, bank.',
            'age.required' => 'The age field is required.',
            'agency.required' => 'The agency field is required.',
        ];
    }
}
