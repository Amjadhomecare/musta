<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JournalExportController extends Controller
{
    /**
     * Stream a CSV of General Journal Vouchers for the given date range, with account class.
     */
    public function export(Request $request): StreamedResponse
    {
        // 1. Validate the requested range.
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        [$from, $to] = [$validated['from'], $validated['to']];
        $filename    = "gjv_{$from}_{$to}.csv";

        // 2. Build a streamed response.
        return response()->streamDownload(
            function () use ($from, $to) {
                $out = fopen('php://output', 'w');
                fputs($out, "\xEF\xBB\xBF");

                // 3. Add 'Account Class' to the header
                fputcsv($out, [
                    'Date', 'Ref Code', 'Ref Number', 'Voucher Type', 'Type',
                    'Pre-Connection Name', 'Maid Name', 'Account', 'Class', 'Amount',
                    'Notes', 'Receive Ref', 'Credit Note Ref',
                    'Contract Ref', 'Created By', 'Updated By',
                    'Created At', 'Updated At',
                ]);

                DB::connection()->disableQueryLog();
                set_time_limit(0);

                // 4. Query with LEFT JOIN
                DB::table('general_journal_vouchers as gjv')
                    ->leftJoin('all_account_ledger__d_b_s as aal', 'gjv.ledger_id', '=', 'aal.id')
                    ->leftJoin('maids_d_b_s as m', 'gjv.maid_id', '=', 'm.id')
                    ->select([
                        'gjv.date','gjv.refCode','gjv.refNumber','gjv.voucher_type','gjv.type',
                        'gjv.pre_connection_name','m.name as maid_name','aal.ledger',
                        'aal.class as account_class',
                        'gjv.amount','gjv.notes','gjv.receiveRef','gjv.creditNoteRef',
                        'gjv.contract_ref','gjv.created_by','gjv.updated_by',
                        'gjv.created_at','gjv.updated_at',
                    ])
                    ->whereBetween('gjv.date', [$from, $to])
                    ->orderBy('gjv.id')
                    ->chunk(2000, function ($rows) use ($out) {
                        foreach ($rows as $row) {
                            fputcsv($out, [
                                $row->date,
                                $row->refCode,
                                $row->refNumber,
                                $row->voucher_type,
                                $row->type,
                                $row->pre_connection_name,
                                $row->maid_name,
                                $row->ledger,
                                $row->account_class,
                                $row->amount,
                                $row->notes,
                                $row->receiveRef,
                                $row->creditNoteRef,
                                $row->contract_ref,
                                $row->created_by,
                                $row->updated_by,
                                $row->created_at,
                                $row->updated_at,
                            ]);
                        }
                        ob_flush();
                        flush();
                    });

                fclose($out);
            },
            $filename,
            [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'no-store, no-cache',
            ]
        );
    }
}
