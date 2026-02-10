<?php

namespace App\Http\Controllers\pro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentOrder;
use App\Services\S3FileService;
use Illuminate\Validation\Rule;


class PaymentOrderCntl extends Controller
{
    // URL: /payment-orders
public function getPaymentOrdersList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page    = $request->input('page', 1);

    $query = \DB::table('payment_orders as po')
        ->leftJoin('maids_d_b_s as m', 'po.maid_id', '=', 'm.id')
        ->select(
            'po.*',
            'm.name as maid_name',
            'm.uae_id_maid',
            'm.passport_number',
            'm.nationality'
        )
        ->orderBy('po.created_at', 'desc');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('m.name', 'like', "%{$search}%")
              ->orWhere('m.uae_id_maid', 'like', "%{$search}%")
              ->orWhere('po.note', 'like', "%{$search}%")
              ->orWhere('po.created_by' ,'like', "%{$search}%");
        });
    }

    if ($request->filled('status')) {
        $query->where('po.status', $request->input('status'));
    }

    if ($request->filled('date_from')) {
        $query->whereDate('po.date', '>=', $request->input('date_from'));
    }
    if ($request->filled('date_to')) {
        $query->whereDate('po.date', '<=', $request->input('date_to'));
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



// URL: POST /payment-orders/store-or-update
public function storeOrUpdatePaymentOrder(Request $request)
{
    $validated = $request->validate([
        'id'              => ['nullable', Rule::exists('payment_orders','id')],
        'date'            => ['required','date'],
        'maid_id'         => ['nullable', Rule::exists('maids_d_b_s','id')],
        'amount'          => ['required','numeric'],
        'transaction'     => ['required','integer'],
        'payment_method'  => ['required','integer'],
        'status'          => ['required','integer'],
        'note'            => ['nullable','string','max:1000'],
        'user'            => ['nullable','string','max:255'],   

        'attachment_file' => [
            Rule::requiredIf(fn () => !$request->filled('id')),
            'file','max:40480',
        
        ],
    ]);

    $po = $request->id ? PaymentOrder::findOrFail($request->id) : new PaymentOrder();

    if ($po->exists && $po->status == 1 && Auth::user()->group !== 'accounting') {
        return response()->json([
            'error'   => true,
            'message' => 'Only accounting users can edit approved payment orders.'
        ], 403);
    }


    $po->date           = $validated['date'];
    $po->maid_id        = $validated['maid_id'] ?? null;
    $po->amount         = $validated['amount'];
    $po->transaction    = $validated['transaction'];
    $po->payment_method = $validated['payment_method'];
    $po->status         = $validated['status'];
    $po->note           = $validated['note'] ?? null;

    if ($request->hasFile('attachment_file')) {
        $storage = new S3FileService(); // instantiate, no DI param
        $newUrl = $storage->uploadToR2($request->file('attachment_file'), 'payment_orders', resize: false);

        if ($newUrl) {
            if ($po->exists && $po->attachment) {
                $storage->deletePreviousFileFromR2($po->attachment, 'r2');
            }
            $po->attachment = $newUrl;
        }
    }

    // created_by / updated_by
    if ($po->exists) {
        $po->updated_by = Auth::user()->name ?? $request->validated('user');
    } else {
        $po->created_by = Auth::user()->name ?? $request->validated('user');
        $po->updated_by = Auth::user()->name ?? $request->validated('user');
    }

    $po->save();

    return response()->json([
        'message' => $request->id ? 'Payment order updated successfully' : 'Payment order created successfully',
        'data'    => $po
    ]);
}


    // URL /payment-orders/bulk-approve
    public function bulkApprove(Request $request)
{
    // ✅ Only accounting group can approve
    if (Auth::user()->group !== 'accounting') {
        return response()->json([
            'error'   => true,
            'message' => 'Only accounting users can bulk approve payment orders.'
        ], 403);
    }

    $validated = $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'exists:payment_orders,id'
    ]);

    $count = PaymentOrder::whereIn('id', $validated['ids'])
        ->update([
            'status'     => 1, // approved
            'updated_by' => Auth::user()->name ?? 'system'
        ]);

    return response()->json([
        'success' => true,
        'message' => "{$count} payment orders approved successfully.",
        'count'   => $count
    ]);
}




public function storePaymentOrder(Request $request)
{
    $validated = $request->validate([
        'maid_id'        => ['required', Rule::exists('maids_d_b_s','id')],
        'date'           => ['nullable','date'],
        'amount'         => ['required','numeric'],
        'transaction'    => ['required','integer'], 
        'status'         => ['required','integer'],     
        'payment_method' => ['required','integer'],      
        'note'           => ['nullable','string','max:1000'],
        'attachment_file'=> ['required','file','max:40480'], 
        'user'           => ['nullable','string','max:255'],
    ]);

    return DB::transaction(function () use ($request, $validated) {
        $po = new PaymentOrder();

        $po->maid_id        = (int) $validated['maid_id'];
        $po->date           = $validated['date'] ?? now();   
        $po->amount         = $validated['amount'];
        $po->transaction    = (int) $validated['transaction']; 
        $po->status         = (int) $validated['status'];
        $po->payment_method = (int) $validated['payment_method'];
        $po->note           = $validated['note'] ?? null;

        if ($request->hasFile('attachment_file')) {
            $storage = new S3FileService();
            $newUrl = $storage->uploadToR2($request->file('attachment_file'), 'payment_orders', resize: false);
            if ($newUrl) {
                $po->attachment = $newUrl;
            }
        }

        $po->created_by = Auth::user()->name ?? $validated['user'];
        $po->updated_by = Auth::user()->name ?? $validated['user'];
        $po->save();

        return response()->json([
            'message' => 'Payment order created successfully',
            'data'    => $po
        ]);
    });
}




}
