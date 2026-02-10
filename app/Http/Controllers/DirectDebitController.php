<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DirectDebit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CancelationDd;
use Intervention\Image\Facades\Image;


class DirectDebitController extends Controller
{
    use \App\Traits\SignatureProcessingTrait;
   
    // URL: /direct-debit-list
 public function directDebitList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $query = DirectDebit::with('customer')->orderBy('created_at', 'desc');

    // Filter by status
    if ($request->filled('status')) {
        $status = $request->input('status');
        $query->where('status', $status);
    }

    // Filter by search term
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('account_title', 'like', "%{$search}%")
            ->orWhere('ref', 'like', "%{$search}%")
            ->orWhere('iban', 'like', "%{$search}%")
            ->orWhere('created_by', 'like', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        });
    }

    // Filter by active status
    if ($request->filled('active')) {
        $active = $request->input('active');
        if ($active === '0') {
            $query->where('active', 0) 
               ->where('status', 1);
            
        } elseif ($active === '1') {
            $query->where('active', 1);

        }  elseif ($active === '2') {

          $query->where('status', 1)
          ->where('active', 0)
          ->whereNotExists(function ($sub) {
              $sub->select(DB::raw(1))
                  ->from('category4_models as c4')
                  ->whereColumn('c4.customer_id', 'direct_debits.customer_id');
          });
          
         } elseif ($active === '3') {
                $query->where('status', 1)
                ->where('active', 0)
                ->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('category4_models as c4')
                        ->whereColumn('c4.customer_id', 'direct_debits.customer_id')
                        ->where('c4.contract_status', 0);
                })
                ->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('category4_models as c4_1')
                        ->whereColumn('c4_1.customer_id', 'direct_debits.customer_id')
                        ->where('c4_1.contract_status', 1);
                });

        }
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [
            $request->input('start_date') . ' 00:00:00',
            $request->input('end_date') . ' 23:59:59'
        ]);
    } elseif ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->input('start_date'));
    } elseif ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->input('end_date'));
    }

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data' => $paginator->items(),
        'total' => $paginator->total(),
        'per_page' => $paginator->perPage(),
        'current_page' => $paginator->currentPage(),
        'last_page' => $paginator->lastPage(),
    ]);
}


public function storeDirectDebit(Request $request)
{
    $randomRef = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $validated = $request->validate([
        'id'                => 'nullable|exists:direct_debits,id',
        'payment_frequency' => 'required|in:M,Q,A,O',
        'commences_on'      => 'required|date|after:today',
        'expires_on'        => 'nullable|date|after_or_equal:commences_on',  
        'iban'              => 'nullable|string|max:34',
        'account_title'     => 'nullable|string|max:255',
        'account_type'      => 'required|in:C,O',
        'paying_bank_name'  => 'nullable|string|max:255',
        'paying_bank_id'    => 'nullable|string|max:255',
        'customer_type'     => 'nullable|string|max:20',
        'customer_id_no'    => 'nullable|string|max:100',
        'fixed_amount'      => 'nullable|numeric',
        'customer_id_type'  => 'nullable|string|max:20',
        'email'             => 'nullable|email|max:255',
        'phone'             => 'nullable|string|max:20',
        'extra'             => 'nullable|array',
        'customer_id'       => 'nullable|exists:customers,id',
        'note'              => 'nullable|string|max:500',
        'sign_url'          => 'nullable|string|max:255',
        'sign_url_2'        => 'nullable|string|max:255',
        'resign_display'    => 'nullable|boolean',
    ]);

    // Normalize dates
    $validated['commences_on'] = Carbon::parse($validated['commences_on'])->format('Y-m-d');
    $validated['expires_on'] = $request->filled('expires_on')
        ? Carbon::parse($request->input('expires_on'))->format('Y-m-d')
        : Carbon::parse($validated['commences_on'])->addYears(10)->format('Y-m-d');

    $isUpdate = $request->filled('id');
    $userName = auth()->user()->name ?? 'system';
    $signUrl  = $validated['sign_url'] ?? null;
    $signUrl2 = $validated['sign_url_2'] ?? null;
    unset($validated['sign_url']); // Remove sign_url from main update payload
    unset($validated['sign_url_2']); // Remove sign_url_2 from main update payload

    if ($isUpdate) {
        // Find existing record
        $directDebit = DirectDebit::findOrFail($request->input('id'));

        $validated['created_by'] = $directDebit->created_by;
        $validated['updated_by'] = $userName;

        // Update main fields
        $directDebit->update($validated);

        // Update the `extra.sign` and `extra.sign2` keys if signature URLs exist
        if (!empty($signUrl) || !empty($signUrl2)) {
            $extra = $directDebit->extra ?? [];
            if (!empty($signUrl)) {
                $extra['sign'] = $signUrl;
            }
            if (!empty($signUrl2)) {
                $extra['sign2'] = $signUrl2;
            }
            $directDebit->extra = $extra;    
            $directDebit->save();               
        }

        if ($validated['resign_display']) {
              $directDebit->status = '4';
            $directDebit->save();               
        }

    } else {
        // Create new record
        $validated['ref']        = $randomRef;
        $validated['created_by'] = $userName;
        $validated['updated_by'] = $userName;

        // Add sign_url to extra JSON if provided
        if (!empty($signUrl)) {
            $validated['extra'] = array_merge($validated['extra'] ?? [], ['sign' => $signUrl]);
        }

        $directDebit = DirectDebit::create($validated);

        // Send SMS with signing link only if send_sms checkbox was checked
        $phone = $validated['phone'] ?? null;
        if ($request->boolean('send_sms') && $phone) {
            $this->sendSigningLinkSms($directDebit->ref, $phone);
        }
    }

    return response()->json([
        'message' => $isUpdate
            ? 'Direct Debit updated successfully.'
            : 'Direct Debit created successfully.',
        'data'    => $directDebit
    ], 200);
}

