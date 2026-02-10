<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\General_journal_voucher;
use App\Models\AsyncStripe;
use Stripe\Charge;



class StripeWebhookController extends Controller
{
    // for testing on local
    // public function handle(Request $request)
    // {
    //     $payload = $request->getContent();
    //     $event = json_decode($payload, true);
    
    //     Log::info('Stripe Webhook Received Object:', $event['data']['object'] ?? []);
    //     Log::info('Metadata:', $event['data']['object']['metadata'] ?? []);
    
    //     if (!isset($event['type']) || $event['type'] !== 'invoice.payment_succeeded') {
    //         return response('Event ignored', 200);
    //     }
    
    //     $object = $event['data']['object'];

    //     Log::info('Stripe Webhook Object:', $object);
    
    //     $metadata = $object['lines']['data'][0]['price']['metadata'] ?? [];
    
    //     $customer = $metadata['customer'] ?? null;
    //     $maid = $metadata['maid_erp'] ?? null;
    //     $note = $metadata['erp_note'] ?? null;
    
    //     if (!$customer && isset($object['customer'])) {
    //         try {
    //             \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    //             $stripeCustomer = \Stripe\Customer::retrieve($object['customer']);
    //             Log::info('Fallback Customer Metadata:', json_decode(json_encode($stripeCustomer), true));
    //             $customer = $stripeCustomer->metadata['customer'] ?? $stripeCustomer->name ?? null;
    //         } catch (\Exception $e) {
    //             Log::error('Failed to retrieve Stripe customer metadata: ' . $e->getMessage());
    //         }
    //     }
    
    //     $stripeId = $object['charge'] ?? null;
    //     $amount = $object['amount_paid'] / 100;
    //     $date = now()->format('Y-m-d');
    
    //     if (!$stripeId || !$date || !$customer || !$amount || !DB::table('all_account_ledger__d_b_s')->where('ledger', $customer)->exists()) {
    //         Log::error('Validation failed for Stripe webhook: Missing or unmatched customer');
    //         return response('Validation error', 400);
    //     }
    
    //     $randomRefNumber = "str_" . Str::random(5);
    
    //     $voucherData = [
    //         [
    //             'date' => $date,
    //             'refCode' => $randomRefNumber,
    //             'refNumber' => 0,
    //             'voucher_type' => 'Receipt Voucher',
    //             'type' => 'debit',
    //             'account' => 'STRIPE',
    //             'amount' => $amount,
    //             'maid_name' => $maid ?? 'No data',
    //             'notes' => $note ?? 'No data',
    //             'created_by' => 'webhook',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //         [
    //             'date' => $date,
    //             'refCode' => $randomRefNumber,
    //             'refNumber' => 0,
    //             'voucher_type' => 'Receipt Voucher',
    //             'type' => 'credit',
    //             'account' => $customer,
    //             'amount' => $amount,
    //             'maid_name' => $maid ?? 'No data',
    //             'notes' => $stripeId,
    //             'created_by' => 'webhook',
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]
    //     ];
    
    //     DB::transaction(function () use ($voucherData, $object, $maid, $note, $customer) {
    //         General_journal_voucher::insert($voucherData);
    
    //         $chargeId = $object['charge'] ?? null;
    
    //         if ($chargeId) {
    //             try {
    //                 \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    //                 $charge = \Stripe\Charge::retrieve($chargeId);
    //                 Log::info('Stripe Charge Retrieved:', json_decode(json_encode($charge), true));
    
    //                 AsyncStripe::updateOrCreate(
    //                     ['stripe_id' => $chargeId],
    //                     [
    //                         'amount' => $object['amount_paid'] / 100,
    //                         'currency' => $object['currency'] ?? 'aed',
    //                         'description' => $object['description'] ?? null,
    //                         'status' => $object['status'] ?? 'succeeded',
    //                         'billing_email' => $charge->billing_details->email ?? null,
    //                         'billing_name' => $charge->billing_details->name ?? null,
    //                         'maid_erp' => $maid ?? null,
    //                         'customer_from' => $customer,
    //                         'note' => $note ?? null,
    //                         'branch' => null,
    //                         'created_by' => 'webhook',
    //                         'updated_by' => 'webhook',
    //                         'stripe_created_at' => date('Y-m-d H:i:s', $charge->created),
    //                     ]
    //                 );
    
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to retrieve charge from Stripe: ' . $e->getMessage());
    //             }
    //         }
    //     });
    
    //     return response('Voucher saved from webhook!', 201);
    // }


    // for production
