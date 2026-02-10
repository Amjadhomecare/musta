<?php

namespace App\Http\Controllers;

use App\Models\DdFollowUp;
use App\Models\DirectDebit;
use App\Enum\DdFollowUps;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DdFollowUpController extends Controller
{
    use \App\Traits\SignatureProcessingTrait;

    /**
     * Admin update of signatures from Follow Up page
     * POST /dd-followup/update-signatures
     */
    public function updateSignatures(Request $request)
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
     * Manual follow-up list: 3+ attempts, customer did NOT reply, DD status != 1 (approved)
     * GET /dd-followup/manual
     */
    public function manualList(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = DdFollowUp::with(['directDebit.customer'])
            ->where('attempt_number', '>=', 3)
            ->where('follow_up_status', '!=', DdFollowUps::CustomerReplied->value) // Not replied
            ->whereHas('directDebit', function ($q) {
                $q->where('status', '!=', DirectDebit::STATUS_ACCEPTED); // DD not approved
            })
            ->orderBy('updated_at', 'desc');

        $this->applyFilters($query, $request);

        return $query->paginate($perPage);
    }

    /**
     * Customer replied list: customer replied (status=3), DD status != 1 (approved)
     * GET /dd-followup/replied
     */
    public function repliedList(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = DdFollowUp::with(['directDebit.customer'])
            ->where('follow_up_status', DdFollowUps::CustomerReplied->value)
            ->whereHas('directDebit', function ($q) {
                $q->where('status', '!=', DirectDebit::STATUS_ACCEPTED);
            })
            ->orderBy('updated_at', 'desc');

        $this->applyFilters($query, $request);

        return $query->paginate($perPage);
    }

    /**
     * Pending follow-up list: attempts < 3, customer did NOT reply
     * GET /dd-followup/pending
     */
    public function pendingList(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = DdFollowUp::with(['directDebit.customer'])
            ->where('attempt_number', '<', 3)
            ->where('follow_up_status', '!=', DdFollowUps::CustomerReplied->value)
            ->whereHas('directDebit', function ($q) {
                $q->where('status', '!=', DirectDebit::STATUS_ACCEPTED);
            })
            ->orderBy('updated_at', 'desc');

        $this->applyFilters($query, $request);

        return $query->paginate($perPage);
    }

    /**
     * Rejected DD list: DD status = 3 (Rejected), rejection reason does NOT start with RR01-
     * GET /dd-followup/rejected
     */
    public function RejectedDdNotSignure(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = DirectDebit::with(['customer'])
            ->where('status', DirectDebit::STATUS_REJECTED)
            ->where('rejected_reason', 'not like', 'RR01-%')
         ;
          
        if ($request->filled('search')) {
            $search = $request->input('search');
        
           $query->where(function ($q) use ($search) {
                $q->where('ref', 'like', "%{$search}%")
                  ->orWhere('account_title', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('created_by', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
           });
        }

        return $query->paginate($perPage);
    }

    /**
     * Apply common filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('follow_up_notes', 'like', "%{$search}%")
                  ->orWhereHas('directDebit', function ($dq) use ($search) {
                      $dq->where('ref', 'like', "%{$search}%")
                         ->orWhere('account_title', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('created_by', 'like', "%{$search}%")
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
    }
}