/**
 * Helper: Send signing link SMS
 */
private function sendSigningLinkSms(string $ref, string $phone): void
{
    $signLink = "https://sign.homecaremaids.ae/sign-dd/{$ref}";
    $message  = "HomeCare Direct Debit: Please sign your mandate using this link: {$signLink}";

    $relayUrl = 'https://hcnextmeta.com/api/relay/sms';

    try {
        \Illuminate\Support\Facades\Http::timeout(15)
            ->post($relayUrl, [
                'text'   => $message,
                'number' => $phone,
            ]);
    } catch (\Throwable $e) {
        Log::error('DD SMS failed', ['ref' => $ref, 'error' => $e->getMessage()]);
    }
}




 public function showSignForm(string $ref)
    {
        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();
        return view('ERP.dd.sign_dd', compact('directDebit', 'ref'));
    }

    /**
     * Show the re-sign form (signature update only, no bank details)
     */
    public function showResignForm(string $ref)
    {
        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();
        return view('ERP.dd.dd_resign', compact('directDebit', 'ref'));
    }

    /**
     * Update only the signatures for a direct debit (re-sign flow)
     * POST external/resign-dd/{ref}
     */
    public function updateResignSignature(Request $request, string $ref)
    {
        $request->validate([
            'signature'  => 'required|string',
            'signature2' => 'required|string',
        ]);

        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();

        // Normalize extra to array
        $extra = $directDebit->extra ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?? [];
        }

        $disk = 'beta';

        $storeB64 = function (string $b64, string $label) use ($directDebit, $disk) {
            $parts = explode(',', $b64, 2);
            $raw   = base64_decode($parts[1] ?? $parts[0], true);

            if ($raw === false || strlen($raw) === 0) {
                abort(422, "Invalid {$label} payload.");
            }

            $fileName = sprintf(
                'dd/%s_%s_%s.png',
                $directDebit->ref,
                $label,
                now()->format('YmdHisv')
            );

            Storage::disk($disk)->put($fileName, $raw);
            return Storage::disk($disk)->url($fileName);
        };

        // Save both signatures
        $signUrl  = $storeB64($request->input('signature'), 'resign1');
        $sign2Url = $storeB64($request->input('signature2'), 'resign2');

        $extra['sign']  = $signUrl;
        $extra['sign2'] = $sign2Url;

        $directDebit->status =$directDebit::STATUS_RESIGN_REQUESTED;
        $directDebit->updated_by = "customer";
        $directDebit->extra = $extra;
        $directDebit->save();

        return response("
            <html>
                <head>
                    <title>Signatures Updated</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            background-color: #f5f5f5;
                            padding: 50px;
                        }
                        .container {
                            background: #fff;
                            padding: 40px;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                            display: inline-block;
                        }
                        h2 { color: #4CAF50; }
                        img {
                            max-width: 300px;
                            margin: 20px;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>✅ Signatures Updated Successfully!</h2>
                        <p>Your new signatures have been securely saved.</p>
                        <div>
                            <h4>Signature 1:</h4>
                            <img src='{$extra['sign']}' alt='Signature 1'>
                        </div>
                        <div>
                            <h4>Signature 2:</h4>
                            <img src='{$extra['sign2']}' alt='Signature 2'>
                        </div>
                    </div>
                </body>
            </html>
        ", 200)->header('Content-Type', 'text/html');
    }

    /**
     * Show the resign-rejection form (3 signatures: 2 digital + 1 paper upload)
     * GET external/resign-rejection/{ref}
     */
    public function showResignRejectionForm(string $ref)
    {
        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();
        return view('ERP.dd.dd_resign_rejection', compact('directDebit', 'ref'));
    }

    /**
     * Process resign-rejection form submission (3 signatures)
     * POST external/resign-rejection/{ref}
     */
    public function updateResignRejectionSignature(Request $request, string $ref)
    {
        $request->validate([
            'signature'       => 'required|string',
            'signature2'      => 'required|string',
            'paper_signature' => 'required|file|image|max:10240', // max 10MB
        ]);

        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();

        // Normalize extra to array
        $extra = $directDebit->extra ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?? [];
        }

        $disk = 'beta';

        // Helper to store base64 signature
        $storeB64 = function (string $b64, string $label) use ($directDebit, $disk) {
            $parts = explode(',', $b64, 2);
            $raw   = base64_decode($parts[1] ?? $parts[0], true);

            if ($raw === false || strlen($raw) === 0) {
                abort(422, "Invalid {$label} payload.");
            }

            $fileName = sprintf(
                'dd/%s_%s_%s.png',
                $directDebit->ref,
                $label,
                now()->format('YmdHisv')
            );

            Storage::disk($disk)->put($fileName, $raw);
            return Storage::disk($disk)->url($fileName);
        };

        // Clean up old signatures if they exist
        if (!empty($extra['sign'])) {
            $oldPath = Str::after($extra['sign'], 'dd/'); // Extract filename from URL
            if ($oldPath && $oldPath !== $extra['sign']) {
                Storage::disk($disk)->delete('dd/' . $oldPath);
            }
        }
        if (!empty($extra['sign2'])) {
            $oldPath = Str::after($extra['sign2'], 'dd/');
            if ($oldPath && $oldPath !== $extra['sign2']) {
                Storage::disk($disk)->delete('dd/' . $oldPath);
            }
        }
        if (!empty($extra['paper_sign'])) {
            $oldPath = Str::after($extra['paper_sign'], 'dd/');
            if ($oldPath && $oldPath !== $extra['paper_sign']) {
                Storage::disk($disk)->delete('dd/' . $oldPath);
            }
        }
        if (!empty($extra['paper_sign_origin'])) {
            $oldPath = Str::after($extra['paper_sign_origin'], 'dd/');
            if ($oldPath && $oldPath !== $extra['paper_sign_origin']) {
                Storage::disk($disk)->delete('dd/' . $oldPath);
            }
        }

        // Save both digital signatures
        $signUrl  = $storeB64($request->input('signature'), 'rejection_sign1');
        $sign2Url = $storeB64($request->input('signature2'), 'rejection_sign2');

        // Save paper signature (uploaded image)
        $paperFile = $request->file('paper_signature');
        
        // 1. Save original unmodified file
        $paperExt = $paperFile->getClientOriginalExtension();
        $paperOriginalName = sprintf(
            'dd/%s_paper_signature_origin_%s.%s',
            $directDebit->ref,
            now()->format('YmdHisv'),
            $paperExt
        );
        Storage::disk($disk)->putFileAs('', $paperFile, $paperOriginalName);
        $paperOriginalUrl = Storage::disk($disk)->url($paperOriginalName);

        // 2. Process and save transparent version
        $paperFileName = sprintf(
            'dd/%s_paper_signature_%s.png',
            $directDebit->ref,
            now()->format('YmdHisv')
        );
        
        // Process the image to remove background (make transparent)
        $processedImageData = $this->processSignatureRemoveBackground($paperFile->getRealPath());
        
        // Store the processed PNG image
        Storage::disk($disk)->put($paperFileName, $processedImageData);
        $paperSignUrl = Storage::disk($disk)->url($paperFileName);

        $extra['sign']  = $signUrl;
        $extra['sign2'] = $sign2Url;
        $extra['paper_sign'] = $paperSignUrl;
        $extra['paper_sign_origin'] = $paperOriginalUrl;

        $directDebit->status = DirectDebit::STATUS_RESIGN_REQUESTED;
        $directDebit->updated_by = "customer_upload_3_signatures";
        $directDebit->save();

        // Update the DdFollowUp record if it exists
        $followUp = $directDebit->followUp;
        if ($followUp) {
            $followUp->update([
                'follow_up_status' => \App\Enum\DdFollowUps::CustomerReplied->value,
                'attachment' => [
                    'sign' => $signUrl,
                    'sign2' => $sign2Url,
                    'paper_sign' => $paperSignUrl,
                    'paper_sign_origin' => $paperOriginalUrl,
                ],
                'updated_by' => 'customer_upload_3_signatures',
            ]);
        }

        return response("
            <html>
                <head>
                    <title>Signatures Submitted</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            background-color: #f5f5f5;
                            padding: 50px;
                        }
                        .container {
                            background: #fff;
                            padding: 40px;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                            display: inline-block;
                            max-width: 600px;
                        }
                        h2 { color: #4CAF50; }
                        img {
                            max-width: 250px;
                            margin: 10px;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                        }
                        .signatures { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>✅ All Signatures Submitted Successfully!</h2>
                        <p>Your signatures have been securely saved. We will process your re-sign request shortly.</p>
                        <div class='signatures'>
                            <div>
                                <h4>Digital Signature 1:</h4>
                                <img src='{$signUrl}' alt='Signature 1'>
                            </div>
                            <div>
                                <h4>Digital Signature 2:</h4>
                                <img src='{$sign2Url}' alt='Signature 2'>
                            </div>
                            <div>
                                <h4>Paper Signature:</h4>
                                <img src='{$paperSignUrl}' alt='Paper Signature'>
                            </div>
                        </div>
                    </div>
                </body>
            </html>
        ", 200)->header('Content-Type', 'text/html');
    }


public function updateSignature(Request $request)
{
    $request->validate([
        'ref'              => 'required|string|exists:direct_debits,ref',
        'signature'        => 'required|string',   
        'signature2'       => 'required|string', 
        // Bank details from Step 0
        'paying_bank_id'   => 'nullable|string|max:10',
        'paying_bank_name' => 'nullable|string|max:255',
        'account_title'    => 'nullable|string|max:255',
        'customer_id_no'   => 'nullable|string|max:100',
        'iban'             => 'nullable|string|max:34',
    ]);

    $directDebit = DirectDebit::where('ref', $request->ref)->firstOrFail();

    // Normalize extra to array
    $extra = $directDebit->extra ?? [];
    if (is_string($extra)) {
        $extra = json_decode($extra, true) ?? [];
    }

    $disk = 'beta';

    $storeB64 = function (string $b64, string $label) use ($directDebit, $disk) {
        // Accept both "data:image/png;base64,..." and plain base64
        $parts = explode(',', $b64, 2);
        $raw   = base64_decode($parts[1] ?? $parts[0], true);

        if ($raw === false || strlen($raw) === 0) {
            abort(422, "Invalid {$label} payload.");
        }

        // Unique filename per signature
        $fileName = sprintf(
            'dd/%s_%s_%s.png',
            $directDebit->ref,
            $label,
            now()->format('YmdHisv')
        );

        Storage::disk($disk)->put($fileName, $raw);

        return Storage::disk($disk)->url($fileName);
    };

    // Save both signatures (mandatory)
    $signUrl  = $storeB64($request->input('signature'),  'sign1');
    $sign2Url = $storeB64($request->input('signature2'), 'sign2');

    $extra['sign']  = $signUrl;
    $extra['sign2'] = $sign2Url;

    $directDebit->extra = $extra;

    // Save bank details if provided (from Step 0)
    if ($request->filled('paying_bank_id')) {
        $directDebit->paying_bank_id = $request->input('paying_bank_id');
    }
    if ($request->filled('paying_bank_name')) {
        $directDebit->paying_bank_name = $request->input('paying_bank_name');
    }
    if ($request->filled('account_title')) {
        $directDebit->account_title = $request->input('account_title');
    }
    if ($request->filled('customer_id_no')) {
        // Remove spaces and dashes from Emirates ID
        $directDebit->customer_id_no = preg_replace('/[\s\-]/', '', $request->input('customer_id_no'));
    }
    if ($request->filled('iban')) {
        // Remove spaces and dashes from IBAN, convert to uppercase
        $cleanIban = strtoupper(preg_replace('/[\s\-]/', '', $request->input('iban')));
        $directDebit->iban = $cleanIban;
        
        // Set account_type: 'C' (Current/Saving) for UAE IBANs, 'O' (Credit Card) for others
        $directDebit->account_type = str_starts_with($cleanIban, 'AE') ? 'C' : 'O';
    }

    $directDebit->updated_by = "customer_submitted";
    $directDebit->status = 4;
    $directDebit->save();

   return response("
        <html>
            <head>
                <title>Signatures Saved</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        background-color: #f5f5f5;
                        padding: 50px;
                    }
                    .container {
                        background: #fff;
                        padding: 40px;
                        border-radius: 10px;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        display: inline-block;
                    }
                    h2 { color: #4CAF50; }
                    img {
                        max-width: 300px;
                        margin: 20px;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>✅ Both Signatures Saved Successfully!</h2>
                    <p>Your signatures have been securely uploaded.</p>
                    <div>
                        <h4>Signature 1:</h4>
                        <img src='{$extra['sign']}' alt='Signature 1'>
                    </div>
                    <div>
                        <h4>Signature 2:</h4>
                        <img src='{$extra['sign2']}' alt='Signature 2'>
                    </div>
                </div>
            </body>
        </html>
    ", 200)->header('Content-Type', 'text/html');
}


public function uploadFile(Request $request)   // ← rename if you like
{
    $request->validate([
        'ref'  => 'required|string|exists:direct_debits,ref',
        'file' => 'required|file|max:10240',     // ⬅ any type, ≤10 MB
    ]);

    $directDebit = DirectDebit::where('ref', $request->ref)->firstOrFail();

    // current extra field → array
    $extra = is_string($directDebit->extra)
        ? json_decode($directDebit->extra, true) ?? []
        : ($directDebit->extra ?? []);

    /* delete old file if present (unchanged) */
    if (!empty($extra['file'])) {
        $disk = 'beta';
        $bucket = rtrim(Storage::disk($disk)->url(''), '/');
        if (str_starts_with($extra['file'], $bucket)) {
            $oldPath = ltrim(str_replace($bucket, '', $extra['file']), '/');
            Storage::disk($disk)->delete($oldPath);
        }
    }

    /* store new file – keep original extension */
    $file     = $request->file('file');
    $disk     = 'beta';
    $ext      = $file->getClientOriginalExtension();      // jpg, docx, pdf, …
    $fileName = 'uploads/' . $directDebit->ref . '_' . now()->format('YmdHis') . '.' . $ext;

    Storage::disk($disk)->putFileAs('', $file, $fileName);

    $url = Storage::disk($disk)->url($fileName);

    // update extra field
    $extra['file'] = $url;
    $directDebit->extra = $extra;
    $directDebit->save();

    return response()->json([
        'message'  => 'File uploaded successfully.',
        'file_url' => $url,
    ], 200, ['Cache-Control' => 'no-store']);
}


// URL: /direct-debit/{id}
public function delete($id)
{
    $directDebit = DirectDebit::findOrFail($id);

  
    if ($directDebit->status == 0 || $directDebit->status == 3) {
        $directDebit->delete();

        return response()->json([
            'message' => 'Direct Debit deleted successfully.'
        ]);
    }

    return response()->json([
        'message' => 'Direct Debit cannot be deleted unless status is Created or Rejected.'
    ], 403);
}

// URL: /request-cancellation
public function requestCancellation(Request $request)
{
    $validated = $request->validate([
        'dd_id'   => 'required|exists:direct_debits,id',
        'note'    => 'nullable|string|max:500',
        'task'    => 'nullable|integer|min:0|max:255', // tinyInt
        'meta'    => 'nullable|array',
        'comment' => 'nullable|array',
    ]);

    $ddId   = (int) $validated['dd_id'];
    $userId = auth()->id(); // matches erp_users FK if your auth users table is that

    // Block duplicate open requests for the same DD (statuses 0,1,2 considered "open")
    $hasOpen = CancelationDd::where('dd_id', $ddId)
        ->whereIn('status', [0, 1, 2]) // 0=Created, 1=In Review, 2=Processing (adjust if you use different codes)
        ->exists();

    if ($hasOpen) {
        return response()->json([
            'message' => 'There is already an open cancellation request for this Direct Debit.'
        ], 422);
    }

    $cancelReq = CancelationDd::create([
        'dd_id'     => $ddId,
        'task'      => $validated['task'] ?? 0,
        'status'    => 0, // Created / New
        'note'      => $validated['note'] ?? null,
        'meta'      => $validated['meta'] ?? [],
        'comment'   => $validated['comment'] ?? [],
        'created_by'=> $userId,
        'update_by' => $userId,
    ]);

    return response()->json([
        'message' => 'Cancellation request created successfully.',
        'data'    => $cancelReq,
    ], 201);
}

// url: /cancelation-requests
public function cancelationRequestList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page    = $request->input('page', 1);

    $query = CancelationDd::query()
        ->with([
            'directDebit:id,ref,commences_on,iban,account_title,center_bank_ref,paying_bank_name,paying_bank_id,fixed_amount,customer_id',
            'directDebit.customer:id,name',
            'createdByUser:id,name',
            'updatedByUser:id,name',
        ])
        ->orderBy('created_at', 'desc');

    // Optional filters (same as before)
    if ($request->filled('dd_id'))   $query->where('dd_id', (int)$request->input('dd_id'));
    if ($request->filled('task'))    $query->where('task', (int)$request->input('task'));
    if ($request->filled('status'))  $query->where('status', (int)$request->input('status'));

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [
            $request->input('start_date') . ' 00:00:00',
            $request->input('end_date')   . ' 23:59:59',
        ]);
    } elseif ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->input('start_date'));
    } elseif ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->input('end_date'));
    }

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('note', 'like', "%{$search}%")
              ->orWhereHas('directDebit', function ($sub) use ($search) {
                  $sub->where('account_title', 'like', "%{$search}%")
                      ->orWhere('ref', 'like', "%{$search}%");
              })
              ->orWhereHas('createdByUser', fn($sub) => $sub->where('name', 'like', "%{$search}%"))
              ->orWhereHas('updatedByUser', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
        });
    }

    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data'         => $paginator->items(),
        'total'        => $paginator->total(),
        'per_page'     => $paginator->perPage(),
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
    ]);
}


