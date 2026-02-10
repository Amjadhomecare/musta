<?php

namespace App\Http\Controllers\pro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\S3FileService;
use App\Models\ApplyVisa;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ApplyVisaStatusLog;
use App\Models\MaidsDB;






class ApplyVisaController extends Controller
{

      // ============================
    // URL: GET /apply-visas
    // ============================
    public function getApplyVisasList(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        $query = DB::table('apply_visas as av')
            ->leftJoin('maids_d_b_s as m', 'av.maid_id', '=', 'm.id')
            ->leftJoin('erp_users as user', function ($join) {
                $join->on(
                    DB::raw("CONVERT(av.created_by USING utf8mb4) COLLATE utf8mb4_0900_ai_ci"),
                    '=',
                    DB::raw("CONVERT(user.name USING utf8mb4) COLLATE utf8mb4_0900_ai_ci")
                );
            })
            ->select(
                'av.*',
                'm.name as maid_name',
                'm.uae_id_maid',
                'm.passport_number',
                'm.nationality',
                'user.name',
                'user.group',
                DB::raw('JSON_LENGTH(av.document) as document_count')
            )
            ->orderBy('av.created_at', 'desc');

        // search by maid / fields / created_by
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('m.name', 'like', "%{$search}%")
                  ->orWhere('m.uae_id_maid', 'like', "%{$search}%")
                  ->orWhere('m.passport_number', 'like', "%{$search}%")
                  ->orWhere('av.note', 'like', "%{$search}%")
                  ->orWhere('av.created_by', 'like', "%{$search}%");
            });
        }

      if ($request->boolean('user')) {
            $query->where('user.group', 'online');
        }

        if ($request->filled('status')) {
            $query->where('av.status', $request->input('status'));
        }
        if ($request->filled('service')) {
            $query->where('av.service', $request->input('service'));
        }
        if ($request->filled('managment_approval')) {
            $query->where('av.managment_approval', $request->input('managment_approval'));
        }

       if ($request->boolean('done')) {
            $query->whereNotIn('av.status', [10, 11, 12, 13, 14]);
        }


        if ($request->filled('date_from')) {
            $query->whereDate('av.date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('av.date', '<=', $request->input('date_to'));
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
     * GET /apply-visas/{id}
     */
    public function getApplyVisaById($id)
    {

        $applyVisa = ApplyVisa::with('maid')->findOrFail($id);

        return response()->json([
            'data' => $applyVisa
        ]);
    }

    
    
   public function storeApplyVisa(Request $request)
{
    $validated = $request->validate([
        'date'               => ['nullable','date'],
        'date_expiration'    => ['nullable','date'],
        'maid_id'            => ['required', Rule::exists('maids_d_b_s','id')],
        'service'            => ['nullable','integer'],
        'status'             => ['nullable','integer'],
        'managment_approval' => ['nullable','integer'],
        'note'               => ['nullable','string','max:1000'],
        'documents'          => ['nullable','array'],
        'documents.*'        => ['file','max:40480'],
        'new_comment'        => ['nullable','string','max:2000'],
    ]);

    $av = new ApplyVisa();
    $av->date               = $validated['date'] ?? null;
    $av->date_expiration    = $validated['date_expiration'] ?? null;
    $av->maid_id            = $validated['maid_id'];
    $av->service            = $validated['service'] ?? null;
    $av->status             = $validated['status'] ?? 0;
    $av->managment_approval = $validated['managment_approval'] ?? 0;
    $av->note               = $validated['note'] ?? null;

    // documents
    $urls = [];
    if ($request->hasFile('documents')) {
        $storage = new S3FileService();
        foreach ($request->file('documents') as $file) {
            $url = $storage->uploadToR2($file, 'apply_visas', resize: false);
            if ($url) $urls[] = $url;
        }
    }
    $av->document = $urls;

    // comments
    $comments = [];
    if ($request->filled('new_comment')) {
        $comments[] = [
            'by'   => Auth::user()->name ?? 'system',
            'text' => trim($request->input('new_comment')),
            'at'   => now()->utc()->toIso8601String(),
        ];
    }
    $av->comments = $comments;

    $av->created_by = Auth::user()->name ?? 'system';
    $av->updated_by = Auth::user()->name ?? 'system';
    $av->save();

    return response()->json([
        'message' => 'Visa application created successfully',
        'data'    => $av
    ]);
}

// URL POST /apply-visas/update
public function updateApplyVisa(Request $request)
{
    $validated = $request->validate([
        'id'                   => ['required', Rule::exists('apply_visas','id')],
        'date'                 => ['nullable','date'],
        'date_expiration'      => ['nullable','date'],
        'maid_id'              => ['required', Rule::exists('maids_d_b_s','id')],
        'service'              => ['nullable','integer'],
        'status'               => ['nullable','integer'],
        'managment_approval'   => ['nullable','integer'],
        'note'                 => ['nullable','string','max:1000'],
        'documents'            => ['nullable','array'],
        'documents.*'          => ['file','max:40480'],
        'keep_document_urls'   => ['nullable','array'],
        'keep_document_urls.*' => ['url'],
        'new_comment'          => ['nullable','string','max:2000'],
        'user'                 => ['nullable','string','max:255'],
    ]);

    return DB::transaction(function () use ($request, $validated) {
        $av = ApplyVisa::findOrFail($validated['id']);

        // capture original status BEFORE changes
        $originalStatus = (int) $av->status;

        // basic fields
        $av->date               = $validated['date'] ?? null;
        $av->date_expiration    = $validated['date_expiration'] ?? $av->date_expiration;
        $av->maid_id            = $validated['maid_id'];
        $av->service            = $validated['service'] ?? $av->service;
        $av->status             = array_key_exists('status', $validated) ? (int)$validated['status'] : $av->status;
        $av->managment_approval = $validated['managment_approval'] ?? $av->managment_approval;
        $av->note               = $validated['note'] ?? $av->note;

        // === documents handling ===
        $existing = is_array($av->document) ? $av->document : (array) ($av->document ?? []);
        $keep = $request->filled('keep_document_urls')
            ? array_values(array_intersect($existing, (array) $request->keep_document_urls))
            : $existing;

        $toDelete = array_values(array_diff($existing, $keep));
        if (!empty($toDelete)) {
            $storage = new \App\Services\S3FileService();
            foreach ($toDelete as $url) {
                $storage->deletePreviousFileFromR2($url, 'r2');
            }
        }

        if ($request->hasFile('documents')) {
            $storage = $storage ?? new \App\Services\S3FileService();
            foreach ($request->file('documents') as $file) {
                $url = $storage->uploadToR2($file, 'apply_visas', resize: false);
                if ($url) {
                    $keep[] = $url;
                }
            }
        }
        $av->document = array_values(array_unique($keep));


        $comments = is_array($av->comments) ? $av->comments : (array) ($av->comments ?? []);
        if ($request->filled('new_comment')) {
            $comments[] = [
                'by'   => Auth::user()->name ?? 'system',
                'text' => trim($request->input('new_comment')),
                'at'   => now()->utc()->toIso8601String(),
            ];
        }
        $av->comments = $comments;

        $av->updated_by = Auth::user()->name ?? $validated['user'];
        $av->save();

        if ($originalStatus !== (int) $av->status) {
            ApplyVisaStatusLog::create([
                'apply_visa_id' => $av->id,
                'status'        => (int) $av->status,  
                // 'maid_id'       => (int) $av->maid_id,             
                'created_by'    => Auth::user()->name ?? $validated['user'],    
                'comment'       => $request->input('new_comment'),  
            ]);
        }

        return response()->json([
            'message' => 'Visa application updated successfully',
            'data'    => $av,
        ]);
    });
}


public function updateStatusAndComment(Request $request)
{
    $validated = $request->validate([
        'id'          => ['required', Rule::exists('apply_visas','id')],
        'status'      => ['required','integer'],
        'new_comment' => ['nullable','string','max:2000'],
        'user'        => ['nullable','string','max:255'],
    ]);

    return DB::transaction(function () use ($request, $validated) {
        $av = ApplyVisa::findOrFail($validated['id']);
        $originalStatus = (int) $av->status;

        // update status
        $av->status = (int)$validated['status'];

        // append new comment if provided
        $comments = is_array($av->comments) ? $av->comments : (array) ($av->comments ?? []);
        if ($request->filled('new_comment')) {
            $comments[] = [
                'by'   => Auth::user()->name ??  $validated['user'],
                'text' => trim($request->input('new_comment')),
                'at'   => now()->utc()->toIso8601String(),
            ];
        }
        $av->comments = $comments;
        $av->updated_by = Auth::user()->name ?? $validated['user'];
        $av->save();

        // log status change if changed
        if ($originalStatus !== (int) $av->status) {
            ApplyVisaStatusLog::create([
                'apply_visa_id' => $av->id,
                'status'        => (int) $av->status,
                'created_by'    => Auth::user()->name ?? $validated['user'],
                'comment'       => $request->input('new_comment'),
            ]);
        }

        return response()->json([
            'message' => 'Status/comment updated successfully',
            'data'    => $av,
        ]);
    });
}

 public function deleteDocument($id, $index)
  {
    $visa = ApplyVisa::findOrFail($id);
    $docs = $visa->document ?? [];

    if (is_string($docs)) {
        $docs = json_decode($docs, true) ?: [];
    }

    if (isset($docs[$index])) {
        $url = $docs[$index];

        // Optionally: delete from R2 using S3FileService
        $storage = new S3FileService();
        $storage->deletePreviousFileFromR2($url, 'r2');

        unset($docs[$index]);
        $visa->document = array_values($docs);
        $visa->save();
    }

    return back()->with('success', 'Document deleted successfully.');
}

   
}
