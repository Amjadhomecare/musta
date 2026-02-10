<?php

namespace App\Http\Controllers\stripe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Price;
use App\Models\AsyncSubStripe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;
use DataTables;
use Illuminate\Support\Facades\Log;

class stripeSub extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        
    }

    // /page-live-sub
    public function pageLiveSubscription(){

        return view('stripe.live_sub' );
    }


    // /stripe-subscription
    public function listLiveSubscription(Request $request)
    {
        // Stripe API key is set in the constructor
    
        $limit = $request->get('limit', 10);
        $startingAfter = $request->get('starting_after', null); 
    
        $params = ['limit' => $limit];
        if ($startingAfter) {
            $params['starting_after'] = $startingAfter;
        }
    
        try {
            $subscriptions = Subscription::all($params);
            $hasMore = $subscriptions->has_more; 
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    
        return response()->json([
            'data' => $subscriptions->data,
            'hasMore' => $hasMore,  
            'limit' => $limit
        ]);
    }
    

  // URL:: /async-sub  

  public function syncStripeSubscriptions()
  {
      try {
        
          $subscriptions = Subscription::all(['limit' => 100, 'status' => 'all'])->autoPagingIterator();

          foreach ($subscriptions as $subscription) {
   
              $subscriptionItem = $subscription->items->data[0] ?? null; 
              $plan = $subscriptionItem->plan ?? null;

              AsyncSubStripe::updateOrCreate(
                  ['sub_id' => $subscription->id], 
                  [
                      'cus_id' => $subscription->customer, 
                      'created_date' => date('Y-m-d H:i:s', $subscription->created),
                      'status' => $subscription->status, 
                      'customer_erp' => $plan->metadata['customer'] ?? null, 
                      'monthly_amount' => $plan->amount /100 ?? null, 
                      'maid_erp' => $subscription->metadata['maid_erp'] ?? null,
                      'cancelled_at' => $subscription->canceled_at ? date('Y-m-d H:i:s', $subscription->canceled_at) : null,
                      'branch' => $subscription->metadata['branch'] ?? null,
                      'note' => $subscription->metadata['note'] ?? null
                    , 
                  ]
              );
          }
  
          return response()->json(['message' => 'Subscriptions synced successfully!']);
      } catch (\Exception $e) {
          return response()->json(['error' => $e->getMessage()], 500);
      }
  }
  
    

// /table/stripe-sub
  public function tableSubStripe(Request $request){

    if ($request->ajax()) {
        $subscriptions= AsyncSubStripe::orderBy('created_date', 'desc');
        return DataTables::of($subscriptions)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {

                return '<button class="btn btn-blue sub-stripe-btn" data-id="' . $row->sub_id . '">Edit</button>';
           
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    return view('stripe.sub_stripe');
  }


  /**
   * Retrieve a subscription by its ID.
   *
   * @param string $id The ID of the subscription to retrieve.
   * @return \Illuminate\Http\JsonResponse The subscription details in JSON format.
   */
  public function fetchSub($id)
  {
      try {

  
          $sub = Subscription::retrieve($id);
  
          return response()->json($sub, 200);
      } catch (\Exception $e) {
   
          \Log::error('Error fetching subscription: ' . $e->getMessage());
  
          return response()->json([
              'error' => 'Unable to retrieve subscription. Please try again later.',
              'message' => $e->getMessage()
          ], 500);
      }
  }
  


  public function updateStripe(Request $request)
  {
      $validateData = $request->validate([
          "stripe_sub_id" => 'required',
          'customer'      => 'nullable|string',
      ]);
  
      try {
          // Retrieve the subscription
          $subscription = Subscription::retrieve($validateData['stripe_sub_id']);
  
          Log::info('Subscription retrieved: ', ['subscription' => $subscription]);
  
          // Update subscription metadata
          $updatedSubscription = Subscription::update(
              $validateData['stripe_sub_id'],
              [
                  'metadata' => [
                     null
                  ],
              ]
          );
  
          // Update plan metadata
          $planId = $subscription->items->data[0]->plan->id;
          $updatedPlan = Plan::update(
              $planId,
              [
                  'metadata' => [
                    'customer' => ''
                  ],
              ]
          );
  
          // Update price metadata
          $priceId = $subscription->items->data[0]->price->id;

 
          $updatedPrice = Price::update(
              $priceId,
              [
                  'metadata' => [
                    'customer' => ''
                  ],
              ]
          );
  
          // Update the actual Stripe customer metadata
          $customerId = $subscription->customer;
          $updatedCustomer = Customer::update(
              $customerId,
              [
                  'metadata' => [
                      'customer' => $validateData['customer'],
                  ],
              ]
          );
  
          return response()->json([
              'success'      => true,
              'subscription' => $updatedSubscription,
              'plan'         => $updatedPlan,
              'price'        => $updatedPrice,
              'customer'     => $updatedCustomer,
          ], 200);
  
      } catch (\Stripe\Exception\ApiErrorException $e) {
          return response()->json([
              'success' => false,
              'error'   => $e->getMessage(),
          ], 400);
      }
  }
  
  
  

}
