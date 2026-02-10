<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmsCountryController extends Controller
{
    public function send(Request $request)
    {
        // Validate message and phone numbers
        $validated = $request->validate([
            'text'    => ['required', 'string'],
            'numbers' => ['required', 'array', 'min:1'],
            'numbers.*' => ['required', 'string'],
        ]);

        // ==== Hard-coded credentials ====
        $KEY    = '0juPkBxuWdgJZDC3LtXK';
        $TOKEN  = 'lCDjo4hBwMGEmiivcXEcjFJmr3ubVgpdq0nk6FO3';
        $SENDER = 'HomeCare';
        $TIMEOUT = 15;

        $BASIC  = base64_encode("$KEY:$TOKEN");
        $URL    = "https://restapi.smscountry.com/v0.1/Accounts/$KEY/SMSes/";

        // Clean up and normalize UAE numbers
        $numbers = [];
        foreach ($validated['numbers'] as $raw) {
            $digits = preg_replace('/\D/', '', $raw);
            if (Str::startsWith($digits, '05') && strlen($digits) === 10) {
                $digits = '971' . substr($digits, 1);
            }
            if (Str::startsWith($digits, '9715') && strlen($digits) === 12) {
                $numbers[] = $digits;
            }
        }

        if (empty($numbers)) {
            return response()->json(['ok' => false, 'error' => 'Invalid numbers.'], 422);
        }

        $results = [];
        foreach ($numbers as $number) {
            try {
                $response = Http::withHeaders([
                        'Authorization' => "Basic $BASIC",
                        'Accept'        => 'application/json',
                    ])
                    ->timeout($TIMEOUT)
                    ->post($URL, [
                        'Text'     => $validated['text'],
                        'Number'   => $number,
                        'SenderId' => $SENDER,
                    ]);

                $json = $response->json();
                $results[] = [
                    'number'  => $number,
                    'status'  => strtolower($json['Success'] ?? '') === 'true',
                    'message' => $json['Message'] ?? 'Unknown',
                    'uuid'    => $json['MessageUUID'] ?? null,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'number'  => $number,
                    'status'  => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'ok'      => collect($results)->contains(fn($r) => $r['status']),
            'results' => $results,
        ]);
    }
}
