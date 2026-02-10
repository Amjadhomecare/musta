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


class transactionController extends Controller
{


    public function syncCharges()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    
        try {
            $processed = 0;
            $limit = 100; // Stripe max is 100 per page
    
            $params = ['limit' => $limit];
            $charges = Charge::all($params)->autoPagingIterator();
    
            foreach ($charges as $charge) {
                if ($processed >= 700) {
                    break;
                }
    
                AsyncStripe::updateOrCreate(
                    ['stripe_id' => $charge['id']],
                    [
                        'amount' => $charge['amount'] / 100,
                        'currency' => $charge['currency'],
                        'refunded_amount' => $charge['amount_refunded'] / 100,
                        'cus_str_id' => $charge['customer'],
                        'description' => $charge['description'],
                        'status' => $charge['status'],
                        'billing_email' => $charge['billing_details']['email'] ?? null,
                        'billing_name' => $charge['billing_details']['name'] ?? null,
                        'receipt_url' => $charge['receipt_url'],
                        'refunded' => $charge['refunded'],
                        'created_by' => Auth::user()->name,
                        'updated_by' => Auth::user()->name,
                        'stripe_created_at' => date('Y-m-d H:i:s', $charge['created']),
                    ]
                );
    
                $processed++;
            }
    
            return response()->json(['message' => 'Sync completed successfully.']);
        } catch (\Exception $e) {
            Log::error('Error syncing charges: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function latest500syncCharges()
{
    Stripe::setApiKey(config('services.stripe.secret'));

    try {
        $limitPerRequest = 100; 
        $totalChargesToSync = 500; 
        $chargesProcessed = 0;
        $lastChargeId = null;

        do {
            $params = ['limit' => $limitPerRequest];
            if ($lastChargeId) {
                $params['starting_after'] = $lastChargeId;
            }
            $charges = Charge::all($params);

            foreach ($charges->data as $charge) {
                AsyncStripe::updateOrCreate(
                    ['stripe_id' => $charge['id']],
                    [
                        'amount' => $charge['amount'] / 100,
                        'currency' => $charge['currency'],
                        'description' => $charge['description'],
                        'status' => $charge['status'],
                        'billing_email' => $charge['billing_details']['email'] ?? null,
                        'billing_name' => $charge['billing_details']['name'] ?? null,
                        'receipt_url' => $charge['receipt_url'],
                        'refunded' => $charge['refunded'],
                        'created_by' => Auth::user()->name,
                        'updated_by' => Auth::user()->name,
                        'stripe_created_at' => date('Y-m-d H:i:s', $charge['created']),
                    ]
                );

                $chargesProcessed++;
                $lastChargeId = $charge['id'];

             
                if ($chargesProcessed >= $totalChargesToSync) {
                    break 2; 
                }
            }
        } while (!empty($charges->has_more));

        return response()->json(['message' => 'Sync completed successfully.']);
    } catch (\Exception $e) {
        Log::error('Error syncing charges: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    

//async-stripepay
public function tableAsyncTransactionsT(Request $request)
{
    if ($request->ajax()) {
        $charges = AsyncStripe::with('subInfo')
           ->orderBy('stripe_created_at', 'desc');
       
        if ($request->has('min_date') && $request->min_date != '') {
            $charges->whereDate('stripe_created_at', '>=', $request->min_date);
        }

        if ($request->has('max_date') && $request->max_date != '') {
            $charges->whereDate('stripe_created_at', '<=', $request->max_date);
        }


        if ($request->has('partial_refund') && $request->partial_refund == 'true') {
            $charges->where(function ($query) {
                $query->where('refunded_amount', '>', 0)
                      ->whereColumn('refunded_amount', '<', 'amount');
            });
        }
        

        return DataTables::of($charges)
            ->addIndexColumn()
            ->editColumn('receipt_url', function ($row) {
                return $row->receipt_url 
                    ? '<a href="' . $row->receipt_url . '" target="_blank">View</a>' 
                    : '';
            })
            ->addColumn('action', function ($row) {
                if (auth()->user()->group === 'accounting') {
                    return '<button class="btn btn-blue pay-stripe-btn" data-id="' . $row->stripe_id . '">Pay</button>';
                }
            })
            ->addColumn('maid_erp', function ($row) {
                return $row->maid_erp 
                    ? '<a href="/maid-report/p4/' . $row->maid_erp . '" target="_blank"> ' . $row->maid_erp . '</a>' 
                    : '';
            })
            ->addColumn('customer_erp', function ($row) {
                return $row->customer_from
                    ? '<a href="/customer/report/p4/' . $row->customer_from . '" target="_blank"> ' . $row->customer_from . ' </a>' 
                    : '';
            })

            ->addColumn('sub_start', function ($row) {
                return    $row->subInfo?->created_date;
            }) 

            ->editColumn('sub_status', function ($row) {
                return    $row->subInfo?->status;
            }) 

            ->editColumn('sub_id', function ($row) {
                if ($row->subInfo && $row->subInfo->sub_id) {
                    $url = 'https://dashboard.stripe.com/subscriptions/' . $row->subInfo->sub_id;
                    return '<a href="' . $url . '" target="_blank">' . $row->subInfo->sub_id . '</a>';
                }
                return '';
            })
            
            ->filterColumn('sub_id', function ($query, $keyword) {
                $query->whereHas('subInfo', function ($q) use ($keyword) {
                    $q->where('sub_id', 'like', "%{$keyword}%");
                });
            })

                
            ->filterColumn('sub_status', function ($query, $keyword) {
                $query->whereHas('subInfo', function ($q) use ($keyword) {
                    $q->where('status', 'like', "%{$keyword}%");
                });
            })



            ->rawColumns(['receipt_url', 'action', 'maid_erp', 'customer_erp','sub_id','sub_status'])
            ->make(true);
    }

    return view('stripe.async_payment');
}



// URL : http:// fetch-charges/{id}

public function getTransactionById($stripeID)
{
    $charge = AsyncStripe::where('stripe_id', $stripeID)->first();

    if (!$charge) {
        return response()->json(['message' => 'Charge not found'], 404);
    }

    return response()->json($charge);
}



///stripe/erp-pay
public function payStripeErp(Request $request)
{
    $validatedData = $request->validate([
        'stripe_id' => 'required',
        'date' => 'required|date',
        'customer' => 'required|string|max:255|exists:all_account_ledger__d_b_s,ledger',
        'maid' => 'nullable|string|max:255',
        'note' => 'nullable|string|max:255',
        'amount' => 'required|numeric|min:1'
    ]);

    $randomRefNumber = "str_" . Str::random(5);
    $now = Carbon::now();

    $transaction = AsyncStripe::where('stripe_id', $validatedData['stripe_id'])->first();

    $stripeLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', 'STRIPE')->first();
    $customerLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $validatedData['customer'])->first();

    if (!$stripeLedger || !$customerLedger) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ledger not found for STRIPE or customer.',
        ], 422);
    }

    $voucherData = [
        [
            'date' => $validatedData['date'],
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'debit',
            'ledger_id' => $stripeLedger->id,
            'amount' => $validatedData['amount'],
            'notes' => $validatedData['customer'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'date' => $validatedData['date'],
            'refCode' => $randomRefNumber,
            'refNumber' => 0,
            'voucher_type' => 'Receipt Voucher',
            'type' => 'credit',
            'ledger_id' => $customerLedger->id,
            'amount' => $validatedData['amount'],
            'notes' => $validatedData['stripe_id'],
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ]
    ];

    DB::transaction(function () use ($voucherData, $transaction, $validatedData, $randomRefNumber) {
        General_journal_voucher::insert($voucherData);

        $transaction->update([
            'rv_erp' => $randomRefNumber,
            'customer_from' => $validatedData['customer'],
            'updated_by' => Auth::user()->name,
            'maid_erp' => $validatedData['maid'],
            'updated_at' => now(),
        ]);
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Receipt Voucher saved successfully'
    ], 201);
}



// to bring live transactions from stripe
public function transactions(Request $request)
{
    if ($request->ajax()) {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Use autoPagingIterator to handle pagination automatically
            $chargesIterator = Charge::all(['limit' => 100])->autoPagingIterator();

            $data = [];
            foreach ($chargesIterator as $charge) {
                $data[] = [
                    'id' => $charge['id'],
                    'amount' => $charge['amount'],
                    'currency' => $charge['currency'],
                    'description' => $charge['description'],
                    'status' => $charge['status'],
                    'receipt_url' => $charge['receipt_url'],
                    'created' => date('Y-m-d H:i:s', $charge['created']),
                    'billing_email' => $charge['billing_details']['email'] ?? null,
                    'billing_name' => $charge['billing_details']['name'] ?? null,
                    'product_name' => $charge['metadata']['product_name'] ?? null,
                ];
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn(
                    'amount',
                    function ($row) {
                        $amount = number_format($row['amount'] / 100, 2);
                        $url = $row['receipt_url'];
                        return '<a href="' . $url . '" target="_blank">' . $amount . '</a>';
                    }
                )
                ->rawColumns(['amount'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    return view('stripe.payments');
}

      



}
