<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NGeniusService
{
    protected string $baseUrl;
    protected string $basicAuth;
    protected string $outletId;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.ngenius.base_url'), '/');
        $this->basicAuth = (string) config('services.ngenius.basic_auth');
        $this->outletId  = (string) config('services.ngenius.outlet_id');
    }

    public function getAccessToken(): string
    {
        if (empty($this->basicAuth)) {
            throw new \RuntimeException('N-Genius basic auth is not configured.');
        }

        $url = $this->baseUrl . '/identity/auth/access-token';

        // ✅ Match Next.js behaviour: POST with Basic auth and **empty JSON body**
        $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->basicAuth,
                'Content-Type'  => 'application/vnd.ni-identity.v1+json',
                'Accept'        => 'application/vnd.ni-identity.v1+json',
            ])
            ->send('POST', $url, [
                'json' => (object) [], 
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'N-Genius Auth Failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        $token = $response->json('access_token');

        if (! $token) {
            throw new \RuntimeException('N-Genius Auth Failed: access_token missing in response.');
        }

        return $token;
    }

    public function createInvoice(array $payload): array
    {
        $token = $this->getAccessToken();

        $url = $this->baseUrl . '/invoices/outlets/' . $this->outletId . '/invoice';

        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/vnd.ni-invoice.v1+json',
                'Content-Type'  => 'application/vnd.ni-invoice.v1+json',
            ])
            ->post($url, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'N-Genius Invoice Failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        return $response->json();
    }

    public function getOrderStatus(string $orderReference): array
    {
        $token = $this->getAccessToken();

        $url = $this->baseUrl . '/transactions/outlets/' . $this->outletId . '/orders/' . $orderReference;

        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/vnd.ni-payment.v2+json',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'N-Genius Order Status Failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        return $response->json();
    }

   /**
     * Process a refund for a captured payment
     * 
     * For invoice payments, the refund URL is found in:
     * response._embedded.payment[0]._links["cnp:refund"].href
     * 
     * For direct orders, it may be in:
     * response._embedded["cnp:capture"][0]._links.self.href + "/refund"
     * 
     * @param string $orderReference The order reference
     * @param array $payload Refund payload containing amount details
     * @return array The refund response
     */
    public function processRefund(string $orderReference, array $payload): array
    {
        $token = $this->getAccessToken();

        // Get the order status to extract the refund URL
        $orderData = $this->getOrderStatus($orderReference);
        
        $payment = $orderData['_embedded']['payment'][0] ?? null;
        
        if (!$payment) {
            throw new \RuntimeException('No payment found for this order. Cannot process refund.');
        }
        
        \Log::info('Processing refund for order', [
            'order_reference' => $orderReference,
            'payment_state' => $payment['state'] ?? 'N/A',
            'payment_id' => $payment['_id'] ?? 'N/A',
        ]);
        
        // Method 1: Check for cnp:refund link in payment object (for invoice/purchase payments)
        $refundUrl = $payment['_links']['cnp:refund']['href'] ?? null;
        
        if ($refundUrl) {
            \Log::info('Using cnp:refund link from payment', [
                'refund_url' => $refundUrl
            ]);
        } else {
            // Log what links ARE available
            \Log::info('cnp:refund not found, checking available links', [
                'available_links' => array_keys($payment['_links'] ?? []),
                'payment_links' => $payment['_links'] ?? 'No links found'
            ]);
            
            // Method 2: Check for capture in embedded data (for direct order payments)
            $capture = $orderData['_embedded']['cnp:capture'][0] ?? null;
            
            if ($capture && isset($capture['_links']['self']['href'])) {
                $refundUrl = rtrim($capture['_links']['self']['href'], '/') . '/refund';
                \Log::info('Using capture-based refund URL', [
                    'refund_url' => $refundUrl
                ]);
            } else {
                // No valid refund URL found
                $paymentState = $payment['state'] ?? 'UNKNOWN';
                
                throw new \RuntimeException(
                    "No refund URL found for this payment. Payment state: {$paymentState}. " .
                    "The payment might not be in a refundable state."
                );
            }
        }

        \Log::info('Posting refund to N-Genius', [
            'url' => $refundUrl,
            'payload' => $payload
        ]);

        $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/vnd.ni-payment.v2+json',
                'Content-Type'  => 'application/vnd.ni-payment.v2+json',
            ])
            ->post($refundUrl, $payload);

        if (! $response->successful()) {
            \Log::error('N-Genius refund API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            throw new \RuntimeException(
                'N-Genius Refund Failed: ' . $response->status() . ' ' . $response->body()
            );
        }

        $refundData = $response->json();
        
        \Log::info('Refund successful', [
            'refund_id' => $refundData['_id'] ?? 'N/A',
            'state' => $refundData['state'] ?? 'N/A'
        ]);

        return $refundData;
    }

}
