<?php

namespace App\Imports;

use App\Models\MaidsDB;
use App\Models\maid_doc_expiry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MaidDocExpiryImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // Skip footer row like "Print Date: ..."
            if (isset($row['passport_detail']) && is_string($row['passport_detail']) && str_contains($row['passport_detail'], 'Print Date:')) {
                continue;
            }

            // ✅ Get passport detail from "Passport Detail" column
            $passportDetail = trim((string)($row['passport_detail'] ?? ''));

            if ($passportDetail === '') {
                continue;
            }

            // Extract passport number (before the first space/country name)
            // Example: "C6130874 INDONESIA" -> "C6130874"
            // Example: "P6661752B PHILIPPINES" -> "P6661752B"
            $passportNumber = $this->extractPassportNumber($passportDetail);

            if ($passportNumber === '') {
                continue;
            }

            // ✅ Find maid by passport number
            $maid = MaidsDB::where('passport_number', $passportNumber)->first();

            if (!$maid) {
                // Log::warning("Row ".($index+2)." - Maid not found for passport: {$passportNumber}");
                continue;
            }

            // ✅ Extract labor card expiry ONLY from card_detail (multiline text)
            $laborCardExpiry = $this->parseDateFromCardDetail($row['card_detail'] ?? null);

            // Log::info("Row ".($index+2)." parsed", [
            //     'maid_id' => $maid->id,
            //     'passport_number' => $passportNumber,
            //     'card_detail_raw' => $row['card_detail'] ?? null,
            //     'labor_card_expiry' => $laborCardExpiry,
            // ]);

            // ✅ Update or create record
            $record = maid_doc_expiry::updateOrCreate(
                ['maid_id' => $maid->id],
                [
                    'labor_card_expiry' => $laborCardExpiry, // "YYYY-MM-DD" or null
                    'updated_by'        => auth()->user()->name ?? null,
                ]
            );

            // Log::info("Row ".($index+2)." saved", [
            //     'record_id' => $record->id,
            //     'table' => $record->getTable(),
            // ]);
        }
    }

    /**
     * Extract passport number from passport detail string
     * Example: "C6130874 INDONESIA" -> "C6130874"
     * Example: "P6661752B PHILIPPINES" -> "P6661752B"
     */
    private function extractPassportNumber($passportDetail): string
    {
        $passportDetail = trim($passportDetail);
        
        // Split by space and take the first part (passport number)
        $parts = explode(' ', $passportDetail);
        
        if (empty($parts[0])) {
            return '';
        }
        
        // Return the first part (passport number) and trim any extra whitespace
        return trim($parts[0]);
    }

    /**
     * Extract dd/mm/yyyy from a multi-line cell like:
     * 113367693
     * New Labour Card 12/01/2026
     */
    private function parseDateFromCardDetail($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string)$value);

        // Normalize weird spaces + newlines
        $text = str_replace("\xC2\xA0", ' ', $text); // non-breaking space
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // ✅ Find date anywhere inside the text
        if (preg_match('/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/', $text, $m)) {
            $date = str_replace('-', '/', $m[1]);

            try {
                return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
