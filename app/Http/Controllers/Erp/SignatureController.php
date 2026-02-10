<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Auth;



class SignatureController extends Controller
{
    /**
     * Handle POST /erp/signatures
     *
     * Expects JSON with two base-64 PNGs plus optional meta-data.
     */
public function store(Request $request)
{

    
    $data = $request->validate([
        'customer_signature' => 'required|string',
        'staff_signature'    => 'required|string',
        'customer_name'      => 'nullable|string|max:255',
        'maid_name'          => 'nullable|string|max:255',
        'checked'            => 'sometimes|boolean',
        'note'              => 'nullable|string|max:1000',
    ]);

    $disk = 'beta';

    // Safer base64 decode
    $decode = fn($b64) => base64_decode(explode(',', $b64)[1]);

    // Save customer signature
    $customerFolder = 'signatures/customer/';
    $custFile = $customerFolder . uniqid('cust_') . '.png';
    Storage::disk($disk)->put($custFile, $decode($data['customer_signature']));
    $customerUrl = Storage::disk($disk)->url($custFile);

    // Save staff signature
    $staffFolder = 'signatures/staff/';
    $staffFile = $staffFolder . uniqid('staff_') . '.png';
    Storage::disk($disk)->put($staffFile, $decode($data['staff_signature']));
    $staffUrl = Storage::disk($disk)->url($staffFile);

    // Save to DB
    Signature::create([
        'customer_signature_url' => $customerUrl,
        'staff_signature_url'    => $staffUrl,
        'customer_name'          => $data['customer_name'] ?? null,
        'maid_name'              => $data['maid_name']     ?? null,
        'checked'                => $data['checked']       ?? false,
        'note'                   => $data['note']          ?? null,
        'created_by'             => Auth::user()->name,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Signatures saved successfully'
    ]);
}


    // URL /signatures/data-table

    public function DataTableSign( Request $request)
    {
        $query= Signature::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($query) use ($search) {
                $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('maid_name', 'like', "%{$search}%");
            });
        }


            $perPage = (int) $request->query('perPage', 10);
            return $query->orderByDesc('id')->paginate($perPage);


    }

}