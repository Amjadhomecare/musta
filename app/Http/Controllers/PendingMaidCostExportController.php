<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendingMaidCostExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $file = 'pending_cost.csv';

        return response()->streamDownload(function () {

            // A. open stream + BOM
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            // B. header
            fputcsv($out, [
                'Date', 'Name', 'Nationality', 'Agent', 'Voucher',
                'Type', 'Ledger', 'Amount', 'Created By', 'Ref Code',
            ]);

            // C. switches
            DB::connection()->disableQueryLog();
            set_time_limit(0);

            // D. flat JOIN streamed with cursor (fully unbuffered)
            DB::table('maids_d_b_s as m')            // <-- your table
                ->join('general_journal_vouchers as f',  // finance table
                       'f.maid_name', '=', 'm.name')      // or maid_id = m.id
                ->where('m.maid_status', 'pending')
                ->select([
                    'f.date      as Date',
                    'm.name      as Name',
                    'm.nationality as Nationality',
                    'm.agency    as Agent',
                    'f.voucher_type as Voucher',
                    'f.type      as Type',
                    'f.account   as Ledger',
                    'f.amount    as Amount',
                    'f.created_by as CreatedBy',
                    'f.refCode   as RefCode',
                ])
                ->orderBy('m.id')
                ->cursor()                       // ⚑ unbuffered, row-by-row
                ->each(function ($row) use ($out) {
                    fputcsv($out, [
                        $row->Date,
                        $row->Name,
                        $row->Nationality,
                        $row->Agent,
                        $row->Voucher,
                        $row->Type,
                        $row->Ledger,
                        $row->Amount,
                        $row->CreatedBy,
                        $row->RefCode,
                    ]);
                });

            fclose($out);
        }, $file, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$file}\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }
}
