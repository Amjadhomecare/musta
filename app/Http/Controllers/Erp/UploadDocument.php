<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; 
use App\Models\Document;
use DataTables;


class UploadDocument extends Controller
{

    public function index(Request $request)
    {

        if (request()->ajax()) {
            $documents = Document::get();

            return DataTables::of($documents)
            
                ->make(true);
        }
        return view('ERP.owner.upload_document');
    }


    public function store(Request $request)
    {
        // ─── 1. Validate input ───────────────────────────────────────────
        $validated = $request->validate([
            'person'      => 'required|string|max:255',
            'file'        => 'required|file|max:20480',  
            'expire_date' => 'nullable|date',
            'note'        => 'nullable|string',
        ]);

        $disk   = 's3';      
        $folder = 'documents/' . now()->format('Y/m');         
        $path   = $request->file('file')->store($folder, $disk);


        $url = Storage::disk($disk)->url($path);


        // ─── 3. Persist metadata ─────────────────────────────────────────
        $document = Document::create([
            'person'      => $validated['person'],
            'expire_date' => $validated['expire_date'] ?? null,
            's3_url'      => $url,
            'note'        => $validated['note'] ?? null,
            'created_by'  => $request->user()?->name,   // or any identifier
        ]);

        // ─── 4. Return a response ───────────────────────────────────────
        return response()->json([
            'id'       => $document->id,
            'file_url' => $url,
            'message'  => 'Document uploaded successfully.',
        ], 201);
    }


    
// app/Http/Controllers/ERP/UploadDocument.php
public function destroy(Request $request)
{
    $request->validate(['id' => 'required|integer']);

    $document = Document::findOrFail($request->id);

    // delete file on S3 (same code as before)
    $disk = 's3';
    $key  = ltrim(parse_url($document->s3_url, PHP_URL_PATH), '/');
    Storage::disk($disk)->delete($key);

    // delete DB row
    $document->delete();

    return response()->json([
        'success' => true,
        'message' => 'Document deleted.',
    ]);
}

}

