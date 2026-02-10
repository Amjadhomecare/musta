<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsRelayController extends Controller
{
    public function send(Request $request)
    {
        $text   = $request->input('text');
        $number = $request->input('number');

        // HARD-CODED SMSCountry credentials (whitelisted domain)
        $KEY    = '0juPkBxuWdgJZDC3LtXK';
        $TOKEN  = 'lCDjo4hBwMGEmiivcXEcjFJmr3ubVgpdq0nk6FO3';
        $SENDER = 'HomeCare';
        $TIMEOUT = 15;

        $BASIC = base64_encode("$KEY:$TOKEN");
        $URL   = "https://restapi.smscountry.com/v0.1/Accounts/$KEY/SMSes/";

        try {
            $response = Http::withHeaders([
                    'Authorization' => "Basic $BASIC",
                    'Accept'        => 'application/json',
                ])
                ->timeout($TIMEOUT)
                ->post($URL, [
                    'Text'     => $text,
                    'Number'   => $number,
                    'SenderId' => $SENDER,
                ]);

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            Log::error('SMS relay failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendBulk(Request $request)
    {
        $text    = $request->input('text') ?? $request->input('Text');
        $numbers = $request->input('numbers') ?? $request->input('Numbers');
        
        // Convert string to array if needed
        if (is_string($numbers)) {
            $numbers = explode(',', $numbers);
        }

        // HARD-CODED SMSCountry credentials (whitelisted domain)
        $KEY    = '0juPkBxuWdgJZDC3LtXK';
        $TOKEN  = 'lCDjo4hBwMGEmiivcXEcjFJmr3ubVgpdq0nk6FO3';
        $SENDER = 'HomeCare';
        $TIMEOUT = 15;

        $BASIC = base64_encode("$KEY:$TOKEN");
        $URL   = "https://restapi.smscountry.com/v0.1/Accounts/$KEY/SMSes/";

        $results = [];

        foreach ($numbers as $number) {
            $number = trim($number);
            
            try {
                $response = Http::withHeaders([
                        'Authorization' => "Basic $BASIC",
                        'Accept'        => 'application/json',
                    ])
                    ->timeout($TIMEOUT)
                    ->post($URL, [
                        'Text'     => $text,
                        'Number'   => $number,
                        'SenderId' => $SENDER,
                    ]);

                $results[] = [
                    'number'  => $number,
                    'success' => $response->json()['Success'] ?? false,
                    'message' => $response->json()['Message'] ?? '',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'number'  => $number,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $sent   = collect($results)->where('success', true)->count();
        $failed = collect($results)->where('success', false)->count();

        return response()->json([
            'summary' => $results,
            'sent'    => $sent,
            'failed'  => $failed,
        ]);
    }
}

