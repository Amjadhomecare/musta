<?php

namespace App\Imports;

use App\Models\CategoryOne;
use App\Models\MaidsDB;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PackageOneImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Handle the collection of rows.
     *
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {

                $row = $row->merge([
                    'maid' => strtoupper(preg_replace('/\s+/', ' ', trim($row['maid']))),
                    'customer' => strtoupper(preg_replace('/\s+/', ' ', trim($row['customer']))),

                ]);

                $maidId = MaidsDB::where('name', $row['maid'])->first()->id;
                $customerId = Customer::where('name', $row['customer'])->first()->id;

                CategoryOne::create([
                    'contract_ref' => 'p1_' . str::random(7),
                    'started_date' => $row['started_date'],
                    'ended_date' => $row['ended_date'],
                    'customer_id' => $customerId,
                    'maid_id' => $maidId,
                    'category' => 'p1',
                    'nationality' => $row['nationality'],
                    'amount' => $row['amount'],
                    'invoice_ref' => 'p1_' . str::random(7),
                    'created_by' => $row['created_by'] ?? null,
                    'created_at' => $row['created_at'] ?? now(),
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
            'started_date' => 'required|date_format:Y-m-d',
            'ended_date' => 'required|date_format:Y-m-d',
            'customer' => 'required|exists:customers,name',
            'maid' => 'required|exists:maids_d_b_s,name',
            'nationality' => 'required|in:Indonesia,Ethiopia,Philippines,Myanmar,Kenya,Uganda,Sri_Lanka,Tanzanian,India,Ghana,nepal,pakistan',
            "contract_ref" => 'unique:category_ones,contract_ref',
            'created_by' => 'nullable|string',
            'created_at' => 'nullable|date_format:Y-m-d',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'date.required' => 'The date field is  and shoud be year month day.',
            'customer.required' => 'The customer field is required.',
            'customer.exists' => 'The customer must exist in the customers database.',
            'maid.required' => 'The maid field is required.',
            'nationality.in' => 'required|in:Indonesia,Ethiopia,Philippines,Myanmar,Kenya,Uganda,Sri_Lanka,Tanzanian,India,Ghana,nepal,pakistan',
            'maid.exists' => 'The maid must exist in the maids database.',
            'created_at.date_format' => 'The created_at field must be in the format yyyy-mm-dd.',
        ];
    }
}










