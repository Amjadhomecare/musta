<?php

namespace App\Imports;

use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;
use App\Models\MaidsDB; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;

class GeneralJournalVoucherImport implements ToCollection
{
public function collection(Collection $rows)
{
    $rows->shift(); // drop header

    $groupedTransactions = [];
    $randomRefNumber = Str::random(2);

    // Collect maid names once to avoid N+1
    $maidNames = [];
    foreach ($rows as $row) {
        $name = isset($row[5]) ? trim((string)$row[5]) : null;
        if ($name !== null && $name !== '') {
            $maidNames[] = $name;
        }
    }
    $maidNames = array_values(array_unique($maidNames));

    // Build a map: name => id
    $maidsMap = MaidsDB::whereIn('name', $maidNames)
        ->pluck('id', 'name')
        ->toArray();

    foreach ($rows as $row) {
        $date = $row[0];

        try {
            $formattedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'date' => "Invalid date format: {$date}. Expected Y-m-d."
            ]);
        }

        $refCode = 'blk_' . trim($randomRefNumber) . str_replace('-', '', trim($formattedDate));

        $voucherType = $row[2];
        $validVoucherTypes = [
            'Payment Voucher', 'Receipt Voucher', 'Journal Voucher', 'Opening Balance',
            'Invoice Package1', 'Invoice Package4', 'Credit note', 'New arrival',
            'invoice', 'Typing Invoice', 'debit_memo'
        ];
        if (!in_array($voucherType, $validVoucherTypes, true)) {
            throw ValidationException::withMessages([
                'voucher_type' => "Invalid voucher_type: {$voucherType}"
            ]);
        }

        $account = $row[6];
        if (!All_account_ledger_DB::where('ledger', $account)->exists()) {
            throw ValidationException::withMessages([
                'account' => "Account {$account} does not exist in ledger"
            ]);
        }

        // Maid name + derived maid_id
        $maidName = isset($row[5]) ? trim((string)$row[5]) : null;
        if ($maidName === '') {
            $maidName = null;
        }
        if ($maidName !== null && !isset($maidsMap[$maidName])) {
            throw ValidationException::withMessages([
                'maid_name' => "Maid name {$maidName} does not exist in the database."
            ]);
        }
        $maidId = $maidName ? ($maidsMap[$maidName] ?? null) : null;

        $type = $row[3];
        if (!in_array($type, ['debit', 'credit'], true)) {
            throw ValidationException::withMessages([
                'type' => "Invalid type: {$type}. It must be 'debit' or 'credit'."
            ]);
        }

        $ledger = All_account_ledger_DB::where('ledger', $account)->first();
        if (!$ledger) {
            throw ValidationException::withMessages([
                'ledger_id' => "No ledger found for account: {$account}"
            ]);
        }
        $ledgerId = $ledger->id;

        $transaction = [
            'date'               => $formattedDate,
            'refCode'            => $refCode,
            'voucher_type'       => $voucherType,
            'refNumber'          => 0,
            'pre_connection_name'=> 'No connection',
            'type'               => $type,
            'maid_id'            => $maidId,        
            'amount'             => round((float)$row[7], 2),
            'invoice_balance'    => isset($row[8]) ? (float)$row[8] : 0,
            'notes'              => $row[9]  ?? null,
            'receiveRef'         => $row[10] ?? null,
            'creditNoteRef'      => $row[11] ?? null,
            'contract_ref'       => $row[12] ?? null,
            'extra'              => $row[13] ?? null,
            'created_by'         => Auth::user()->name ?? null,
            'updated_by'         => $row[15] ?? null,
            'created_at'         => now(),
            'updated_at'         => now(),
            'ledger_id'          => $ledgerId,
        ];

        $groupedTransactions[$refCode][] = $transaction;
    }

    DB::beginTransaction();
    try {
        foreach ($groupedTransactions as $refCode => $transactions) {
            $totalDebit  = collect($transactions)->where('type', 'debit')->sum(fn($t) => round($t['amount'], 2));
            $totalCredit = collect($transactions)->where('type', 'credit')->sum(fn($t) => round($t['amount'], 2));

            if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
                throw ValidationException::withMessages([
                    'balance' => "Transactions for refCode {$refCode} are not balanced."
                ]);
            }

            // Batch insert keeps performance and includes maid_id
            General_journal_voucher::insert($transactions);
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
}