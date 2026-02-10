<?php

namespace App\Imports;

use App\Models\All_account_ledger_DB;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Handle the collection of rows.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                
                $row = $row->merge([
                    'name' => strtoupper(preg_replace('/\s+/', ' ', trim($row['name']))),
                ]);
    
                $customer = Customer::create([
                    'name' => $row['name'],
                    'note' => $row['note'] ?? 'No data',
                    'phone' => $row['phone'],
                ]);

                All_account_ledger_DB::create([
                    'ledger' => $customer->name, 
                    'class' => 'Account Receivable', 
                    'group' => 'customer', 
                    'note' => $customer->phone, 
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
            'name' => 'required|unique:customers,name',
            'phone' => 'required|unique:customers,phone',
            'note' => 'nullable|string',
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
            'name.unique' => 'The customer name must be unique.',
            'phone.unique' => 'The phone number must be unique.',
        ];
    }
}
