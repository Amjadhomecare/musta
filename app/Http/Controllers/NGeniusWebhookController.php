<?php

namespace App\Http\Controllers;

use App\Models\NetWorkLink;
use Illuminate\Http\Request;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use Illuminate\Support\Str;


class NGeniusWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Log entire payload for debugging
        // Log::info('N-Genius Webhook Received', $request->all());

        $payload = $request->all();

        // 2. Extract payment node if exists
        $payment = $payload['order']['_embedded']['payment'][0] ?? [];

        // 3. Extract references
        $orderRef = $payment['orderReference']
            ?? ($payload['order']['reference'] ?? null);

        $paymentRef = $payment['reference'] ?? null;

        if (!$orderRef && !$paymentRef) {
            return response()->json(['message' => 'No reference'], 400);
        }

        // 4. Find NetWorkLink with eager loading of customer relationship
        $netLink = NetWorkLink::with('customer')
            ->where('order_reference', $orderRef)
            ->orWhere('gateway_reference', $paymentRef)
            ->first();

        if (!$netLink) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // 5. Extract event name and payment state
        $eventName = strtoupper($payload['eventName'] ?? 'UNKNOWN');
        $paymentState = strtoupper($payment['state'] ?? 'UNKNOWN');

        \Log::info('N-Genius Webhook Processing', [
            'order_ref' => $orderRef,
            'payment_ref' => $paymentRef,
            'event_name' => $eventName,
            'payment_state' => $paymentState,
            'current_status' => $netLink->status,
        ]);

        // 6. Map event/state → internal numeric status code
        // Priority: eventName (webhook event) over payment state
        $statusMap = match (true) {
            // Success events
            $eventName === 'PURCHASED' => NetWorkLink::STATUS_PAID,
            in_array($paymentState, ['PURCHASED', 'CAPTURED', 'SUCCESS', 'PAID']) => NetWorkLink::STATUS_PAID,
            
            // Full Refund events
            $eventName === 'REFUNDED' => NetWorkLink::STATUS_REFUNDED,
            $eventName === 'PURCHASE_REVERSED' => NetWorkLink::STATUS_REFUNDED,
            $eventName === 'REFUND_REQUESTED' => NetWorkLink::STATUS_REFUNDED,
            $paymentState === 'REFUNDED' => NetWorkLink::STATUS_REFUNDED,
            
            // Partial Refund events (map to REFUNDED as well, since partially refunded is still a type of refund)
            $eventName === 'PARTIALLY_REFUNDED' => NetWorkLink::STATUS_REFUNDED,
            $eventName === 'PARTIAL_REFUND_REQUESTED' => NetWorkLink::STATUS_REFUNDED,
            
            // Refund voided (payment was refunded but then the refund was cancelled - back to PAID)
            $eventName === 'REFUND_VOIDED' => NetWorkLink::STATUS_PAID,
            $eventName === 'REFUND_VOID_REQUESTED' => NetWorkLink::STATUS_PAID,
            
            // Refund/Partial Refund failures
            $eventName === 'REFUND_FAILED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'REFUND_REQUEST_FAILED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'PARTIAL_REFUND_FAILED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'PARTIAL_REFUND_REQUEST_FAILED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'REFUND_VOID_FAILED' => NetWorkLink::STATUS_FAILED,
            
            // Purchase failure events
            $eventName === 'PURCHASE_DECLINED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'PURCHASE_FAILED' => NetWorkLink::STATUS_FAILED,
            $eventName === 'PURCHASE_REVERSAL_FAILED' => NetWorkLink::STATUS_FAILED,
            in_array($paymentState, ['FAILED', 'DECLINED', 'ERROR']) => NetWorkLink::STATUS_FAILED,
            
            // Expired
            $paymentState === 'EXPIRED' => NetWorkLink::STATUS_EXPIRED,
            
            // Canceled/Cancelled
            in_array($paymentState, ['CANCELED', 'CANCELLED']) => NetWorkLink::STATUS_CANCELED,
            
            // Default to pending for unknown states
            default => NetWorkLink::STATUS_PENDING,
        };

        // 7. Update status & create journal entry (wrapped in transaction)
        \DB::transaction(function () use ($netLink, $statusMap, $eventName, $paymentState, $orderRef, $paymentRef) {
            $oldStatus = $netLink->status; // Capture old status inside transaction for atomicity

            // Set paid_at timestamp only on first successful payment
            if ($statusMap === NetWorkLink::STATUS_PAID && !$netLink->paid_at) {
                $netLink->paid_at = now();
            }
            
            // Clear paid_at if payment was refunded
            if ($statusMap === NetWorkLink::STATUS_REFUNDED && $netLink->paid_at) {
                $netLink->paid_at = null;
            }

            $netLink->status = $statusMap;
            $netLink->save();

            // 8. Create journal entries only when status becomes PAID and was not previously PAID
            if ($statusMap === NetWorkLink::STATUS_PAID && $oldStatus !== NetWorkLink::STATUS_PAID) {
                \Log::info('Creating PAYMENT journal entries', [
                    'order_ref' => $orderRef,
                    'old_status' => $oldStatus,
                    'new_status' => $statusMap,
                ]);
                
                try {
                    // Retrieve NETWORK WALLET ledger
                    $networkWalletLedger = \DB::table('all_account_ledger__d_b_s')
                        ->where('ledger', 'NETWORK WALLET')
                        ->first();

                    if (!$networkWalletLedger) {
                        \Log::warning('NETWORK WALLET ledger not found - cannot create payment journal');
                        return;
                    }

                    // Load customer with ledger_id
                    $customer = $netLink->customer;
                    
                    if (!$customer) {
                        \Log::warning('Customer not found for NetWorkLink', ['net_link_id' => $netLink->id]);
                        return;
                    }
                    
                    if (!$customer->ledger_id) {
                        \Log::warning('Customer has no ledger_id', ['customer_id' => $customer->id]);
                        return;
                    }

                    // Prepare journal vouchers
                    $amount = (float) $netLink->amount_value;
                    $date = now()->format('Y-m-d');
                    $refCode = 'ng_' . Str::random(5);

                    $voucherData = [
                        // Debit: NETWORK WALLET
                        [
                            'date' => $date,
                            'refCode' => $refCode,
                            'refNumber' => 0,
                            'voucher_type' => 'Receipt Voucher',
                            'type' => 'debit',
                            'ledger_id' => $networkWalletLedger->id,
                            'amount' => $amount,
                            'notes' => "N-Genius Payment: {$orderRef}" . "\n" . "customer: {$customer->name}" . "\n" . "amount: {$amount}",
                            'created_by' => 'ngenius_webhook',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        // Credit: Customer
                        [
                            'date' => $date,
                            'refCode' => $refCode,
                            'refNumber' => 0,
                            'voucher_type' => 'Receipt Voucher',
                            'type' => 'credit',
                            'ledger_id' => $customer->ledger_id,
                            'amount' => $amount,
                            'notes' => "N-Genius Gateway Ref: {$paymentRef}" . "\n" . "customer: {$customer->name}" . "\n" . "amount: {$amount}",
                            'created_by' => 'ngenius_webhook',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ];

                    General_journal_voucher::insert($voucherData);
                    
                    \Log::info('Payment journal entries created successfully', [
                        'ref_code' => $refCode,
                        'amount' => $amount,
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Failed to create payment journal entries', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't throw - allow webhook to succeed even if journal fails
                }
            }

            // 9. Create REFUND journal entries only when status becomes REFUNDED and was previously PAID
            if ($statusMap === NetWorkLink::STATUS_REFUNDED && $oldStatus === NetWorkLink::STATUS_PAID) {
                \Log::info('Creating REFUND journal entries', [
                    'order_ref' => $orderRef,
                    'old_status' => $oldStatus,
                    'new_status' => $statusMap,
                    'amount' => $netLink->amount_value
                ]);
                
                try {
                    // Retrieve NETWORK WALLET ledger
                    $networkWalletLedger = \DB::table('all_account_ledger__d_b_s')
                        ->where('ledger', 'NETWORK WALLET')
                        ->first();

                    if (!$networkWalletLedger) {
                        \Log::warning('NETWORK WALLET ledger not found - cannot create refund journal');
                        return;
                    }

                    // Load customer with ledger_id (already eager loaded)
                    $customer = $netLink->customer;
                    
                    if (!$customer) {
                        \Log::warning('Customer not found for NetWorkLink', ['net_link_id' => $netLink->id]);
                        return;
                    }
                    
                    if (!$customer->ledger_id) {
                        \Log::warning('Customer has no ledger_id', ['customer_id' => $customer->id]);
                        return;
                    }

                    // Prepare refund journal vouchers (REVERSED from payment)
                    $amount = (float) $netLink->amount_value;
                    $date = now()->format('Y-m-d');
                    $refCode = 'ngr_' . Str::random(5);

                    $voucherData = [
                        // Debit: Customer (reversed from payment)
                        [
                            'date' => $date,
                            'refCode' => $refCode,
                            'refNumber' => 0,
                            'voucher_type' => 'Journal Voucher',
                            'type' => 'debit',
                            'ledger_id' => $customer->ledger_id,
                            'amount' => $amount,
                            'notes' => "N-Genius Refund: {$orderRef}" . "\n" . "customer: {$customer->name}" . "\n" . "amount: {$amount}",
                            'created_by' => 'ngenius_webhook',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        // Credit: NETWORK WALLET (reversed from payment)
                        [
                            'date' => $date,
                            'refCode' => $refCode,
                            'refNumber' => 0,
                            'voucher_type' => 'Journal Voucher',
                            'type' => 'credit',
                            'ledger_id' => $networkWalletLedger->id,
                            'amount' => $amount,
                            'notes' => "N-Genius Refund Gateway Ref: {$paymentRef}" . "\n" . "customer: {$customer->name}" . "\n" . "amount: {$amount}",
                            'created_by' => 'ngenius_webhook',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ];

                    General_journal_voucher::insert($voucherData);
                    
                    \Log::info('Refund journal entries created successfully', [
                        'ref_code' => $refCode,
                        'amount' => $amount,
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Failed to create refund journal entries', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't throw - allow webhook to succeed even if journal fails
                }
            }
        });

        // 9. Respond quickly (required by N-Genius)
        return response()->json(['message' => 'OK'], 200);
    }
}
