<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\General_journal_voucher;
use Illuminate\Support\Facades\Log;
use App\Models\MaidsDB;
use App\Models\categoryOne;
use App\Models\customer_complaints;
use Illuminate\Validation\ValidationException;

class CustomerUser extends Controller
{
    /**
     * Check if the customer exists and generate/reuse the JWT token.
     */
    public function checkCustomer(Request $request)
    {

        $request->validate([
            'phone' => 'required|string',
          
        ]);

  
        $customer = Customer::where('phone', $request->phone)->first();

    
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
    
        if (!$customer->token) {
         
            $token = JWTAuth::fromUser($customer);       
            $customer->update(['token' => $token]);

        } else {
    
            $token = $customer->token;
        }


        return response()->json([
            'name' => $customer->name,
            'phone' => $customer->phone,
            'token' => $token
        ]);
    }

 
// /profile/invoices
public function getInvoices(Request $request)
{
    $request->validate([
        'name' => 'required|string|exists:customers,name',
    ]);

    $invoices = General_journal_voucher::where('account', $request->input('name'))
                   ->orderBy('id', 'desc')
                   ->take(100)
                   ->get();



    return response()->json([
        'data' => $invoices,
    ], 200);
}


// /profile/c-p1/

public function getP1Contracts(Request $request)
{
  
    $request->validate([
        'name' => 'required|string|exists:category_ones,customer',
    ]);


    $p1 = categoryOne::where('customer', $request->input('name'))
                   ->with(['returnInfo', 'maidInfo']) 
                   ->orderBy('id', 'desc')
                   ->take(100)
                   ->get();
    

    return response()->json([
        'data' => $p1,
    ], 200);
}


// /customer/complaint
public function postComplain(Request $request)
{
    try {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'maid_id' => 'required|exists:maids_d_b_s,id',
            'reason' => 'required|string|min:1', 
            'note' => 'required|string',
        ]);

        $complaint = customer_complaints::create($validated);

        return response()->json([
            'message' => 'Customer complaint created successfully!',
            'complaint' => $complaint,
        ], 201);

    } catch (ValidationException $e) {
      
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);


    } catch (\Exception $e) {
      
        return response()->json([
            'message' => 'Failed to create complaint.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    
}
