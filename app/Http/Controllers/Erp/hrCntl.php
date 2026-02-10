<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\maid_doc_expiry;
use App\Models\MaidClearence;
use Carbon\Carbon;
use App\Models\General_journal_voucher;
use App\Models\All_account_ledger_DB;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\MaidsDB;
use App\Models\TicketMaid;
use App\Models\Noc;



class hrCntl extends Controller
{
public function tableMaidExpairP4(Request $request)
{
    if ($request->ajax()) {

        // ✅ Join ONCE (stable query) so DataTables global search works correctly
        $data = MaidsDB::query()
            ->leftJoin('maid_doc_expiries', 'maids_d_b_s.id', '=', 'maid_doc_expiries.maid_id')
            ->whereIn('maids_d_b_s.maid_status', ['approved', 'hired'])
            ->whereIn('maids_d_b_s.maid_type', ['HC', 'Direct hire'])
            ->select([
                'maids_d_b_s.*',
                'maid_doc_expiries.passport_expiry',
                'maid_doc_expiries.eid_expiry',
                'maid_doc_expiries.labor_card_expiry',
                'maid_doc_expiries.visa_expiry',
            ]);

        // Filter for passport expiring soon (30 days or less)
        if ($request->passport_expiring === 'true') {
            $data->whereNotNull('maid_doc_expiries.passport_expiry')
                 ->whereRaw('DATEDIFF(maid_doc_expiries.passport_expiry, CURDATE()) BETWEEN 0 AND 30')
                 ->orderBy('maid_doc_expiries.passport_expiry', 'ASC');
        }

        // Filter for EID expiring soon (30 days or less)
        if ($request->eid_expiring === 'true') {
            $data->whereNotNull('maid_doc_expiries.eid_expiry')
                 ->whereRaw('DATEDIFF(maid_doc_expiries.eid_expiry, CURDATE()) BETWEEN 0 AND 30')
                 ->orderBy('maid_doc_expiries.eid_expiry', 'ASC');
        }

        // Filter for null passport
        if ($request->null_passport === 'true') {
            $data->whereNull('maid_doc_expiries.passport_expiry');
        }

        // Filter for null EID
        if ($request->null_eid === 'true') {
            $data->whereNull('maid_doc_expiries.eid_expiry');
        }

        // Filter for visa expiring soon (30 days or less)
        if ($request->visa_expiring === 'true') {
            $data->whereNotNull('maid_doc_expiries.visa_expiry')
                 ->whereRaw('DATEDIFF(maid_doc_expiries.visa_expiry, CURDATE()) BETWEEN 0 AND 30')
                 ->orderBy('maid_doc_expiries.visa_expiry', 'ASC');
        }

        // Filter for null visa
        if ($request->null_visa === 'true') {
            $data->whereNull('maid_doc_expiries.visa_expiry');
        }

        // Filter for labor card expiring soon (30 days or less)
        if ($request->labor_card_expiring === 'true') {
            $data->whereNotNull('maid_doc_expiries.labor_card_expiry')
                 ->whereRaw('DATEDIFF(maid_doc_expiries.labor_card_expiry, CURDATE()) BETWEEN 0 AND 30')
                 ->orderBy('maid_doc_expiries.labor_card_expiry', 'ASC');
        }

        // Filter for null labor card
        if ($request->null_labor_card === 'true') {
            $data->whereNull('maid_doc_expiries.labor_card_expiry');
        }

        // Filter by maid type
        if (!empty($request->maid_type)) {
            $data->where('maids_d_b_s.maid_type', $request->maid_type);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('maid_name', function ($row) {
                $url = url('/maid-report/p4/' . $row->name);
                return '<a target="_blank" href="' . $url . '">' . e($row->name) . '</a>';
            })
            ->addColumn('passport_number', function ($row) {
                return e($row->passport_number ?? 'N/A');
            })
            ->addColumn('maid_type', function ($row) {
                $badgeClass = $row->maid_type === 'HC' ? 'badge-light-primary' : 'badge-light-info';
                return '<span class="badge ' . $badgeClass . '">' . e($row->maid_type) . '</span>';
            })
            ->addColumn('passport_expiry', function ($row) {
                if (!$row->passport_expiry) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $date = \Carbon\Carbon::parse($row->passport_expiry);
                $daysRemaining = \Carbon\Carbon::now()->diffInDays($date, false);

                $badgeClass = 'badge-light-success';
                if ($daysRemaining < 0) {
                    $badgeClass = 'badge-light-danger';
                    $status = 'Expired';
                } elseif ($daysRemaining <= 30) {
                    $badgeClass = 'badge-light-warning';
                    $status = $daysRemaining . ' days left';
                } else {
                    $status = $date->format('Y-m-d');
                }

                return $date->format('Y-m-d') . ' <span class="badge ' . $badgeClass . '">' . $status . '</span>';
            })
            ->addColumn('eid_expiry', function ($row) {
                if (!$row->eid_expiry) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $date = \Carbon\Carbon::parse($row->eid_expiry);
                $daysRemaining = \Carbon\Carbon::now()->diffInDays($date, false);

                $badgeClass = 'badge-light-success';
                if ($daysRemaining < 0) {
                    $badgeClass = 'badge-light-danger';
                    $status = 'Expired';
                } elseif ($daysRemaining <= 30) {
                    $badgeClass = 'badge-light-warning';
                    $status = $daysRemaining . ' days left';
                } else {
                    $status = $date->format('Y-m-d');
                }

                return $date->format('Y-m-d') . ' <span class="badge ' . $badgeClass . '">' . $status . '</span>';
            })
            ->addColumn('labor_card_expiry', function ($row) {
                if (!$row->labor_card_expiry) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $date = \Carbon\Carbon::parse($row->labor_card_expiry);
                $daysRemaining = \Carbon\Carbon::now()->diffInDays($date, false);

                $badgeClass = 'badge-light-success';
                if ($daysRemaining < 0) {
                    $badgeClass = 'badge-light-danger';
                    $status = 'Expired';
                } elseif ($daysRemaining <= 30) {
                    $badgeClass = 'badge-light-warning';
                    $status = $daysRemaining . ' days left';
                } else {
                    $status = $date->format('Y-m-d');
                }

                return $date->format('Y-m-d') . ' <span class="badge ' . $badgeClass . '">' . $status . '</span>';
            })
            ->addColumn('visa_expiry', function ($row) {
                if (!$row->visa_expiry) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $date = \Carbon\Carbon::parse($row->visa_expiry);
                $daysRemaining = \Carbon\Carbon::now()->diffInDays($date, false);

                $badgeClass = 'badge-light-success';
                if ($daysRemaining < 0) {
                    $badgeClass = 'badge-light-danger';
                    $status = 'Expired';
                } elseif ($daysRemaining <= 30) {
                    $badgeClass = 'badge-light-warning';
                    $status = $daysRemaining . ' days left';
                } else {
                    $status = $date->format('Y-m-d');
                }

                return $date->format('Y-m-d') . ' <span class="badge ' . $badgeClass . '">' . $status . '</span>';
            })
            ->addColumn('maid_state', function ($row) {
                return ucfirst($row->maid_status);
            })
            ->addColumn('actions', function ($row) {
                return '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $row->id . '">Edit</button>';
            })
            ->rawColumns(['maid_name', 'maid_type', 'passport_expiry', 'eid_expiry', 'visa_expiry', 'labor_card_expiry', 'actions'])
            ->make(true);
    }

    return view('ERP.hr.p4_maids');
}


    // URL /store-leave-salary
public function storeLevaeSalary(Request $request, $id = null)
{
  
    $request->merge(['id' => $id]); 

    $validated = $request->validate([
        'id'              => 'nullable|integer|exists:maid_clearences,id',
        'maid_name'       => 'required|string|max:255',
        'last_entry_date' => 'nullable|date',
        'travel_date'     => 'nullable|date',
        'dedcution'       => 'nullable|numeric',
        'ticket'          => 'nullable|numeric',
        'allowance'       => 'nullable|numeric',
        'note'            => 'nullable|string|max:500',
        'reason'          => 'required|in:renewal,cancel',
        'for'             => 'required|in:maid,staff',
        'remaining_amount'         => 'nullable|numeric',
        'allowance'       => 'required|numeric',
        'salary_dh'      => 'nullable|numeric',
        'salary_details'  => 'nullable|string|max:255',
        'end_of_service_dh' => 'nullable|numeric',
        'end_of_service_details' => 'nullable|string|max:255',
        'other_dh'       => 'nullable|numeric',
        'other_details'  => 'nullable|string|max:255',
    ]);

    $validated['last_entry_date'] = $validated['last_entry_date']
        ? \Carbon\Carbon::parse($validated['last_entry_date'])->format('Y-m-d')
        : null;

    $validated['travel_date'] = $validated['travel_date']
        ? \Carbon\Carbon::parse($validated['travel_date'])->format('Y-m-d')
        : null;

    $record = MaidClearence::updateOrCreate(
        ['id' => $validated['id'] ?? 0],
        [
            'maid_name'       => $validated['maid_name'],
            'last_entry_date' => $validated['last_entry_date'],
            'travel_date'     => $validated['travel_date'],
            'dedcution'       => $validated['dedcution'],
            'ticket'          => $validated['ticket'],
            'allowance'       => $validated['allowance'],
            'note'            => $validated['note'],
            'reason'          => $validated['reason'],
            'type'            => $validated['for'],
            'allowance'         => $validated['allowance'],
            'remaining_amount'    => $validated['remaining_amount'],
            'created_by'      => auth()->user()->name,
            'updated_by'      => auth()->user()->name,
            'salary_dh'      => $validated['salary_dh'],
            'salary_details'  => $validated['salary_details'] ?? '14 days',
            'end_of_service_dh' => $validated['end_of_service_dh'] ?? 0,
            'end_of_service_details' => $validated['end_of_service_details'] ?? '21 days for each YEAR',
            'other_dh'       => $validated['other_dh'] ?? 0,
            'other_details'  => $validated['other_details'] ?? '-',
        ]
    );

    return response()->json([
        'status'  => 'success',
        'message' => $validated['id'] ? 'Leave salary updated successfully.' : 'Leave salary created successfully.',
        'data'    => $record,
        'code'    => $validated['id'] ? 200 : 201,
    ]);
}


// store-staff-leave-salary
// '/staff-leave-salaries/{id}'

public function leaveSalaryStaffForm( Request $request,$id = null) {
    $request->merge(['id' => $id]); 

    Log::info('Request data for leaveSalaryStaffForm: ', $request->all());

    $validated = $request->validate([
        'id'              => 'nullable|integer',
        'maid_name'       => 'required|string|max:255',
        'last_entry_date' => 'nullable|date',
        'travel_date'     => 'nullable|date',
        'dedcution'       => 'nullable|numeric',
        'ticket'          => 'nullable|numeric',
        'allowance'       => 'nullable|numeric',
        'note'            => 'nullable|string|max:500',
        'reason'          => 'required|in:renewal,cancel', 
        'remaining_amount' => 'nullable|numeric',
        'allowance'       => 'required|numeric',
        'pp'             => 'nullable|string|max:50',
        'pp_expire'      => 'nullable|date',
        'emirate_id'     => 'nullable|string|max:50',
        'job_title'      => 'nullable|string|max:100',
        'basic_salary'   => 'nullable|numeric',
        'salary_dh'      => 'nullable|numeric'
    ]);

    $validated['last_entry_date'] = $validated['last_entry_date']
        ? \Carbon\Carbon::parse($validated['last_entry_date'])->format('Y-m-d')
        : null;

    $validated['travel_date'] = $validated['travel_date']
        ? \Carbon\Carbon::parse($validated['travel_date'])->format('Y-m-d')
        : null;

    $record = MaidClearence::updateOrCreate(
        ['id' => $validated['id'] ?? 0],
        [
            'maid_name'       => $validated['maid_name'],
            'last_entry_date' => $validated['last_entry_date'],
            'travel_date'     => $validated['travel_date'],
            'dedcution'       => $validated['dedcution'],
            'ticket'          => $validated['ticket'],
            'allowance'       => $validated['allowance'],
            'note'            => $validated['note'],
            'reason'          => $validated['reason'],
            'type'            => 'staff',
            'allowance'         => $validated['allowance'],
            'remaining_amount'    => $validated['remaining_amount'],
            'pp'             => $validated['pp'],
            'pp_expire'      => $validated['pp_expire'] ? \Carbon\Carbon::parse($validated['pp_expire'])->format('Y-m-d') : null,
            'emirate_id'     => $validated['emirate_id'],
            'job_title'      => $validated['job_title'],
            'basic_salary'   => $validated['basic_salary'],
            'salary_dh'      => $validated['salary_dh'],
            'created_by'      => auth()->user()->name,
            'updated_by'      => auth()->user()->name,

        ]
    );

    return response()->json([
        'status'  => 'success',
        'message' => $validated['id'] ? 'Leave salary updated successfully.' : 'Leave salary created successfully.',
        'data'    => $record,
        'code'    => $validated['id'] ? 200 : 201,
    ]);

}


    // URL /leave-salaries  
public function leaveSalaryMaidList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $query = MaidClearence::query()
                        ->where('type','maid')     
                        ->orderBy('created_at', 'desc');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where('maid_name', 'like', "%{$search}%");
    }

