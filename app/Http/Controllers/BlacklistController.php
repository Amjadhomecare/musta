<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlacklistController extends Controller
{
    /**
     * Display the blacklist approval form
     */
    public function showApprovalForm($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            return view('blacklist.blacklist_approve', compact('customer'));
        } catch (\Exception $e) {
            Log::error('Error displaying blacklist approval form: ' . $e->getMessage());
            return abort(404, 'Customer not found');
        }
    }

    /**
     * Process the blacklist approval
     */
    public function processApproval(Request $request, $customerId)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($customerId);
            
            // Update the black_list field to true
            $customer->black_list = true;
            $customer->save();

            DB::commit();

            return redirect()->route('blacklist.success')
                ->with('success', 'Customer has been successfully blacklisted.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing blacklist approval: ' . $e->getMessage());
            
            return back()->with('error', 'An error occurred while processing the blacklist approval.');
        }
    }

    /**
     * Display success message after blacklist approval
     */
    public function success()
    {
        return view('blacklist.success');
    }
}