/**
 * Send Direct Debit signing link via SMS to customer
 *
 * POST /direct-debit/send-sms
 */
public function sendSigningLink(Request $request)
{
    $request->validate([
        'ref'   => 'required|string|exists:direct_debits,ref',
        'phone' => 'required|string|max:20',
    ]);

    $ref   = $request->input('ref');
    $phone = $request->input('phone');

    // Build the signing link
    $signLink = "https://sign.homecaremaids.ae/sign-dd/{$ref}";

    // SMS message
    $message = "HomeCare Direct Debit: Please sign your mandate using this link: {$signLink}";

    // Use relay API
    $relayUrl = 'https://hcnextmeta.com/api/relay/sms';

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(15)
            ->post($relayUrl, [
                'text'   => $message,
                'number' => $phone,
            ]);

        $result = $response->json() ?? [];

        // Check for success in the relay response
        $success = (strtolower($result['Success'] ?? '') === 'true') || (bool)($result['ok'] ?? false);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully.',
                'link'    => $signLink,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['Message'] ?? ($result['message'] ?? 'Failed to send SMS.'),
        ], 422);

    } catch (\Throwable $e) {
        Log::error('Direct Debit SMS failed', ['error' => $e->getMessage(), 'ref' => $ref]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

/**
 * GET /direct-debit/by-ref/{ref}
 * Fetch direct debit data by reference code for auto-filling edit form
 */
public function getByRef(string $ref)
{
    $directDebit = DirectDebit::with('customer')->where('ref', $ref)->first();

    if (!$directDebit) {
        return response()->json([
            'success' => false,
            'message' => 'Direct Debit not found with this reference code.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data'    => $directDebit,
    ]);
}

/**
 * List DD follow-ups with filters
 * GET /dd-follow-ups
 * ?status=4 for manual follow-up, ?status=3 for customer replied
 */
public function ddFollowUpList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    
    $query = \App\Models\DdFollowUp::with(['directDebit.customer'])
        ->orderBy('updated_at', 'desc');

    // Filter by follow_up_status
    if ($request->filled('status')) {
        $query->where('follow_up_status', $request->input('status'));
    }

    // Filter by attempt count
    if ($request->filled('min_attempts')) {
        $query->where('attempt_number', '>=', $request->input('min_attempts'));
    }

    // Search
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('follow_up_notes', 'like', "%{$search}%")
              ->orWhereHas('directDebit', function ($dq) use ($search) {
                  $dq->where('ref', 'like', "%{$search}%")
                     ->orWhere('account_title', 'like', "%{$search}%")
                     ->orWhereHas('customer', function ($cq) use ($search) {
                         $cq->where('name', 'like', "%{$search}%");
                     });
              });
        });
    }

    // Date filters
    if ($request->filled('start_date')) {
        $query->whereDate('created_at', '>=', $request->input('start_date'));
    }
    if ($request->filled('end_date')) {
        $query->whereDate('created_at', '<=', $request->input('end_date'));
    }

    return $query->paginate($perPage);

    }

    /**
     * Admin update of signatures from Follow Up page
     */
    public function updateFollowUpSignatures(Request $request)
    {
        $request->validate([
            'ref' => 'required|string|exists:direct_debits,ref',
            'paper_signature' => 'nullable|file|image|max:10240',
            'sign1' => 'nullable|file|image|max:5120',
            'sign2' => 'nullable|file|image|max:5120',
        ]);

        $directDebit = DirectDebit::where('ref', $request->ref)->firstOrFail();
        
        // Normalize extra
        $extra = $directDebit->extra ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?? [];
        }

        $disk = 'beta';
        $updated = false;

        // 1. Handle Paper Signature
        if ($request->hasFile('paper_signature')) {
            // Clean up old
            if (!empty($extra['paper_sign'])) {
                $old = Str::after($extra['paper_sign'], 'dd/');
                if ($old && $old !== $extra['paper_sign']) Storage::disk($disk)->delete('dd/'.$old);
            }
            if (!empty($extra['paper_sign_origin'])) {
                $old = Str::after($extra['paper_sign_origin'], 'dd/');
                if ($old && $old !== $extra['paper_sign_origin']) Storage::disk($disk)->delete('dd/'.$old);
            }

            $file = $request->file('paper_signature');
            $ts = now()->format('YmdHisv');

            // Save Original
            $origName = sprintf('dd/%s_paper_signature_origin_%s.%s', $directDebit->ref, $ts, $file->getClientOriginalExtension());
            Storage::disk($disk)->putFileAs('', $file, $origName);
            $extra['paper_sign_origin'] = Storage::disk($disk)->url($origName);

            // Save Processed
            $procName = sprintf('dd/%s_paper_signature_%s.png', $directDebit->ref, $ts);
            $processed = $this->processSignatureRemoveBackground($file->getRealPath());
            Storage::disk($disk)->put($procName, $processed);
            $extra['paper_sign'] = Storage::disk($disk)->url($procName);
            
            $updated = true;
        }

        // 2. Handle Digital Sign 1
        if ($request->hasFile('sign1')) {
             if (!empty($extra['sign'])) {
                $old = Str::after($extra['sign'], 'dd/');
                if ($old && $old !== $extra['sign']) Storage::disk($disk)->delete('dd/'.$old);
            }
            $file = $request->file('sign1');
            $name = sprintf('dd/%s_rejection_sign1_%s.%s', $directDebit->ref, now()->format('YmdHisv'), $file->getClientOriginalExtension());
            Storage::disk($disk)->putFileAs('', $file, $name);
            $extra['sign'] = Storage::disk($disk)->url($name);
            $updated = true;
        }

        // 3. Handle Digital Sign 2
        if ($request->hasFile('sign2')) {
             if (!empty($extra['sign2'])) {
                $old = Str::after($extra['sign2'], 'dd/');
                if ($old && $old !== $extra['sign2']) Storage::disk($disk)->delete('dd/'.$old);
            }
            $file = $request->file('sign2');
            $name = sprintf('dd/%s_rejection_sign2_%s.%s', $directDebit->ref, now()->format('YmdHisv'), $file->getClientOriginalExtension());
            Storage::disk($disk)->putFileAs('', $file, $name);
            $extra['sign2'] = Storage::disk($disk)->url($name);
            $updated = true;
        }

        if ($updated) {
            $directDebit->extra = $extra;
            $directDebit->save();

            // Synch DdFollowUp
            if ($directDebit->followUp) {
                $att = $directDebit->followUp->attachment ?? [];
                if (isset($extra['paper_sign'])) $att['paper_sign'] = $extra['paper_sign'];
                if (isset($extra['paper_sign_origin'])) $att['paper_sign_origin'] = $extra['paper_sign_origin'];
                if (isset($extra['sign'])) $att['sign'] = $extra['sign'];
                if (isset($extra['sign2'])) $att['sign2'] = $extra['sign2'];
                
                $directDebit->followUp->update(['attachment' => $att]);
            }
        }

        return response()->json(['success' => true]);
    }
    /**
     * Show the update bank details form (no signatures, just bank info)
     * GET external/update-dd/{ref}
     */
    public function showUpdateBankDetailsForm(string $ref)
    {
        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();
        return view('ERP.dd.dd_update_bank_details', compact('directDebit', 'ref'));
    }

    /**
     * Update customer bank details
     * POST external/update-dd/{ref}
     */
    public function updateBankDetails(Request $request, string $ref)
    {
        $request->validate([
            'paying_bank_id'   => 'required|string|max:10',
            'paying_bank_name' => 'required|string|max:255',
            'account_title'    => 'required|string|max:255',
            'customer_id_no'   => 'required|string|max:100',
            'iban'             => 'required|string|max:34',
        ]);

        $directDebit = DirectDebit::where('ref', $ref)->firstOrFail();

        // Prevent update if status is 1 (Active/Accepted)
        if ($directDebit->status == 1) {
            abort(403, 'This Direct Debit is already active and cannot be updated.');
        }

        $directDebit->paying_bank_id = $request->input('paying_bank_id');
        $directDebit->paying_bank_name = $request->input('paying_bank_name');
        $directDebit->account_title = $request->input('account_title');

        // Remove spaces and dashes
        $directDebit->customer_id_no = preg_replace('/[\s\-]/', '', $request->input('customer_id_no'));
        
        $cleanIban = strtoupper(preg_replace('/[\s\-]/', '', $request->input('iban')));
        $directDebit->iban = $cleanIban;
        
        // Set account_type: 'C' (Current/Saving) for UAE IBANs, 'O' (Credit Card) for others
        $directDebit->account_type = str_starts_with($cleanIban, 'AE') ? 'C' : 'O';

        
        $directDebit->updated_by = "customer_updated_bank_details";
        $directDebit->status = 4;
        $directDebit->save();

        return response("
            <html>
                <head>
                    <title>Bank Details Updated</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            background-color: #f5f5f5;
                            padding: 50px;
                        }
                        .container {
                            background: #fff;
                            padding: 40px;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                            display: inline-block;
                        }
                        h2 { color: #4CAF50; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>✅ Bank Details Updated Successfully!</h2>
                        <p>Your bank details have been securely updated.</p>
                    </div>
                </body>
            </html>
        ", 200)->header('Content-Type', 'text/html');
    }
}