    if ($request->filled('reason')) {
    $query->where('reason', $request->input('reason'));
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


// url /leave-salaries-staff
public function leaveSalaryStaffList(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);

    $query = MaidClearence::query()
                     ->where('type', 'staff')        
                      ->orderBy('created_at', 'desc');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where('maid_name', 'like', "%{$search}%");
    }

    if ($request->filled('reason')) {
    $query->where('reason', $request->input('reason'));
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


// URL /maid-clearence/{id}
public function getMAidClearenceById($id)
{
    $m = MaidClearence::with('maid')->findOrFail($id);

    $maidName = $m->maid_name;

    
    $c = MaidClearence::where('maid_name', $maidName)->count();


    return view('ERP.hr.maid_clearence', compact('m', 'c'));
}

// URL /maid-clearance/{id}/update-items
public function updateClearanceItems(Request $request, $id)
{
    $validated = $request->validate([
        'clearance_items' => 'required|array',
        'clearance_items.*.label' => 'required|string',
        'clearance_items.*.details' => 'required|string',
        'clearance_items.*.amount' => 'required|numeric',
        'allowance' => 'nullable|numeric',
        'ticket' => 'nullable|numeric',
        'dedcution' => 'nullable|numeric',
    ]);

    $clearance = MaidClearence::findOrFail($id);
    
    // Get existing clearance_items to preserve signatures
    $existingItems = $clearance->clearance_items ?? [];
    
    // Preserve signatures if they exist
    $signatures = isset($existingItems['signatures']) ? $existingItems['signatures'] : null;
    
    // Update clearance items (numeric keys only)
    $clearance->clearance_items = $validated['clearance_items'];
    
    // Re-add signatures if they existed
    if ($signatures) {
        $items = $clearance->clearance_items;
        $items['signatures'] = $signatures;
        $clearance->clearance_items = $items;
    }
    
    // Update other editable fields if provided
    if (isset($validated['allowance'])) {
        $clearance->allowance = $validated['allowance'];
    }
    if (isset($validated['ticket'])) {
        $clearance->ticket = $validated['ticket'];
    }
    if (isset($validated['dedcution'])) {
        $clearance->dedcution = $validated['dedcution'];
    }
    
    // Also update individual columns for backward compatibility
    foreach ($validated['clearance_items'] as $item) {
        if ($item['label'] === 'Salaries') {
            $clearance->salary_dh = $item['amount'];
            $clearance->salary_details = $item['details'];
        } elseif ($item['label'] === 'End of service') {
            $clearance->end_of_service_dh = $item['amount'];
            $clearance->end_of_service_details = $item['details'];
        } elseif ($item['label'] === 'Other') {
            $clearance->other_dh = $item['amount'];
            $clearance->other_details = $item['details'];
        }
    }
    
    $clearance->updated_by = Auth::user()->name;
    $clearance->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Clearance items updated successfully'
    ]);
}