public function handle(Request $request)
{
    /* 0. Verify signature */
    $webhookSecret = config('services.stripe.webhook_secret');
    $payload   = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');

    try {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        Log::error('Stripe Webhook signature verification failed: '.$e->getMessage());
        return response('Signature verification failed', 400);
    }

    if (($event->type ?? '') !== 'invoice.payment_succeeded') {
        return response('Event ignored', 200);
    }

    $object = $event->data->object;

    /* 1. Customer lookup  (meta → price → name) */
    $customer = null;
    $maid     = null;
    $note     = null;
    $stripeCustomerName = null;          // keep for step-3 fallback

    if (!empty($object->customer)) {
        try {
            $stripeCustomer     = \Stripe\Customer::retrieve($object->customer, []);
            $customerMeta       = $stripeCustomer->metadata['customer'] ?? null;
            $stripeCustomerName = $stripeCustomer->name              ?? null;

            if ($customerMeta) {
                $customer = $customerMeta; // step-1 success
            }
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve Stripe customer metadata: '.$e->getMessage());
        }
    }

    // step-2: price metadata (only if step-1 didn’t give us a value)
    if (!$customer) {
        $priceMeta = $object->lines->data[0]->price->metadata ?? [];
        $customer  = $priceMeta['customer']  ?? null;
        $maid      = $priceMeta['maid_erp']  ?? null;
        $note      = $priceMeta['erp_note']  ?? null;
    }

    // step-3: fall back to Stripe Customer name
    if (!$customer && $stripeCustomerName) {
        $customer = $stripeCustomerName;
    }

    /* 2. Early-exit guards */
    if (!$customer) {
        return response('No ERP customer metadata found – event ignored', 200);
    }

    if (!DB::table('all_account_ledger__d_b_s')->where('ledger', $customer)->exists()) {
        return response('Customer not in ledger – event ignored', 200);
    }

    $stripeId = $object->charge ?? null;
    if (!$stripeId) {
        return response('No charge ID – event ignored', 200);
    }

    /* 3. Prepare voucher rows */
    $amount  = $object->amount_paid / 100;
    $date    = now()->format('Y-m-d');
    $refCode = 'str_'.Str::random(5);

$stripeLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', 'STRIPE')->first();
$customerLedger = DB::table('all_account_ledger__d_b_s')->where('ledger', $customer)->first();

if (!$stripeLedger || !$customerLedger) {
    return response('Missing ledger entry for STRIPE or customer – event ignored', 200);
}

$voucherData = [
    [
        'date'         => $date,
        'refCode'      => $refCode,
        'refNumber'    => 0,
        'voucher_type' => 'Receipt Voucher',
        'type'         => 'debit',
        'ledger_id'    => $stripeLedger->id,
        'amount'       => $amount,
        'notes'        => $note ?? 'No data',
        'created_by'   => 'webhook',
        'created_at'   => now(),
        'updated_at'   => now(),
    ],
    [
        'date'         => $date,
        'refCode'      => $refCode,
        'refNumber'    => 0,
        'voucher_type' => 'Receipt Voucher',
        'type'         => 'credit',
        'ledger_id'    => $customerLedger->id,
        'amount'       => $amount,
        'notes'        => $stripeId,
        'created_by'   => 'webhook',
        'created_at'   => now(),
        'updated_at'   => now(),
    ],
];


    /* 4. Atomic write */
    DB::transaction(function () use ($voucherData, $object, $maid, $note, $customer, $stripeId) {
        General_journal_voucher::insert($voucherData);

        try {
            $charge = \Stripe\Charge::retrieve($stripeId);

            AsyncStripe::updateOrCreate(
                ['stripe_id' => $stripeId],
                [
                    'amount'            => $object->amount_paid / 100,
                    'currency'          => $object->currency ?? 'aed',
                    'description'       => $object->description ?? null,
                    'status'            => $object->status      ?? 'succeeded',
                    'billing_email'     => $charge->billing_details->email ?? null,
                    'billing_name'      => $charge->billing_details->name  ?? null,
                    'maid_erp'          => $maid ?? null,
                    'customer_from'     => $customer,
                    'note'              => $note ?? null,
                    'branch'            => null,
                    'created_by'        => 'webhook',
                    'updated_by'        => 'webhook',
                    'stripe_created_at' => date('Y-m-d H:i:s', $charge->created),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve charge from Stripe: '.$e->getMessage());
        }
    });

    return response('Voucher saved from webhook!', 201);
}

    
}