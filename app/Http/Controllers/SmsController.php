<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Config;
use App\Models\UpcomingInstallment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\SmsLog;

class SmsController extends Controller
{
  

public function sendBulkSmsForp4(Http $http)
{
    $targetDate = Carbon::today()->addDay();

    $rows = UpcomingInstallment::with([
        'contractRef' => fn($q) => $q->where('contract_status', 1),
        'customerInfo'
    ])
    ->where('accrued_date', $targetDate->toDateString())
    ->whereHas('contractRef', fn($q) => $q->where('contract_status', 1))
    ->get();

    $customers = $rows
        ->filter(fn($r) => $r->customerInfo && $r->customerInfo->phone)
        ->map(fn($r) => [
            'phone'  => $r->customerInfo->phone,
            'amount' => $r->amount
        ])
        ->unique('phone')
        ->values();

    if ($customers->isEmpty()) {
        Log::warning('⚠️ No customers found for cron SMS on: ' . $targetDate->toDateString());
        return response()->json(['message' => 'No customers found'], 204);
    }

    $key      = env('SMSCOUNTRY_KEY');
    $token    = env('SMSCOUNTRY_TOKEN');
    $basic    = base64_encode("{$key}:{$token}");
    $url      = "https://restapi.smscountry.com/v0.1/Accounts/{$key}/SMSes/";
    $senderId = env('SMSCOUNTRY_SENDER', 'HomeCare');
    $company  = env('company_name', 'Our Company');

    $results = [];

    foreach ($customers as $cust) {
        $number = preg_replace('/[^0-9]/', '', $cust['phone']);

        if (strlen($number) === 10 && str_starts_with($number, '05')) {
            $number = '971' . substr($number, 1);
        }

        if (!preg_match('/^9715[0-9]{8}$/', $number)) {
            $results[] = [
                'number'  => $number,
                'status'  => 400,
                'success' => false,
                'message' => 'Invalid phone format',
            ];

            SmsLog::create([
                'number'  => $number,
                'status'  => 400,
                'success' => false,
                'message' => 'Invalid phone format',
                'text'    => null,
            ]);

            continue;
        }

        $message = "Dear Customer, this is From TADBEER {$company} your monthly payment of AED {$cust['amount']} is due for the month and date {$targetDate->toDateString()}.

        please ignore this message if you have already paid.

        Thank you.

        عزيزي العميل، الدفعة الشهرية لخدماتنا الخاصة بالخادمة وقدرها  {$cust['amount']} درهم مستحقة لشهر وتاريخ {$targetDate->toDateString()}. 
        يرجى تجاهل هذه الرسالة إذا كنت قد قمت بالدفع بالفعل. شكراً لكم.";

        try {
            $res = $http->withHeaders([
                'Authorization' => "Basic {$basic}",
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($url, [
                'Text'     => $message,
                'Number'   => $number,
                'SenderId' => $senderId
            ]);

            $status  = $res->status();
            $success = filter_var($res->json()['Success'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $msg     = $res->json()['Message'] ?? '';

            $results[] = [
                'number'  => $number,
                'status'  => $status,
                'success' => $success,
                'message' => $msg,
            ];

            SmsLog::create([
                'number'  => $number,
                'status'  => $status,
                'success' => $success,
                'message' => $message,
                'text'    => $message,
            ]);
        } catch (\Throwable $e) {
            $results[] = [
                'number'  => $number,
                'status'  => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ];

            SmsLog::create([
                'number'  => $number,
                'status'  => 500,
                'success' => false,
                'message' => $e->getMessage(),
                'text'    => $message,
            ]);
        }
    }

    return response()->json([
        'summary' => $results,
        'sent'    => collect($results)->where('success', true)->count(),
        'failed'  => collect($results)->where('success', false)->count(),
    ]);
}



// URL  /datatable/sms-log
public function LogSmsP4(Request $request)
{
    $query = SmsLog::query();

    if ($search = $request->query('search')) {
        $query->where('number', 'like', "%{$search}%")
              ->orWhere('message', 'like', "%{$search}%");
    }

$perPage = (int) $request->query('perPage', 10);
return $query->orderByDesc('id')->paginate($perPage);

}



}