// URL /maid-clearance/{id}/save-signatures
public function saveClearanceSignatures(Request $request, $id)
{
    $validated = $request->validate([
        'hr_signature' => 'nullable|string',
        'employee_signature' => 'nullable|string',
        'gm_signature' => 'nullable|string',
    ]);

    $clearance = MaidClearence::findOrFail($id);
    
    // Get existing clearance_items to preserve existing data
    $existingItems = $clearance->clearance_items ?? [];
    
    // Get existing signatures or create new array
    $signatures = isset($existingItems['signatures']) ? $existingItems['signatures'] : [];
    
    // Decode base64 and upload to S3
    $decode = fn($b64) => base64_decode(explode(',', $b64)[1]);
    $disk = 'beta';
    
    // Only update signatures that were provided
    if (isset($validated['hr_signature'])) {
        $hrFile = 'signatures/clearance/hr_' . uniqid() . '.png';
        Storage::disk($disk)->put($hrFile, $decode($validated['hr_signature']));
        $signatures['hr_manager'] = Storage::disk($disk)->url($hrFile);
    }
    
    if (isset($validated['employee_signature'])) {
        $empFile = 'signatures/clearance/emp_' . uniqid() . '.png';
        Storage::disk($disk)->put($empFile, $decode($validated['employee_signature']));
        $signatures['employee'] = Storage::disk($disk)->url($empFile);
    }
    
    if (isset($validated['gm_signature'])) {
        $gmFile = 'signatures/clearance/gm_' . uniqid() . '.png';
        Storage::disk($disk)->put($gmFile, $decode($validated['gm_signature']));
        $signatures['general_manager'] = Storage::disk($disk)->url($gmFile);
    }
    
    // Update timestamps
    $signatures['signed_at'] = now()->toDateTimeString();
    $signatures['signed_by'] = Auth::user()->name;
    
    // Preserve existing clearance items (numeric keys only)
    $items = [];
    foreach ($existingItems as $key => $value) {
        if (is_numeric($key)) {
            $items[$key] = $value;
        }
    }
    
    // Add signatures
    $items['signatures'] = $signatures;
    
    $clearance->clearance_items = $items;
    $clearance->updated_by = Auth::user()->name;
    $clearance->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Signatures saved successfully',
        'signatures' => $signatures
    ]);
}





