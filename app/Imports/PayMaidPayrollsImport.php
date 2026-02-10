<?php

namespace App\Imports;

use App\Models\PayMaidPayroll;
use App\Models\MaidsDB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\{
    ToModel, WithHeadingRow, WithValidation
};
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PayMaidPayrollsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * Transform a single row into a PayMaidPayroll model.
     *
     * @param  array  $row   // headings become array keys
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        /* ───── 1) Find the maid by CSV 'maid' (name), ensure status/type ───── */
        $maid = MaidsDB::query()
            ->where('name', $row['maid'])
            ->whereIn('maid_status', ['approved', 'hired'])
            ->whereIn('maid_type', ['Direct hire', 'HC'])
            ->first();

        if (!$maid) {
            return null; // skip silently if maid not eligible / not found
        }

        /* ───── 2) Normalise accrued_month to YYYY-MM-25 ─────────── */
        $date = is_numeric($row['accrued_month'])
            ? Carbon::instance(ExcelDate::excelToDateTimeObject($row['accrued_month']))
            : Carbon::parse($row['accrued_month']);

        $accrued = $date->setDay(25)->format('Y-m-d');

        /* ───── 3) Skip if we already have this maid_id + month ──── */
        if (PayMaidPayroll::where([
            'maid_id'       => $maid->id,
            'accrued_month' => $accrued,
        ])->exists()) {
            return null;
        }

        /* ───── 4) Build the model (store maid_id instead of name) ── */
        return new PayMaidPayroll([
            'accrued_month'  => $accrued,
            'maid_id'        => $maid->id,            // ← store ID
            // 'maid'        => $maid->name,          // ← leave unset (migrating to maid_id)
            'status'         => $maid->maid_status,
            'basic'          => $maid->salary,
            'maid_type'      => $maid->maid_type,
            'method'         => $maid->payment,
            'working_dayes'  => $row['working_dayes']  ?? 0,
            'deduction'      => $row['deduction']      ?? 0,
            'allowance'      => $row['allowance']      ?? 0,
            'note'           => $row['note']           ?? 'Excel import',
            'net_salary'     => $row['net_salary']     ?? 0,
            'created_by'     => Auth::user()->name ?? 'system',
            'created_at'     => now(),
        ]);
    }

    /** Headings row index (1 = first row) */
    public function headingRow(): int
    {
        return 1;
    }

    /** Validation that runs *before* model() */
    public function rules(): array
    {
        return [
            'maid'          => ['required', 'string'],
            'accrued_month' => ['required', 'date'],
            // keep CSV format as-is; we resolve maid_id internally
        ];
    }
}
