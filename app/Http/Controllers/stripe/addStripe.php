<?php

namespace App\Http\Controllers\stripe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use DataTables;
use Illuminate\Support\Facades\Log;
use Auth;
use App\Models\AsyncStripe;
use Stripe\Customer;
use Stripe\Product;
use Stripe\Subscription;
use App\Models\General_journal_voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\stripe_link;

class addStripe extends Controller
{


  // Function to get all the stripe links for customer url /stripe-links
  public function pageCustomerStripeLink(Request $request, $name)
  {
      if ($request->ajax()) {
          $data = stripe_link::where('customer_name', $name)->get();
          return DataTables::of($data)
              ->addIndexColumn()
              ->addColumn('url', function($row) {
                  $btn = '<a target="_blank" href="' . ($row?->url ?? '#') . '" class="edit btn btn-blue btn-sm">View</a>';
                  return $btn;
              })
              ->rawColumns(['url'])
              ->make(true);
      }
  
      return view('ERP.customers.stripe', compact('name'));
  }
  

    // Function to store the stripe link for customer url /store-stripe-link
    public function storeStripeLink(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'maidName' => 'required|string',
            'customerName' => 'required|string',
            'note' => 'nullable|string',
            'payment_type' => 'required|in:one_time,monthly',
        ]);
    
        try {
            $product = \Stripe\Product::create([
                'name' => $validatedData['maidName'],
                'description' => 'Created from ERP',
                'metadata' => [
                    'customer' => $validatedData['customerName'],
                    'maid_erp' => $validatedData['maidName'],
                    'erp_note' => $validatedData['note']
                ],
            ]);
    
            $priceData = [
                'unit_amount' => $validatedData['amount'] * 100,
                'currency' => 'aed',
                'product' => $product->id,
                'metadata' => [
                    'customer' => $validatedData['customerName'],
                    'maid_erp' => $validatedData['maidName'],
                    'erp_note' => $validatedData['note']
                ],
            ];
    
            if ($validatedData['payment_type'] === 'monthly') {
                $priceData['recurring'] = ['interval' => 'month'];
            }
    
            $price = \Stripe\Price::create($priceData);
    
            $paymentLink = \Stripe\PaymentLink::create([
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
            ]);
    
            $stripeLink = stripe_link::create([
                'url' => $paymentLink->url,
                'maid_name' => $validatedData['maidName'],
                'customer_name' => $validatedData['customerName'],
                'amount' => $validatedData['amount'],
                'note' => $validatedData['note'] ?? 'Stripe Link Created from ERP',
                'created_by' => Auth::user()->name,
                'updated_by' => Auth::user()->name,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Stripe Link Created Successfully',
                'data' => $stripeLink
            ], 200);
    
        } catch (\Exception $e) {
            Log::error('Error creating Stripe setup: ' . $e->getMessage());
            throw new \Exception('Failed to create Stripe setup: ' . $e->getMessage());
        }
    }
    

}
