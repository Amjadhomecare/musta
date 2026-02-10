<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category4Model;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use Illuminate\Support\Facades\DB;
use DataTables;

class AuditDhCntl extends Controller
{
    /** Blade entry-point */
    public function index()
    {
        return view('ERP.auditdh.audit_dh');
    }

    /** Ajax source for the datatable */
public function getListAuditDh(Request $request)
{
    abort_unless($request->ajax(), 404);

    $ledgerId = All_account_ledger_DB::where('ledger', 'P4_MAIDS_PAYROLL')->value('id');

    // Latest Category4Model row per maid (by MAX(id))
    $lastContractSub = DB::table('category4_models as c4')
        ->selectRaw('MAX(c4.id) AS last_id, c4.maid_id')
        ->groupBy('c4.maid_id');

    // Customer balance by ledger_id = SUM(debit) - SUM(credit)
    $balanceSub = DB::table('general_journal_vouchers as gjb')
        ->selectRaw("
            gjb.ledger_id,
            SUM(CASE WHEN gjb.type = 'debit' THEN gjb.amount ELSE -gjb.amount END) AS balance
        ")
        ->groupBy('gjb.ledger_id');

    // Build Eloquent query (required for DataTables::eloquent)
    $q = General_journal_voucher::from('general_journal_vouchers as gjv')
        ->selectRaw("
            gjv.id,

            -- base fields (explicit aliases)
            gjv.date            AS date,
            gjv.amount          AS amount,
            gjv.notes           AS notes,
            gjv.creditNoteRef   AS creditNoteRef,
            gjv.refCode         AS refCode,
            gjv.type            AS type,
            gjv.voucher_type    AS voucher_type,

            -- maid info
            m.name              AS maid_name,
            m.salary            AS salary,
            m.payment           AS bank_cash_value,

            -- customer info
            cus.name            AS customer_name_value,
            cus.ledger_id       AS customer_ledger_id,

            -- pre-aggregated balance
            bal.balance         AS balance_value,

            -- paid / unpaid for the same month
            CASE WHEN p.id IS NULL THEN 'unpaid' ELSE 'paid' END AS paid_status
        ")
        // Base filters
        ->where('gjv.ledger_id', $ledgerId)
        ->when($request->filled('min_date'), fn ($qq) =>
            $qq->whereDate('gjv.date', '>=', $request->min_date))
        ->when($request->filled('max_date'), fn ($qq) =>
            $qq->whereDate('gjv.date', '<=', $request->max_date))

        // Joins
        ->join('maids_d_b_s as m', 'm.id', '=', 'gjv.maid_id')
        ->where('m.maid_type', 'Direct hire')
        ->where('m.maid_status', 'hired')

        ->leftJoin('pay_maid_payrolls as p', function ($join) {
            $join->on('p.maid_id', '=', 'gjv.maid_id')
                 ->whereRaw("DATE_FORMAT(p.accrued_month, '%Y-%m') = DATE_FORMAT(gjv.date, '%Y-%m')");
        })

        ->joinSub($lastContractSub, 'lc', function ($join) {
            $join->on('lc.maid_id', '=', 'gjv.maid_id');
        })
        ->leftJoin('category4_models as c4', 'c4.id', '=', 'lc.last_id')
        ->leftJoin('customers as cus', 'cus.id', '=', 'c4.customer_id')

        ->leftJoinSub($balanceSub, 'bal', function ($join) {
            $join->on('bal.ledger_id', '=', 'cus.ledger_id');
        });

    return DataTables::eloquent($q)
        ->addIndexColumn()

        // 🔎 GLOBAL SEARCH OVERRIDE (prevents general_journal_vouchers.<col>)
        ->filter(function ($query) use ($request) {
            $kw = $request->input('search.value');
            if (!$kw) return;

            $like = "%{$kw}%";
            $query->where(function ($qq) use ($like) {
                $qq->orWhere('gjv.date', 'like', $like)
                   ->orWhere('gjv.type', 'like', $like)              // mapped to alias
                   ->orWhere('gjv.voucher_type', 'like', $like)      // mapped to alias
                   ->orWhere('m.name', 'like', $like)
                   ->orWhere('m.salary', 'like', $like)
                   ->orWhere('gjv.amount', 'like', $like)
                   ->orWhere('gjv.notes', 'like', $like)
                   ->orWhere('cus.name', 'like', $like)
                   ->orWhere('gjv.creditNoteRef', 'like', $like)
                   ->orWhere('gjv.refCode', 'like', $like)
                   ->orWhere('m.payment', 'like', $like)
                   ->orWhereRaw("CASE WHEN p.id IS NULL THEN 'unpaid' ELSE 'paid' END LIKE ?", [$like]);
            });
        }, true)

        // 🧭 PER-COLUMN SEARCH MAPPINGS
        ->filterColumn('date', function ($query, $keyword) {
            $k = trim($keyword);
            if (preg_match('/^\\d{4}-\\d{2}$/', $k)) {
                $query->whereRaw("DATE_FORMAT(gjv.date,'%Y-%m') = ?", [$k]);
            } else {
                $query->where('gjv.date', 'like', "%{$k}%");
            }
        })
        ->filterColumn('type', function ($query, $keyword) {          // 👈 add this
            $query->where('gjv.type', 'like', "%{$keyword}%");
        })
        ->filterColumn('voucher_type', function ($query, $keyword) {  // 👈 and this
            $query->where('gjv.voucher_type', 'like', "%{$keyword}%");
        })
        ->filterColumn('maid_name', function ($query, $keyword) {
            $query->where('m.name', 'like', "%{$keyword}%");
        })
        ->filterColumn('salary', function ($query, $keyword) {
            $query->where('m.salary', 'like', "%{$keyword}%");
        })
        ->filterColumn('amount', function ($query, $keyword) {
            $query->where('gjv.amount', 'like', "%{$keyword}%");
        })
        ->filterColumn('notes', function ($query, $keyword) {
            $query->where('gjv.notes', 'like', "%{$keyword}%");
        })
        ->filterColumn('customer_name', function ($query, $keyword) {
            $query->where('cus.name', 'like', "%{$keyword}%");
        })
        ->filterColumn('balance', function ($query, $keyword) {
            $query->where('bal.balance', 'like', "%{$keyword}%");
        })
        ->filterColumn('creditNoteRef', function ($query, $keyword) {
            $query->where('gjv.creditNoteRef', 'like', "%{$keyword}%");
        })
        ->filterColumn('refCode', function ($query, $keyword) {
            $query->where('gjv.refCode', 'like', "%{$keyword}%");
        })
        ->filterColumn('bank_cash', function ($query, $keyword) {
            $query->where('m.payment', 'like', "%{$keyword}%");
        })
        ->filterColumn('paid_status', function ($query, $keyword) {
            $kw = strtolower(trim($keyword));
            if ($kw === 'paid')       $query->whereNotNull('p.id');
            elseif ($kw === 'unpaid') $query->whereNull('p.id');
            else $query->whereRaw("CASE WHEN p.id IS NULL THEN 'unpaid' ELSE 'paid' END LIKE ?", ["%{$keyword}%"]);
        })

        // ↕ ORDERING MAPPINGS
        ->orderColumn('date',          'gjv.date $1')
        ->orderColumn('type',          'gjv.type $1')               // 👈 add
        ->orderColumn('voucher_type',  'gjv.voucher_type $1')       // 👈 add
        ->orderColumn('maid_name',     'm.name $1')
        ->orderColumn('salary',        'm.salary $1')
        ->orderColumn('amount',        'gjv.amount $1')
        ->orderColumn('customer_name', 'cus.name $1')
        ->orderColumn('balance',       'bal.balance $1')
        ->orderColumn('refCode',       'gjv.refCode $1')
        ->orderColumn('creditNoteRef', 'gjv.creditNoteRef $1')
        ->orderColumn('bank_cash',     'm.payment $1')
        ->orderColumn('paid_status',   "CASE WHEN p.id IS NULL THEN 'unpaid' ELSE 'paid' END $1")

        // Rendered columns expected by your JS
        ->editColumn('customer_name', function ($row) {
            if (!$row->customer_name_value) return '-';
            $c = e($row->customer_name_value);
            return '<a href="/customer/soa/'.$c.'" target="_blank">'.$c.'</a>';
        })
        ->editColumn('balance', function ($row) {
            if (!$row->customer_ledger_id || is_null($row->balance_value)) return '-';
            return '<a href="/page/invoices/'.e($row->customer_ledger_id).'" target="_blank">'.number_format($row->balance_value, 2).'</a>';
        })
        ->editColumn('bank_cash', function ($row) {
            $payment = $row->bank_cash_value ?? '';
            if (!$row->customer_name_value) return e($payment);
            $c = e($row->customer_name_value);
            return '<a href="/customer/report/p4/'.$c.'" target="_blank">'.e($payment).'</a>';
        })

        // sanitize pass-throughs
        ->editColumn('notes', fn ($row) => e($row->notes))
        ->editColumn('creditNoteRef', fn ($row) => e($row->creditNoteRef ?? ''))
        ->editColumn('refCode',       fn ($row) => e($row->refCode ?? ''))

        ->rawColumns(['customer_name','balance','bank_cash'])
        ->make(true);
}

}