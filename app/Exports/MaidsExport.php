<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
// use Maatwebsite\Excel\Concerns\ShouldQueue; // <— uncomment if you want queued export

class MaidsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading /*, ShouldQueue*/
{
    /**
     * Build a single flat query. No N+1, no PHP-side filtering.
     */
    public function query()
    {
        return DB::table('maids_d_b_s as m')
            ->join('general_journal_vouchers as gjv', 'gjv.maid_id', '=', 'm.id')
            ->where('m.maid_status', 'pending')
      
            ->select([
                'gjv.date',
                'm.name',
                'm.nationality',
                'm.agency',
                'gjv.voucher_type',
                'gjv.type',
                'gjv.account',
                'gjv.amount',
                'gjv.created_by',
                'gjv.refCode',
            ])
            ->orderBy('gjv.date'); // deterministic order helps
    }

    /**
     * Map each DB row to an export row.
     */
    public function map($row): array
    {
        return [
            $row->date,
            $row->name,
            $row->nationality,
            $row->agency,
            $row->voucher_type,
            $row->type,          // you called this 'post_type' in headings; keeping 'type' for correctness
            $row->account,
            $row->amount,
            $row->created_by,
            $row->refCode,
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Name',
            'nationality',
            'Agent',
            'Voucher',
            'post_type',  // will contain gjv.type
            'Ledger',
            'Amount',
            'Created By',
            'Ref Code',
        ];
    }

    /**
     * Stream in chunks to keep memory low.
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
