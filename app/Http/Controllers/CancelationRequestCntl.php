<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\DirectDebit;
use App\Models\CancelationDd;
use App\Models\RefundDd;


class CancelationRequestCntl extends Controller
{

    public function CancelationRequestList( Request $request , $name){
          
        $customerId = Customer::where('name', $name)->value('id');
        $directDebit = DirectDebit::with(['cancelationDd' , 'refundDd'])
        ->where('customer_id', $customerId)->get();


        return view('ERP.customers.cancelation_request_list', compact('directDebit' , 'name'));

        
    }

    public function storeCancellationRequest(Request $request)
    {
        // Determine request type
        $requestType = $request->input('request_type'); // 'cancellation' or 'refund'

        // Validate based on request type
        if ($requestType === 'refund') {
            $validated = $request->validate([
                'dd_id' => 'required|exists:direct_debits,id',
                'request_type' => 'required|in:cancellation,refund',
                'note' => 'required|string|max:1000',
                'amount' => 'required|numeric|min:0',
            ]);
        } else {
            $validated = $request->validate([
                'dd_id' => 'required|exists:direct_debits,id',
                'request_type' => 'required|in:cancellation,refund',
                'note' => 'required|string|max:1000',
            ]);
        }

        $directDebit = DirectDebit::where('id', $validated['dd_id'])->first();

        // Check if direct debit is valid for requests
        if ($directDebit->active == DirectDebit::ACTIVE_CANCELLED || $directDebit->status != DirectDebit::STATUS_ACCEPTED) {
            return redirect()->back()->with('error', 'Direct debit must be active before a request can be submitted.');
        }

        // Handle based on request type
        if ($requestType === 'refund') {
            // Check if a refund request already exists
            $existingRefund = RefundDd::where('dd_id', $validated['dd_id'])->first();
            
            if ($existingRefund) {
                return redirect()->back()->with('error', 'A refund request already exists for this subscription.');
            }

            // Create refund request
            RefundDd::create([
                'dd_id' => $validated['dd_id'],
                'amount' => $validated['amount'],
                'note' => $validated['note'],
                'created_by' => auth()->user()->name ?? 'system',
                'updated_by' => auth()->user()->name ?? 'system',
            ]);

            // Check if cancellation exists
            $existingCancellation = CancelationDd::where('dd_id', $validated['dd_id'])->first();

            if ($existingCancellation) {
                // Update existing cancellation to include refund (change task to type 2)
                $existingCancellation->update([
                    'task' => CancelationDd::TASK_CANCELATION_AND_REFUND,
                    'update_by' => auth()->user()->id ?? 'system',
                ]);
            } else {
                // Create new cancellation with task type 2 (Cancellation and Refund)
                CancelationDd::create([
                    'dd_id' => $validated['dd_id'],
                    'task' => CancelationDd::TASK_CANCELATION_AND_REFUND,
                    'note' => $validated['note'],
                    'status' => CancelationDd::STATUS_REQUESTED,
                    'created_by' => auth()->user()->id ?? 'system',
                    'update_by' => auth()->user()->id ?? 'system',
                ]);
            }

            return redirect()->back()->with('success', 'Refund request submitted successfully.');
        } else {
            // Check if a cancellation request already exists
            $existingCancellation = CancelationDd::where('dd_id', $validated['dd_id'])->first();
            
            if ($existingCancellation) {
                return redirect()->back()->with('error', 'A cancellation request already exists for this subscription.');
            }

            // Create cancellation only with task type 3 (Cancellation only)
            CancelationDd::create([
                'dd_id' => $validated['dd_id'],
                'task' => CancelationDd::TASK_CANCELATION_ONLY,
                'note' => $validated['note'],
                'status' => CancelationDd::STATUS_REQUESTED,
                'created_by' => auth()->user()->id ?? 'system',
                'update_by' => auth()->user()->id ?? 'system',
            ]);

            return redirect()->back()->with('success', 'Cancellation request submitted successfully.');
        }
    }

    public function getRefundListApi(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $query = RefundDd::with(['directDebit.customer']);

        // Search across customer name, amount, note
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('directDebit.customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $refunds->items(),
            'total' => $refunds->total(),
            'current_page' => $refunds->currentPage(),
            'per_page' => $refunds->perPage(),
            'last_page' => $refunds->lastPage(),
        ]);
    }

    public function bulkApproveRefunds(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:refund_dds,id',
        ]);

        $updated = RefundDd::whereIn('id', $validated['ids'])
            ->where('status', RefundDd::STATUS_REQUESTED)
            ->update([
                'status' => RefundDd::STATUS_APPROVED,
                'updated_by' => auth()->user()->name ?? 'system',
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} refund(s) approved successfully.",
            'updated' => $updated,
        ]);
    }
    
}

