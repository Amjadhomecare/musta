<?php

namespace App\Http\Controllers;

use App\Models\WiredTransfer;
use App\Models\Customer;
use App\Services\S3FileService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class WiredTranserCntl extends Controller
{
    protected $s3Service;

    public function __construct(S3FileService $s3Service)
    {
        $this->s3Service = $s3Service;
    }

    /**
     * List wired transfers with pagination, search, and filters
     */
    public function index(Request $request)
    {
        $page     = (int) $request->input('page', 1);
        $perPage  = (int) $request->input('per_page', $request->input('pageSize', 10));
        $search   = trim((string) $request->input('search', ''));
        $start    = $request->input('start_date');
        $end      = $request->input('end_date');
        $status   = $request->input('status');

        $query = WiredTransfer::with('customer')
            ->orderByDesc('id');

        // Date filters
        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        // Search by customer name, amount, note
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('amount_value', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }


        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Format items for frontend
        $items = collect($paginator->items())->map(function (WiredTransfer $transfer) {
            $customer = $transfer->customer;

            return [
                'id'            => $transfer->id,
                'customer_id'   => $transfer->customer_id,
                'customer_name' => $customer ? $customer->name : 'N/A',
                'url'           => $transfer->url,
                'status'        => $transfer->status,
                'status_label'  => $transfer->status_label,
                'amount_value'  => $transfer->amount_value,
                'note'          => $transfer->note,
                'created_at'    => optional($transfer->created_at)->toDateTimeString(),
                'updated_at'    => optional($transfer->updated_at)->toDateTimeString(),
                'created_by'    => $transfer->created_by,
                'updated_by'    => $transfer->updated_by,
            ];
        });

        return response()->json([
            'data'         => $items,
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
        ]);
    }

    /**
     * Store a new wired transfer with optional file upload
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'  => ['required', 'integer', 'exists:customers,id'],
            'amount_value' => ['required', 'numeric', 'min:0.01'],
            'status'       => ['nullable', 'integer', Rule::in([
                WiredTransfer::PENDING,
                WiredTransfer::COMPLETED,
                WiredTransfer::unknown,
                WiredTransfer::not_found
            ])],
            'note'         => ['nullable', 'string', 'max:1000'],
            'file'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:10240'], // 10MB max
        ]);

        $fileUrl = null;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileUrl = $this->s3Service->uploadToR2(
                $file,
                'wiredtransfers',
                false,            
                400,
                400
            );

            if (!$fileUrl) {
                return response()->json([
                    'message' => 'Failed to upload file to storage'
                ], 500);
            }
        }

        $transfer = WiredTransfer::create([
            'customer_id'  => $data['customer_id'],
            'url'          => $fileUrl,
            'status'       => $data['status'] ?? WiredTransfer::PENDING,
            'amount_value' => $data['amount_value'],
            'note'         => $data['note'] ?? null,
            'created_by'   => Auth::user()->name ?? 'system',
        ]);

        return response()->json([
            'message' => 'Wired transfer created successfully',
            'id'      => $transfer->id,
        ], 201);
    }

    /**
     * Update an existing wired transfer
     */
    public function update(Request $request, $id)
    {
        $transfer = WiredTransfer::findOrFail($id);

        // Only accountants can update status
        if ($request->has('status') && Auth::user()->group !== 'accounting') {
            return response()->json([
                'message' => 'Only accountant allowed to update status'
            ], 403);
        }

        $data = $request->validate([
            'customer_id'  => ['required', 'integer', 'exists:customers,id'],
            'amount_value' => ['required', 'numeric', 'min:0.01'],
            'status'       => ['nullable', 'integer', Rule::in([
                WiredTransfer::PENDING,
                WiredTransfer::COMPLETED,
                WiredTransfer::unknown,
                WiredTransfer::not_found
            ])],
            'note'         => ['nullable', 'string', 'max:1000'],
            'file'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:10240'],
        ]);

        // Handle new file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($transfer->url) {
                $this->s3Service->deletePreviousFileFromR2($transfer->url, 'r2');
            }

            // Upload new file
            $file = $request->file('file');
            $fileUrl = $this->s3Service->uploadToR2(
                $file,
                'wiredtransfers',
                false,
                400,
                400
            );

            if ($fileUrl) {
                $data['url'] = $fileUrl;
            }
        }

        $updateData = [
            'customer_id'  => $data['customer_id'],
            'url'          => $data['url'] ?? $transfer->url,
            'amount_value' => $data['amount_value'],
            'note'         => $data['note'] ?? null,
            'updated_by'   => Auth::user()->name ?? 'system',
        ];

        // Only update status if user is accountant
        if (Auth::user()->group === 'accounting' && isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        $transfer->update($updateData);

        return response()->json([
            'message' => 'Wired transfer updated successfully',
            'id'      => $transfer->id,
        ]);
    }

    /**
     * Delete a wired transfer and its associated file
     * Only accountants can delete
     */
    public function destroy($id)
    {
        // Only accountants can delete
        if (Auth::user()->group !== 'accounting') {
            return response()->json([
                'message' => 'Only accountant allowed to delete wired transfers'
            ], 403);
        }

        $transfer = WiredTransfer::findOrFail($id);

        // Delete file from S3 if exists
        if ($transfer->url) {
            $this->s3Service->deletePreviousFileFromR2($transfer->url, 'r2');
        }

        $transfer->delete();

        return response()->json([
            'message' => 'Wired transfer deleted successfully',
        ]);
    }
}

