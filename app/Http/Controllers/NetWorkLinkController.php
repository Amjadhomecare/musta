<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MaidsDB;
use App\Models\NetWorkLink;
use App\Services\NGeniusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;


class NetWorkLinkController extends Controller
{
    public function store(Request $request, NGeniusService $ngenius)
    {
        $data = $request->validate([
            'customer_id'             => ['required', 'integer', 'exists:customers,id'],
            'maid_id'                 => ['nullable', 'integer', 'exists:maids_d_b_s,id'],
            'customer_email'          => ['nullable', 'email'],
            'amount_value'            => ['required', 'numeric', 'min:0.01'],
            'expiry_date'             => ['required', 'date'],
            'transaction_type'        => ['required', Rule::in(['PURCHASE', 'AUTH'])],
            'skip_email_notification' => ['boolean'],
            'note'                    => ['nullable', 'string'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $maid     = !empty($data['maid_id'])
            ? MaidsDB::find($data['maid_id'])
            : null;

        // Decide which email to send to N-Genius
        $email = $data['customer_email'];

        // Fallback so lastName is never empty (to avoid 422 "Invalid Last Name")
        $firstName = $customer->name;
           ;
        $lastName  = $customer->name;

        // Amount in AED (you already tested plain 100.00 in Postman and it works)
        $amount = (float) $data['amount_value'];

        // Build payload exactly according to N-Genius invoice API
        $payload = [
            'firstName'         => $firstName,
            'lastName'          => $lastName,
            'email'             => $email ?? 'gg@gmail.com',
            'transactionType'   => $data['transaction_type'], 
            'emailSubject'      => 'Invoice from ' . config('app.name'),
            'invoiceExpiryDate' => $data['expiry_date'], 
            'paymentAttempts'   =>5, 
            'items' => [
                [
                    'description' => $maid
                        ? ('Maid payment: ' . ($maid->name ?? $maid->id))
                        : 'Service payment',
                    'totalPrice'  => [
                        'currencyCode' => 'AED',
                        'value'        => $amount.'00',
                    ],
                    'quantity'    => 1,
                ],
            ],
            'total' => [
                'currencyCode' => 'AED',
                'value'        => $amount.'00',
            ],
            'message' => $data['note'] ?: 'Thank you for your payment.',
            'skipInvoiceCreatedEmailNotification' => (bool) ($data['skip_email_notification'] ?? false),
        ];

        // Optional debugging while testing:
        // \Log::info('N-Genius invoice payload', $payload);

        // Call N-Genius
        $invoice = $ngenius->createInvoice($payload);

        // Extract useful fields from N-Genius response
        $links = $invoice['_links'] ?? [];

        $netLink = NetWorkLink::create([
            'customer_id' => $customer->id,
            'maid_id'     => $maid?->id,

            'gateway_reference' => $invoice['reference']      ?? null,
            'order_reference'   => $invoice['orderReference'] ?? null,
            'outlet_ref'        => $invoice['outletRef']      ?? null,

            'expiry_date'      => $invoice['invoiceExpiryDate'] ?? $data['expiry_date'],
            'transaction_type' => $invoice['transactionType']   ?? $data['transaction_type'],
            'amount_value'     => $amount,

            'self_url'       => $links['self']['href']       ?? null,
            'payment_url'    => $links['payment']['href']    ?? ($invoice['paymentLink'] ?? null),
            'email_data_url' => $links['email-data']['href'] ?? null,
            'resend_url'     => $links['resend']['href']     ?? null,

            'skip_email_notification' => (bool) ($invoice['skipInvoiceCreatedEmailNotification']
                ?? $data['skip_email_notification']
                ?? false),

            'status'  => NetWorkLink::STATUS_PENDING,
            'paid_at' => null,
            'note'    => $data['note'] ?? null,

            'raw_request'  => $payload,
            'raw_response' => $invoice,

            'created_by' => Auth::user()->name,
        ]);

        return response()->json([
            'message'     => 'Payment link created successfully.',
            'payment_url' => $netLink->payment_url,
            'netlink_id'  => $netLink->id,
            'gateway_ref' => $netLink->gateway_reference,
        ], 201);
    }


    public function update(Request $request, $id)
{
    $netLink = NetWorkLink::findOrFail($id);

    $data = $request->validate([
        'customer_id'             => ['required', 'integer', 'exists:customers,id'],
        'maid_id'                 => ['nullable', 'integer', 'exists:maids_d_b_s,id'],
        'customer_email'          => ['nullable', 'email'],
        'amount_value'            => ['required', 'numeric', 'min:0.01'],
        'expiry_date'             => ['required', 'date'],
        'transaction_type'        => ['required', Rule::in(['PURCHASE', 'AUTH'])],
        'skip_email_notification' => ['boolean'],
        'note'                    => ['nullable', 'string'],
    ]);

    $customer = Customer::findOrFail($data['customer_id']);
    $maid     = !empty($data['maid_id'])
        ? MaidsDB::find($data['maid_id'])
        : null;

    $netLink->update([
        'customer_id'            => $customer->id,
        'maid_id'                => $maid?->id,
        'expiry_date'            => $data['expiry_date'],
        'transaction_type'       => $data['transaction_type'],
        'skip_email_notification'=> $data['skip_email_notification'] ?? false,
        'note'                   => $data['note'],
        'updated_by'             => Auth::user()->name ?? null,
    ]);

    return response()->json([
        'message' => 'NetLink updated successfully.',
        'id'      => $netLink->id,
    ]);
}



      public function index(Request $request)
    {
        // Query params from Vue / Postman
        $page     = (int) $request->input('page', 1);
        $perPage  = (int) $request->input('per_page', $request->input('pageSize', 10));
        $search   = trim((string) $request->input('search', ''));
        $start    = $request->input('start_date');
        $end      = $request->input('end_date');

        $query = NetWorkLink::with(['customer', 'maid'])
            ->orderByDesc('id');

        // Optional date filter (on created_at)
        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        // Optional search: by gateway_reference, order_reference, customer name, email
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('gateway_reference', 'like', "%{$search}%")
                  ->orWhere('order_reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                        
                        
                  });
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Shape each item for the frontend (customer_name, maid_name, status_text, etc.)
        $items = collect($paginator->items())->map(function (NetWorkLink $link) {
            $customer = $link->customer;
            $maid     = $link->maid;

            $customerName = $customer->name;
           

            return [
                'id'             => $link->id,
                'customer_id'    => $link->customer_id,
                'customer_name'  => $customerName ?: $link->customer_id,
                'maid_id'        => $link->maid_id,
                'maid_name'      => $maid->name ?? null,
                'amount_value'   => $link->amount_value,
                'status'         => $link->status,
                'status_text'    => $link->status_text ?? null, // accessor on model (optional)
                'expiry_date'    => optional($link->expiry_date)->toDateString(),
                'payment_url'    => $link->payment_url,
                'note'           => $link->note,
                'created_at'     => optional($link->created_at)->toDateTimeString(),
                'updated_at'     => optional($link->updated_at)->toDateTimeString(),
                'created_by'     => $link->created_by,
            ];
        });

        return response()->json([
            'data'         => $items,
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
        ]);
    }


    public function refreshOrderStatus($id, NGeniusService $ngenius)
    {
        $netLink = NetWorkLink::findOrFail($id);

        if (!$netLink->order_reference) {
            return response()->json([
                'message' => 'No order reference found for this payment link'
            ], 400);
        }

        try {
            // Fetch order status from N-Genius
            $orderData = $ngenius->getOrderStatus($netLink->order_reference);

            \Log::info('Manual order status refresh', [
            'response' => $orderData
            ]);

            // Extract event name and payment state
            $payment = $orderData['_embedded']['payment'][0] ?? [];
            $eventName = strtoupper($orderData['eventName'] ?? 'UNKNOWN');
            $paymentState = strtoupper($payment['state'] ?? 'UNKNOWN');

            \Log::info('Manual refresh event details', [
                'event_name' => $eventName,
                'payment_state' => $paymentState,
                'order_ref' => $netLink->order_reference
            ]);

            // Map event/state to internal status (same logic as webhook)
            $statusMap = match (true) {
                // Success events
                $eventName === 'PURCHASED' => NetWorkLink::STATUS_PAID,
                in_array($paymentState, ['PURCHASED', 'CAPTURED', 'SUCCESS', 'PAID']) => NetWorkLink::STATUS_PAID,
                
                // Refund events
                $paymentState === 'REFUNDED' => NetWorkLink::STATUS_REFUNDED,
                $eventName === 'PURCHASE_REVERSED' => NetWorkLink::STATUS_REFUNDED,
                
                // Failure events
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

            $oldStatus = $netLink->status;

            // Update paid_at only on first successful payment
            if ($statusMap === NetWorkLink::STATUS_PAID && !$netLink->paid_at) {
                $netLink->paid_at = now();
            }
            
            // Clear paid_at if payment was reversed
            if ($eventName === 'PURCHASE_REVERSED' && $netLink->paid_at) {
                $netLink->paid_at = null;
            }

            $netLink->status = $statusMap;
            $netLink->updated_by = \Auth::user()->name ?? 'system';
            $netLink->save();

            \Log::info('NetWorkLink manually updated', [
                'id' => $netLink->id,
                'old_status' => $oldStatus,
                'new_status' => $statusMap,
                'event_name' => $eventName,
                'payment_state' => $paymentState,
                'paid_at' => $netLink->paid_at
            ]);

            return response()->json([
                'message' => 'Order status refreshed successfully',
                'old_status' => $oldStatus,
                'new_status' => $statusMap,
                'status_text' => $netLink->status_text,
                'event_name' => $eventName,
                'payment_state' => $paymentState,
            ]);

        } catch (\Exception $e) {
            \Log::error('Order status refresh failed', [
                'netlink_id' => $netLink->id,
                'order_reference' => $netLink->order_reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to refresh order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a refund for a payment
     * 
     * @param Request $request
     * @param int $id NetWorkLink ID
     * @param NGeniusService $ngenius
     * @return \Illuminate\Http\JsonResponse
     */
    public function processRefund(Request $request, $id, NGeniusService $ngenius)
    {
        $netLink = NetWorkLink::findOrFail($id);

        // Validate request
        $data = $request->validate([
            'amount_value' => ['required', 'numeric', 'min:0.01'],
            'currency_code' => ['required', 'string', Rule::in(['AED', 'GBP', 'USD', 'ZAR'])],
        ]);

        if (!$netLink->order_reference) {
            return response()->json([
                'message' => 'No order reference found for this payment link'
            ], 400);
        }

        // Check if already refunded
        if ($netLink->status === NetWorkLink::STATUS_REFUNDED) {
            return response()->json([
                'message' => 'This payment has already been refunded'
            ], 400);
        }

        // Check if payment was successful before allowing refund
        if ($netLink->status !== NetWorkLink::STATUS_PAID) {
            return response()->json([
                'message' => 'Can only refund successful payments'
            ], 400);
        }

        try {
            // Convert amount to minor units (cents)
            // N-Genius expects minor units, so 100.00 AED = 10000
            $amountInMinorUnits = (int) ($data['amount_value'] * 100);

            // Build refund payload according to N-Genius API
            $payload = [
                'amount' => [
                    'currencyCode' => $data['currency_code'],
                    'value' => $amountInMinorUnits,
                ]
            ];

            \Log::info('Processing refund', [
                'netlink_id' => $netLink->id,
                'order_reference' => $netLink->order_reference,
                'amount' => $data['amount_value'],
                'payload' => $payload
            ]);

            // Call N-Genius refund API
            $refundResponse = $ngenius->processRefund($netLink->order_reference, $payload);

            // DO NOT update status here - let the webhook handle it
            // This ensures journal entries are created when status transitions from PAID to REFUNDED
            
            \Log::info('Refund requested successfully - waiting for webhook', [
                'netlink_id' => $netLink->id,
                'refund_state' => $refundResponse['state'] ?? 'UNKNOWN',
                'refund_id' => $refundResponse['_id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Refund requested successfully. Status will update when webhook is received.',
                'state' => $refundResponse['state'] ?? 'SUCCESS',
                'refund_id' => $refundResponse['_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Refund processing failed', [
                'netlink_id' => $netLink->id,
                'order_reference' => $netLink->order_reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

}
