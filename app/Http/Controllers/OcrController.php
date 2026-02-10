<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AzureDocService;

class OcrController extends Controller
{
    /** @var AzureDocService */
    private AzureDocService $svc;

    public function __construct(AzureDocService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * POST /ocr/azure
     * Body: multipart/form-data with field "image"
     *
     * Response JSON:
     * {
     *   "rawText": "... full OCR text ...",
     *   "data": {
     *       "id": "784-1978-0579185-8",
     *       "name": "Jehad Mahmoud Alzoubi",
     *       "nationality": "Syrian Arab Republic"
     *   }
     * }
     */
    public function analyze(Request $request)
    {
        // 1️⃣  Validate input
        $request->validate([
            'image' => 'required|image|max:6144',   // 6 MB limit
        ]);

        try {
            // 2️⃣  Call Azure
            $json = $this->svc->analyze(
                $request->file('image')->getPathname()
            );

            // 3️⃣  Flatten the entire OCR text (all pages & lines)
            $rawText = collect($json['analyzeResult']['content'] ?? [])
                ->implode("\n");

            // 4️⃣  Extract specific fields from the raw text
            $data = $this->extractFields($rawText);

            return response()->json([
                'rawText' => $rawText,
                'data'    => $data,
            ]);

        } catch (\Throwable $e) {
            // 5️⃣  Log the full stack-trace and bubble a concise error to JS
            Log::error($e);

            return response()->json([
                'message' => 'Azure OCR failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract ID number, name, and nationality from flat OCR text.
     * Arabic words on the *same line* (e.g. "الاسم" / "الجنس") are removed.
     */
    private function extractFields(string $text): array
    {
        // Remove Arabic block chars, then collapse whitespace
        $clean = static fn (string $s): string => trim(
            preg_replace(
                ['/[\p{Arabic}]+/u', '/\s+/'],
                [' ',               ' '],
                $s
            )
        );

        return [
            'id' => preg_match('/784-\d{4}-\d{7}-\d/', $text, $m) ? $m[0] : '',

                'name' => preg_match(
                '/Name\s*[:：]?\s*([A-Z][A-Za-z\'\- ]{2,80}?)(?=\s+Date of Birth|[\r\n]|$)/i',
                $text,
                $m
            ) ? $clean($m[1]) : '',


            'nationality' => preg_match(
                '/Nationality\s*[:：]?\s*([\p{L}\s\']+)/iu',
                $text,
                $m
            ) ? $clean($m[1]) : '',
        ];
    }
}
