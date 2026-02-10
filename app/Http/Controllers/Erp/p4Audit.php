<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;

class p4Audit extends Controller
{
    /// url  /p4-audit
    public function p4Audit(Request $request)
    {
        if ($request->ajax()) {

            /* ───────────────────── Pre-aggregates ───────────────────── */

            // Pending installments per contract
            // INDEX: upcoming_installments (contract, invoice_status)
            $installmentsAgg = DB::table('upcoming_installments')
                ->select('contract', DB::raw('COUNT(*) AS installment_info_count'))
                ->where('invoice_status', 0)
                ->groupBy('contract');

            // 1) latest invoice date per ledger_id
            // INDEX: general_journal_vouchers (ledger_id, voucher_type, type, date, id)
            $latestDatePerLedger = DB::table('general_journal_vouchers')
                ->select('ledger_id', DB::raw('MAX(date) AS max_date'))
                ->where('voucher_type', 'Invoice Package4')
                ->where('type', 'debit')
                ->groupBy('ledger_id');

            // 2) for that latest date, pick the max(id) per (ledger_id, date) to break ties
            $latestIdForDate = DB::table('general_journal_vouchers')
                ->select('ledger_id', 'date', DB::raw('MAX(id) AS max_id'))
                ->where('voucher_type', 'Invoice Package4')
                ->where('type', 'debit')
                ->groupBy('ledger_id', 'date');

            /* ───────────────────── Base query ───────────────────────── */

            $query = DB::table('category4_models as c4')
                ->leftJoin('maids_d_b_s as m',   'm.id',  '=', 'c4.maid_id')
                ->leftJoin('customers as cu',    'cu.id', '=', 'c4.customer_id') // has cu.ledger_id
                ->leftJoin('erp_users as u',     'u.name','=', 'c4.created_by') // INDEX: erp_users(name)
                ->leftJoinSub($installmentsAgg, 'ia', function ($j) {
                    $j->on('ia.contract', '=', 'c4.Contract_ref');
                })
                // join latest date for this customer's ledger
                ->leftJoinSub($latestDatePerLedger, 'ld', function ($j) {
                    $j->on('ld.ledger_id', '=', 'cu.ledger_id');
                })
                // join (ledger_id, date -> max_id) for that date
                ->leftJoinSub($latestIdForDate, 'li', function ($j) {
                    $j->on('li.ledger_id', '=', 'ld.ledger_id')
                      ->on('li.date',      '=', 'ld.max_date');
                })
                // fetch the final latest row to get amount (and confirm filters)
                ->leftJoin('general_journal_vouchers as gjv_latest', function ($j) {
                    $j->whereColumn('gjv_latest.ledger_id', 'li.ledger_id')
                      ->whereColumn('gjv_latest.date',      'li.date')
                      ->whereColumn('gjv_latest.id',        'li.max_id')
                      ->where('gjv_latest.voucher_type', 'Invoice Package4')
                      ->where('gjv_latest.type', 'debit');
                })
                ->select([
                    'c4.date',
                    'c4.Contract_ref',
                    DB::raw('cu.phone as phone'),
                    'cu.name as customer',
                    'm.name as maid',
                    DB::raw('m.maid_type as maid_type'),
                    DB::raw('m.payment as maid_payment'),
                    DB::raw('m.salary as maid_salary'),
                    DB::raw('`u`.`group` as user_type'),
                    'c4.created_by',
                    // precomputed
                    DB::raw('COALESCE(ia.installment_info_count, 0) as installment_info_count'),
                    DB::raw('gjv_latest.date   as customer_invoice_date'),
                    DB::raw('gjv_latest.amount as customer_invoice_amount'),
                    DB::raw('gjv_latest.notes   as customer_invoice_note'),
                ])
                ->where('c4.contract_status', 1)
                ->when($request->filled('min_date'), fn ($q) =>
                    $q->whereDate('c4.date', '>=', $request->min_date)
                )
                ->when($request->filled('max_date'), fn ($q) =>
                    $q->whereDate('c4.date', '<=', $request->max_date)
                )
                // avoid HAVING; keep it sargable
                ->when((int)$request->input('installmentZero') === 1, fn ($q) =>
                    $q->whereRaw('COALESCE(ia.installment_info_count, 0) = 0')
                )
                ->orderByDesc('c4.created_at');  // INDEX: category4_models (contract_status, created_at)

            /* ───────────────────── DataTables ───────────────────────── */

            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('customer', function ($row) {
                    return '<a href="' . url("/customer/report/p4/{$row->customer}") . '" target="_blank">' . e($row->customer) . '</a>';
                })
                ->editColumn('maid', function ($row) {
                    return '<a href="' . url("/maid-report/p4/{$row->maid}") . '" target="_blank">' . e($row->maid) . '</a>';
                })
                ->editColumn('maid_salary', function ($row) {
                    return '<a href="' . url("/maid-report/p4/{$row->maid}") . '" target="_blank">' . e(number_format((float)$row->maid_salary, 2)) . '</a>';
                })
                ->editColumn('Contract_ref', function ($row) {
                    return '<a href="' . url("/category4/contract-bycontract/{$row->Contract_ref}") . '" target="_blank">' . e($row->Contract_ref) . '</a>';
                })
                ->addColumn('installment', function ($row) {
                    $count = (int)($row->installment_info_count ?? 0);
                    return '<a href="/edit-upcoming-installment/' . e($row->Contract_ref) . '" target="_blank">' . $count . ' Installments</a>';
                })
                ->addColumn('maid_type', function ($row) {
                    return '<a href="' . url("/page/maid-finance/{$row->maid}") . '" target="_blank">' . e($row->maid_type) . '</a>';
                })
                ->addColumn('maid_payment', function ($row) {
                    return '<a href="' . url("/payroll/history/{$row->maid}") . '" target="_blank">' . e($row->maid_payment) . '</a>';
                })
                ->addColumn('phone', function ($row) {
                    return '<a href="' . url("/customer/report/p4/{$row->customer}") . '" target="_blank">' . e($row->phone) . '</a>';
                })
                ->addColumn('customer_invoice', function ($row) {
                    return '<a href="' . url("/page/invoices/{$row->customer}") . '" target="_blank">' . e($row->customer_invoice_date) . '</a>';
                })
                ->addColumn('amount_invoice', function ($row) {
                    return '<a href="' . url("/page/invoices/{$row->customer}") . '" target="_blank">' . e($row->customer_invoice_amount) . '</a>';
                })
                ->addColumn('user_type', function ($row) {
                    return '<a href="' . url("/page/invoices/{$row->customer}") . '" target="_blank">' . e($row->user_type) . '</a>';
                })

                // Filters
                ->filterColumn('phone', function ($q, $keyword) {
                    $q->where('cu.phone', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('maid_type', function ($q, $keyword) {
                    $q->where('m.maid_type', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('user_type', function ($q, $keyword) {
                    $q->whereRaw("`u`.`group` LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('customer', function ($q, $keyword) {
                    $q->where('cu.name', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('maid', function ($q, $keyword) {
                    $q->where('m.name', 'like', '%' . $keyword . '%');
                })

                ->rawColumns([
                    'customer','maid','maid_salary','Contract_ref',
                    'installment','maid_type','maid_payment','phone',
                    'customer_invoice','amount_invoice','user_type',
                ])
                ->make(true);
        }

        return view('ERP.cat4.audit');
    }
}
