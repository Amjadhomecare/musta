<?php

namespace App\Imports;

use App\Models\MaidsDB;
use App\Models\maid_doc_expiry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PassportDocExpiryImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            
            // Skip footer rows or invalid data
            if (isset($row['pass']) && is_string($row['pass']) && str_contains($row['pass'], 'Print Date:')) {
                continue;
            }

            // Get passport number from "Pass" column
            $passportNumber = trim((string)($row['pass'] ?? ''));
            
            if ($passportNumber === '') {
                continue;
            }

            // Find maid by passport number
            $maid = MaidsDB::where('passport_number', $passportNumber)->first();

            if (!$maid) {
                // Skip if maid not found
                continue;
            }

            // Parse "Expiry D" column and convert from dd/mm/yyyy to yyyy-mm-dd
            $visaExpiry = $this->parseDateFromExpiryD($row['expiry_d'] ?? null);

            if (!$visaExpiry) {
                // Skip if no valid date found
                continue;
            }

            // Calculate EID expiry as visa expiry minus 30 days
            $eidExpiry = null;
            try {
                $eidExpiry = Carbon::parse($visaExpiry)->subDays(30)->format('Y-m-d');
            } catch (\Throwable $e) {
                // If calculation fails, skip EID expiry
            }

            // Update or create record in maid_doc_expiries table
            maid_doc_expiry::updateOrCreate(
                ['maid_id' => $maid->id],
                [
                    'visa_expiry' => $visaExpiry,
                    'eid_expiry' => $eidExpiry,
                    'updated_by' => auth()->user()->name ?? null,
                ]
            );
        }
    }

    /**
     * Parse date from dd/mm/yyyy format to yyyy-mm-dd
     */
    private function parseDateFromExpiryD($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string)$value);

        // Handle Excel date serial numbers (if the cell is formatted as date)
        if (is_numeric($text)) {
            try {
                // Excel stores dates as number of days since 1900-01-01
                // But Carbon can handle Excel serial dates
                return Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($text)->format('Y-m-d'))->format('Y-m-d');
            } catch (\Throwable $e) {
                // Fall through to string parsing
            }
        }

        // Normalize weird spaces
        $text = str_replace("\xC2\xA0", ' ', $text); // non-breaking space
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Match dd/mm/yyyy or dd-mm-yyyy format
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $text, $m)) {
            $day = $m[1];
            $month = $m[2];
            $year = $m[3];

            try {
                return Carbon::createFromFormat('d/m/Y', "$day/$month/$year")->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