// URL /staff-clearence/{id}
public function getStaffClearenceById($id)
{
    $m = MaidClearence::findOrFail($id);

    $maidName = $m->maid_name;

    // Count how many times this maid_name appears
    $c = MaidClearence::where('maid_name', $maidName)->count();

    return view('ERP.hr.staff_clearence', compact('m', 'c'));
}




  // URL /maid-visit-visa
public function getMaidVistVisa(Request $request)
{
    // ── Pagination input ─────────────────────────────────────────────
    $perPage = $request->integer('per_page', 10);
    $page    = $request->integer('page', 1);

 
    $latestCategoryOnes = DB::table('category_ones as co1')
        ->select([
            'co1.maid_id',
            DB::raw('MAX(co1.created_at) as latest_created_at'),
        ])
        ->groupBy('co1.maid_id');

    // ── Main query with computed column + join to latest category_ones ─
    $query = DB::table('maids_d_b_s as m')
        ->leftJoinSub($latestCategoryOnes, 'co_latest', function ($join) {
            $join->on('co_latest.maid_id', '=', 'm.id');
        })
        ->leftJoin('category_ones as co', function ($join) {
            $join->on('co.maid_id', '=', 'co_latest.maid_id')
                 ->on('co.created_at', '=', 'co_latest.latest_created_at');
        })
        ->select([
            'm.*',
            DB::raw('DATEDIFF(m.visit_visa_expired, CURDATE()) AS days_remaining'),
            DB::raw('co.created_by as co_created_by'),
            DB::raw('co.created_at as co_created_at'),
            DB::raw('m.nationality')
        ])
        ->where('m.maid_type', 'p1')
        ->where('m.visa_status', '!=', 'c')
        ->orderBy('days_remaining', 'asc');

    // ── Search filter ────────────────────────────────────────────────
    if ($request->filled('search')) {
        $search = $request->string('search')->toString();
        $query->where(function ($q) use ($search) {
            $q->where('m.name', 'like', "%{$search}%");
        });
    }

        // ── Search nationality ────────────────────────────────────────────────
    if ($request->filled('search_nationality')) {
        $search = $request->string('search_nationality')->toString();
        $query->where(function ($q) use ($search) {
            $q->where('m.nationality', 'like', "%{$search}%");
        });
    }


    // Remove rows where visit_visa_expired is NULL
    if ($request->boolean('remove_null')) {
        $query->whereNotNull('m.visit_visa_expired');
    }

    // ── Paginate & respond ───────────────────────────────────────────
    $paginator = $query->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data'         => $paginator->items(),    // includes days_remaining + co_created_by/co_created_at
        'total'        => $paginator->total(),
        'per_page'     => $paginator->perPage(),
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'server_date'  => \Carbon\Carbon::now()->toDateString(),
    ]);
}



    // URL /bulk-update-maid-visit-visa
    public function bulkUpdateMaidVisitVisa(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('visa_status', 'c'); 

        if (empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

    
        MaidsDB::whereIn('id', $ids)->update(['visa_status' => $status]);

        return response()->json(['message' => 'Maid visit visa status updated successfully']);
    }



    // URL /store-or-update-ticket
    public function storeOrUpdateTicket(Request $request , $id = null)
        {
            $validated = $request->validate([
                'id'             => 'nullable|exists:ticket_maids,id',
                'maid_name'      => 'required|string|max:255',
                'travel_date'    => 'required|date',
                'destination'    => 'required|string|max:255',
                'return_date'    => 'nullable|date',
                'status'         => 'required|in:pending,approved,rejected',
                'ticket_number'  => 'nullable|string|max:255',
                'ticket_type'    => 'nullable|string|max:255',
                'ticket_price'   => 'nullable|string|max:255',
                'note'           => 'nullable|string|max:1000',
            ]);

            $ticket = TicketMaid::updateOrCreate(
                ['id' => $request->id],
                $validated + [
                    'created_by' => Auth::user()->name,
                    'updated_by' => Auth::user()->name,
                ]
               
            );

            return response()->json([
                'message' => $request->id ? 'Ticket updated successfully' : 'Ticket created successfully',
                'data'    => $ticket
            ]);
        }


        // URL /ticket-maid-list
        public function getTicketMaidList(Request $request)
        {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = DB::table('ticket_maids')
                ->leftJoin('maids_d_b_s', function ($join) {
                        $join->on(DB::raw("ticket_maids.maid_name COLLATE utf8mb4_general_ci"), '=', DB::raw("maids_d_b_s.name COLLATE utf8mb4_general_ci"));
                    })
                ->select('ticket_maids.*', 'maids_d_b_s.nationality', 'maids_d_b_s.agency' , 
                         'maids_d_b_s.maid_status', 'maids_d_b_s.maid_type' , 'maids_d_b_s.passport_number') 
                ->orderBy('ticket_maids.created_at', 'desc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('ticket_maids.maid_name', 'like', "%{$search}%");
            }

            if ($request->filled('status')) {
                $query->where('ticket_maids.status', $request->input('status'));
            }

            if ($request->filled('nationality')) {
                $query->where('maids_d_b_s.nationality', $request->input('nationality'));
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
 
        // URL /get-maids-salary-p1

        public function getMaidsSalaryP1 (Request $request)
        {
        
        $perPage = $request->integer('per_page', 10);
        $page    = $request->integer('page', 1);
        $ledgerId = All_account_ledger_DB::where('ledger', 'P1_MAID_SALARY')->value('id');
     
        // ── Query with computed column ───────────────────────────────────
        $query = General_journal_voucher::with('maidRelation:id,name')
                    ->where('ledger_id', $ledgerId)
                    ->where('type', 'credit')
                   ->orderBy('created_at', 'desc')
                  
                   ; 

        // ── Search filter ────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('maidRelation', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // ── Paginate & respond ───────────────────────────────────────────
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'         => $paginator->items(),    
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
     
        ]);
             
        }

   

 // URL: /get-maids-salary-p1-by-name/{name}

public function getMaidsSalaryP1ByName($name)
{
    // 1) Resolve maid safely
    $maid = MaidsDB::where('name', $name)->first();
    if (!$maid) {
        abort(404, 'Maid not found.');
    }

    // 2) Resolve P1_MAID_SALARY ledger id
    $accountID = All_account_ledger_DB::where('ledger', 'P1_MAID_SALARY')->value('id');
    if (!$accountID) {
        abort(404, 'Ledger P1_MAID_SALARY not found.');
    }


    $salaryRecords = General_journal_voucher::with('accountLedger:id,ledger')
        ->where('maid_id', $maid->id)
        ->where('ledger_id', $accountID)
        ->get();

    $refCodes = $salaryRecords->pluck('refCode')->filter()->unique()->values();
    $relatedVouchers = General_journal_voucher::with('accountLedger:id,ledger')
        ->whereIn('refCode', $refCodes)
        ->get()
        ->groupBy('refCode'); // Collection keyed by refCode

    // 5) Split credits / debits and totals
    $credits = $salaryRecords->where('type', 'credit');
    $debits  = $salaryRecords->where('type', 'debit');

    $totalCredit = (float) $credits->sum('amount');
    $totalDebit  = (float) $debits->sum('amount');
    $netAmount   = $totalCredit - $totalDebit;

    return view('ERP.hr.salary_p1_t_account', compact(
        'name',
        'credits',
        'debits',
        'totalCredit',
        'totalDebit',
        'netAmount',
        'relatedVouchers',
        'maid'
    ));
}


// URL: /noc-list   
        public function NocList(Request $request)
        {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Noc::query()
                ->orderBy('created_at', 'desc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('maid_name', 'like', "%{$search}%");
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

// URL: /store-noc  
public function storeNoc(Request $request, $id = null)
{
    $validated = $request->validate([
        'id' => 'nullable|exists:nocs,id',
        'maid_name' => 'required|string|max:255',
        'customer_name' => 'required|string|max:255',
        'note' => 'nullable|string|max:500',
        't_date' => 'nullable|date',
        'r_date' => 'nullable|date',
        'country' => 'nullable|string',
        'cus_phone' => 'nullable|string|max:20',
        'cus_id' => 'nullable|string',
        'extra_data' => 'array'
    ]);

    $extraData = [
        'cus_phone' => $validated['cus_phone'] ?? null,
        'cus_id'    => $validated['cus_id'] ?? null,
    ] + ($validated['extra_data'] ?? []);

    unset($validated['cus_phone'], $validated['cus_id'], $validated['extra_data']);

    $validated['t_date'] = !empty($validated['t_date'])
        ? \Carbon\Carbon::parse($validated['t_date'])->format('Y-m-d') : null;
    $validated['r_date'] = !empty($validated['r_date'])
        ? \Carbon\Carbon::parse($validated['r_date'])->format('Y-m-d') : null;

    $noc = Noc::updateOrCreate(
        ['id' => $validated['id'] ?? 0],
        $validated + [
            'created_by' => Auth::user()->name,
            'updated_by' => Auth::user()->name,
            'extra_data' => $extraData,
        ]
    );

    return response()->json([
        'message' => $validated['id']
            ? 'NOC updated successfully.'
            : 'NOC created successfully.',
        'data' => $noc
    ]);
}

    
// URL: /get-noc-by-id/{id}
    
    public function getNocById($id)
    {
        $noc = Noc::with(['customer' , 'maid'])
                         ->findOrFail($id);

        return view('ERP.hr.noc_form', compact('noc'));
    }

    // URL: /get-maid-doc-expiry/{id}
    public function getMaidDocExpiry($id)
    {
        $maid = MaidsDB::with('maidDocExpiry')->findOrFail($id);
        
        return response()->json([
            'maid_id' => $maid->id,
            'maid_name' => $maid->name,
            'doc_expiry' => $maid->maidDocExpiry
        ]);
    }

    // URL: /update-maid-doc-expiry
    public function updateMaidDocExpiry(Request $request)
    {
        $validated = $request->validate([
            'maid_id' => 'required|exists:maids_d_b_s,id',
            'passport_expiry' => 'nullable|date',
            'eid_expiry' => 'nullable|date',
        ]);

        $docExpiry = maid_doc_expiry::updateOrCreate(
            ['maid_id' => $validated['maid_id']],
            [
                'passport_expiry' => $validated['passport_expiry'],
                'eid_expiry' => $validated['eid_expiry'],
                'labor_card_expiry' => $request->input('labor_card_expiry'),
                'created_by' => Auth::user()->name,
                'updated_by' => Auth::user()->name,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Document expiry updated successfully.',
            'data' => $docExpiry
        ]);
    }
}

