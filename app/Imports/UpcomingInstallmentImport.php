<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Models\UpcomingInstallment;
use App\Models\Customer;
use Auth;

class UpcomingInstallmentImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Handle the collection of rows.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $user = Auth::user()->name;

        DB::transaction(function () use ($rows, $user) {
            foreach ($rows as $row) {
                // Normalize customer name (from Excel)
                $customerName = strtoupper(preg_replace('/\s+/', ' ', trim($row['customer'])));

                // Fetch customer_id from DB
                $customer = Customer::whereRaw('TRIM(UPPER(name)) = ?', [$customerName])->first();

                if (!$customer) {
                    // Skip or throw if not found
                    continue;
                }

                UpcomingInstallment::create([
                    'accrued_date' => $row['accrued_date'],
                    'customer_id'  => $customer->id,
                    'note'         => $row['note'],
                    'cheque'       => $row['cheque'],
                    'contract'     => trim($row['contract']),
                    'amount'       => $row['amount'],
                    'created_by'   => $user ?? null,
                    'created_at'   => $row['created_at'] ?? now()
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
            'accrued_date' => 'required|date_format:Y-m-d',
            'customer'     => 'required|string', 
            'note'         => 'nullable|string',
            'cheque'       => 'nullable',
            'contract'     => 'required|exists:category4_models,Contract_ref',
            'amount'       => 'required|numeric',
            'created_by'   => 'nullable|string',
            'created_at'   => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'accrued_date.required'   => 'The accrued date field is required.',
            'accrued_date.date_format'=> 'The accrued date field must be in the format Y-m-d.',
            'customer.required'       => 'The customer field is required.',
            'note.string'             => 'The note field must be a string.',
            'cheque.string'           => 'The cheque field must be a string.',
            'contract.required'       => 'The contract field is required.',
            'contract.exists'         => 'The contract must exist in the category4_models database.',
            'amount.required'         => 'The amount field is required.',
            'amount.numeric'          => 'The amount field must be a number.',
            'created_by.string'       => 'The created by field must be a string.',
            'created_at.date_format'  => 'The created at field must be in the format Y-m-d.',
        ];
    }
}
