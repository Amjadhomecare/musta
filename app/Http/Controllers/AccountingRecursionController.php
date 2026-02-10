<?php

namespace App\Http\Controllers;

use App\Models\AccountingRecursion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AccountingRecursionController extends Controller
{
    /**
     * GET /accounting/recursions
     * Paginated list (one row per line), joined with ledger name.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $page    = (int) $request->input('page', 1);

        $query = DB::table('accounting_recursions as ar')
            ->leftJoin('all_account_ledger__d_b_s as l', 'ar.ledger_id', '=', 'l.id')
            ->select(
                'ar.*',
                'l.ledger as ledger_name',
                'l.id as ledger_ref_id'
            )
            ->orderBy('ar.created_at', 'desc');

        // 🔍 Search by name, note, or ledger name
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('ar.name', 'like', "%{$search}%")
                  ->orWhere('ar.note', 'like', "%{$search}%")
                  ->orWhere('l.ledger', 'like', "%{$search}%");
            });
        }

        // 💳 Filter by post type (debit/credit)
        if ($request->filled('post_type')) {
            $query->where('ar.post_type', $request->input('post_type'));
        }

        // 🔁 Filter by recursion type (1 = monthly, 2 = weekly)
        if ($request->filled('recursion')) {
            $query->where('ar.recursion', (int) $request->input('recursion'));
        }

        // 🚫 Filter by enabled/disabled recurrence
        if ($request->has('active')) {
            $request->boolean('active')
                ? $query->where('ar.recursion_number', '>', 0)
                : $query->where('ar.recursion_number', '=', 0);
        }

        // 📅 Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('ar.start_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('ar.start_date', '<=', $request->input('date_to'));
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'         => $paginator->items(),
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
        ]);
    }

    /**
     * POST /accounting/recursions
     * Create a batch (one record per line).
     *
     * Payload:
     * {
     *   "name": "Salary JV - May",
     *   "recursion": 1,               // 1=monthly, 2=weekly
     *   "recursion_number": 12,       // 0 = disabled
     *   "start_date": "2025-05-01",
     *   "note": "optional",
     *   "lines": [
     *     { "ledger_id": 3, "post_type": "debit",  "amount": 5000, "note": "Salary Expense" },
     *     { "ledger_id": 10,"post_type": "credit", "amount": 5000, "note": "Cash" }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // shared (top-level)
            'name'             => ['required', 'string', 'max:255'],
            'recursion'        => ['required', 'integer', Rule::in([1, 2])],
            'recursion_number' => ['required', 'integer', 'min:0'],   // 0 = disabled
            'start_date'       => ['required', 'date'],
            'note'             => ['nullable', 'string'],

            // lines
            'lines'                => ['required', 'array', 'min:2'],
            'lines.*.ledger_id'    => ['required', 'exists:all_account_ledger__d_b_s,id'],
            'lines.*.post_type'    => ['required', Rule::in(['debit', 'credit'])],
            'lines.*.amount'       => ['required', 'numeric', 'min:0.01'],
            'lines.*.note'         => ['nullable', 'string'],
        ]);

        // Validate total debit == total credit
        [$debit, $credit] = $this->sumDebitsCredits($data['lines']);
        if (abs($debit - $credit) > 0.00001) {
            throw ValidationException::withMessages([
                'lines' => ["Total debit ({$debit}) must equal total credit ({$credit})."]
            ]);
        }

        $created = [];
        DB::transaction(function () use (&$created, $data) {
            foreach ($data['lines'] as $ln) {
                $created[] = AccountingRecursion::create([
                    'name'             => $data['name'],
                    'ledger_id'        => $ln['ledger_id'],
                    'post_type'        => $ln['post_type'],
                    'recursion'        => $data['recursion'],
                    'recursion_number' => $data['recursion_number'],
                    'amount'           => $ln['amount'],
                    'note'             => $ln['note'] ?? ($data['note'] ?? null),
                    'start_date'       => $data['start_date'],
                    'last_run_at'      => null,
                ]);
            }
        });

        return response()->json([
            'message' => 'Batch created.',
            'data'    => $created,
            'totals'  => ['debit' => round($debit, 2), 'credit' => round($credit, 2)],
        ], Response::HTTP_CREATED);
    }

    /**
     * POST /accounting/recursions/update
     * Batch upsert by NAME + optional deletes.
     *
     * Payload:
     * {
     *   "name": "Salary JV - May (updated)",
     *   "recursion": 1,
     *   "recursion_number": 11,
     *   "start_date": "2025-05-01",
     *   "note": "optional",
     *   "lines": [
     *     { "id": 101, "ledger_id": 3,  "post_type": "debit",  "amount": 5200, "note": "Adj" },
     *     {           "ledger_id": 10, "post_type": "credit", "amount": 5200, "note": "Adj" }
     *   ],
     *   "delete_ids": [102, 103]   // optional
     * }
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            // shared (top-level)
            'name'             => ['required', 'string', 'max:255'],
            'recursion'        => ['required', 'integer', Rule::in([1, 2])],
            'recursion_number' => ['required', 'integer', 'min:0'],
            'start_date'       => ['required', 'date'],
            'note'             => ['nullable', 'string'],

            // lines
            'lines'                => ['required', 'array', 'min:2'],
            'lines.*.id'           => ['nullable', 'integer', 'exists:accounting_recursions,id'],
            'lines.*.ledger_id'    => ['required', 'exists:all_account_ledger__d_b_s,id'],
            'lines.*.post_type'    => ['required', Rule::in(['debit', 'credit'])],
            'lines.*.amount'       => ['required', 'numeric', 'min:0.01'],
            'lines.*.note'         => ['nullable', 'string'],

            // deletes (optional)
            'delete_ids'           => ['nullable', 'array'],
            'delete_ids.*'         => ['integer', 'exists:accounting_recursions,id'],
        ]);

        // Validate total debit == total credit
        [$debit, $credit] = $this->sumDebitsCredits($data['lines']);
        if (abs($debit - $credit) > 0.00001) {
            throw ValidationException::withMessages([
                'lines' => ["Total debit ({$debit}) must equal total credit ({$credit})."]
            ]);
        }

        $saved = [];
        DB::transaction(function () use (&$saved, $data) {
            // optional explicit deletions
            if (!empty($data['delete_ids'])) {
                AccountingRecursion::whereIn('id', $data['delete_ids'])->delete();
            }

            // upsert each line — top-level shared fields applied to every row
            foreach ($data['lines'] as $ln) {
                if (!empty($ln['id'])) {
                    $m = AccountingRecursion::lockForUpdate()->findOrFail($ln['id']);
                    $m->update([
                        'name'             => $data['name'],
                        'ledger_id'        => $ln['ledger_id'],
                        'post_type'        => $ln['post_type'],
                        'recursion'        => $data['recursion'],
                        'recursion_number' => $data['recursion_number'],
                        'amount'           => $ln['amount'],
                        'note'             => $ln['note'] ?? ($data['note'] ?? null),
                        'start_date'       => $data['start_date'],
                    ]);
                    $saved[] = $m->fresh();
                } else {
                    $saved[] = AccountingRecursion::create([
                        'name'             => $data['name'],
                        'ledger_id'        => $ln['ledger_id'],
                        'post_type'        => $ln['post_type'],
                        'recursion'        => $data['recursion'],
                        'recursion_number' => $data['recursion_number'],
                        'amount'           => $ln['amount'],
                        'note'             => $ln['note'] ?? ($data['note'] ?? null),
                        'start_date'       => $data['start_date'],
                        'last_run_at'      => null,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Batch saved.',
            'data'    => $saved,
            'totals'  => ['debit' => round($debit, 2), 'credit' => round($credit, 2)],
        ]);
    }

    /**
     * DELETE /accounting/recursions/{id?}
     * - If {id} provided → delete that record.
     * - If no {id} and request has {name} → delete all with that name.
     * - Else → 422.
     *
     * Supports JSON body with axios: axios.delete(url, { data: { name: '...' }})
     */
    public function destroy(Request $request, $id = null)
    {
        if ($id) {
            $deleted = 0;
            DB::transaction(function () use (&$deleted, $id) {
                $deleted = AccountingRecursion::whereKey($id)->delete();
            });

            if ($deleted === 0) {
                return response()->json(['message' => 'Record not found.'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['message' => 'Accounting recursion deleted successfully.', 'deleted' => 1]);
        }

        // Delete by name (batch)
        $name = $request->input('name');
        if (!is_string($name) || trim($name) === '') {
            return response()->json([
                'message' => 'Either {id} path param or {name} in body is required.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $count = 0;
        DB::transaction(function () use (&$count, $name) {
            $count = AccountingRecursion::where('name', $name)->delete();
        });

        return response()->json([
            'message' => $count ? "Deleted {$count} record(s) with name \"{$name}\"." : 'No records matched the provided name.',
            'deleted' => $count,
            'name'    => $name,
        ]);
    }

    /**
     * Utility: sum debit/credit totals from a lines array.
     */
    private function sumDebitsCredits(array $lines): array
    {
        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $ln) {
            $amt = (float) ($ln['amount'] ?? 0);
            $type = strtolower((string) ($ln['post_type'] ?? ''));
            if ($type === 'debit')  { $debit  += $amt; }
            if ($type === 'credit') { $credit += $amt; }
        }

        return [$debit, $credit];
    }
}
