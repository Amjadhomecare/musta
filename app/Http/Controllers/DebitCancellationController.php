<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CancelationDd;

class DebitCancellationController extends Controller
{
    /**
     * Get list of direct debit cancellations.
     * Uses Eloquent ORM as requested.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);

        // Fetch Cancellations with their related DirectDebit
        $query = CancelationDd::with(['directDebit.customer', 'createdByUser'])
            ->orderBy('created_at', 'desc');
        
        // Handle search if needed (basic example)
        if ($request->filled('search')) {
             $search = $request->input('search');
             $query->whereHas('directDebit', function($q) use ($search) {
                 $q->where('ref', 'like', "%{$search}%")
                   ->orWhere('account_title', 'like', "%{$search}%");
             });
        }

        $data = $query->paginate($limit);

        return response()->json($data);
    }
}
