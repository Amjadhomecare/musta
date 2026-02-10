<?php

namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\General_journal_voucher;
use App\Models\registerComplaint;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DataTables;
use Auth;
use App\Models\User;



class complainDepCntl extends Controller
{


      // url page/accounting-complain

      public function pageRegesterComplaint()
      {
          return view('ERP.make.register_complaint');
      }

    // url post/complaint
// url post/complaint
public function postNewComplaint(Request $request)
{
    $validated = $request->validate([
        'maidName'     => 'required|string|exists:maids_d_b_s,name',
        'contractRef'  => 'required|string|max:255',
        'customer'     => 'required|string|exists:customers,name',
        'reason'       => 'required|string|max:255',
        'status'       => 'required|in:pending,in progress,done',
        'type'         => 'required|in:general,ranaway,urgent',
        'assignedTo'   => 'nullable|string',
        'forwardTo'    => 'nullable|string',
    ]);

    // Resolve IDs from provided names
    $maidId = DB::table('maids_d_b_s')->where('name', $validated['maidName'])->value('id');
    $customerId = DB::table('customers')->where('name', $validated['customer'])->value('id');

    RegisterComplaint::create([
        'maid_id'      => $maidId,
        'customer_id'  => $customerId,
        'contract_ref' => $validated['contractRef'],
        'memo'         => $validated['reason'],
        'status'       => $validated['status'],
        'type'         => $validated['type'],
        'assigned_to'  => $validated['assignedTo'] ?? null,
        'forward_to'   => $validated['forwardTo'] ?? null,
        'created_by'   => auth()->user()->name,
        'updated_by'   => auth()->user()->name,
    ]);

    return response()->json(['message' => 'Complaint registered successfully'], 201);
}

       // url post/accounting-complain
public function storeAccountingComplain(Request $request)
{
    $validated = $request->validate([
        'maidName'     => 'nullable|string',
        'contractRef'  => 'nullable|string|max:160',
        'customer'     => 'nullable|string',
        'reason'       => 'nullable|string|max:160',
        'type'         => 'nullable|in:general,ranaway,urgent',
        'assignedTo'   => 'required|string',
        'forwardTo'    => 'nullable|string',
    ]);

    // Resolve optional IDs only if a name was sent
    $maidId = null;
    if (!empty($validated['maidName'])) {
        $maidId = DB::table('maids_d_b_s')->where('name', $validated['maidName'])->value('id');
    }

    $customerId = null;
    if (!empty($validated['customer'])) {
        $customerId = DB::table('customers')->where('name', $validated['customer'])->value('id');
    }

    RegisterComplaint::create([
        'maid_id'      => $maidId,
        'customer_id'  => $customerId,
        'contract_ref' => $validated['contractRef'] ?? null,
        'memo'         => $validated['reason'] ?? null,
        'type'         => $validated['type'] ?? 'general',
        'assigned_to'  => $validated['assignedTo'],
        'forward_to'   => $validated['forwardTo'] ?? null,
        'created_by'   => auth()->user()->name,
        'updated_by'   => auth()->user()->name,
    ]);

    return response()->json(['message' => 'Complaint registered successfully'], 201);
}
    

    // update/notify 
public function updateNotify(Request $request)
{
    $validated = $request->validate([
        'id'          => 'required|exists:register_complaints,id',
        'memo'        => 'nullable|string',
        'maidName'    => 'nullable|string',       // name comes in; we’ll translate to maid_id
        'contractRef' => 'nullable|string|max:120',
        'customer'    => 'nullable|string',       // name comes in; we’ll translate to customer_id
        'assignedTo'  => 'nullable|string',
        'actionTaken' => 'nullable|string',
        'status'      => 'nullable|string|in:pending,in progress,done',
    ]);

    $complaint = RegisterComplaint::findOrFail($validated['id']);

    // Merge action_taken JSON array
    $existing = $complaint->action_taken ?? [];
    if (is_string($existing)) {
        $existing = json_decode($existing, true) ?: [];
    }

    if (!empty($validated['actionTaken'])) {
        $existing[] = Carbon::today()->format('Y-m-d') . ' ' . auth()->user()->name . ': ' . $validated['actionTaken'];
    }

    // Resolve IDs if names provided
    $maidId = $complaint->maid_id;
    if (!empty($validated['maidName'])) {
        $maidId = DB::table('maids_d_b_s')->where('name', $validated['maidName'])->value('id') ?? $maidId;
    }

    $customerId = $complaint->customer_id;
    if (!empty($validated['customer'])) {
        $customerId = DB::table('customers')->where('name', $validated['customer'])->value('id') ?? $customerId;
    }

    $complaint->update([
        'assigned_to'  => $validated['assignedTo'] ?? $complaint->assigned_to,
        'memo'         => $validated['memo'] ?? $complaint->memo,
        'maid_id'      => $maidId,
        'contract_ref' => $validated['contractRef'] ?? $complaint->contract_ref,
        'customer_id'  => $customerId,
        'action_taken' => json_encode($existing),
        'updated_by'   => auth()->user()->name,
        'updated_at'   => Carbon::now(),
        'status'       => $validated['status'] ?? $complaint->status,
    ]);

    return response()->json(['message' => 'Notification updated successfully'], 201);
}



         // url /all/notified/complaints
     public function tableNotification(Request $request)
{
    if ($request->ajax()) {
        $complaints = registerComplaint::query()
            ->leftJoin('customers', 'customers.id', '=', 'register_complaints.customer_id')
            ->leftJoin('maids_d_b_s as maids', 'maids.id', '=', 'register_complaints.maid_id')
            ->select(
                'register_complaints.*',
                'customers.name as customer_name',
                'maids.name as maid_name'
            )
            ->orderBy('register_complaints.created_at', 'desc');

        return DataTables::of($complaints)
            ->addIndexColumn()
            ->addColumn('action_taken', function ($row) {
                $actions = json_decode($row->action_taken, true) ?? []; 
                $listItems = '';
                foreach ($actions as $action) {
                    $listItems .= '<li>' . htmlspecialchars($action) . '</li>';
                }
                return '<ul class="action-list">' . $listItems . '</ul>';
            })
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('maid_name', function($query, $keyword) {
                $query->where('maids.name', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-blue edit-notify-btn" data-id="' .$row->id. '">Action</button>
                        <button class="btn btn-danger delete-notify-btn" data-id="' .$row->id. '">Delete</button>';
            })
            ->rawColumns(['action_taken', 'action'])
            ->make(true);
    }
}


  public function tableNotificationByuser(Request $request)
{
    if ($request->ajax()) {

        $currentUser = Auth::user()->name;

        $complaints = RegisterComplaint::query()
            ->leftJoin('customers', 'customers.id', '=', 'register_complaints.customer_id')
            ->leftJoin('maids_d_b_s as maids', 'maids.id', '=', 'register_complaints.maid_id')
            ->where(function ($q) use ($currentUser) {
                $q->where('register_complaints.assigned_to', $currentUser)
                  ->orWhere('register_complaints.created_by', $currentUser);
            })
            ->select(
                'register_complaints.*',
                'customers.name as customer_name',
                'maids.name as maid_name'
            )
            ->orderBy('register_complaints.created_at', 'desc');

        return \DataTables::of($complaints)
            ->addIndexColumn()
            ->addColumn('action_taken', function ($row) {
                $actions = json_decode($row->action_taken, true) ?? [];
                $listItems = '';
                foreach ($actions as $action) {
                    $listItems .= '<li>' . htmlspecialchars($action) . '</li>';
                }
                return '<ul class="action-list">' . $listItems . '</ul>';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-blue edit-notify-btn" data-id="' . $row->id . '">Action</button>';
            })
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('maid_name', function($query, $keyword) {
                $query->where('maids.name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['action_taken', 'action'])
            ->make(true);
    }

    return view('ERP.make.user_notify');
}
       //this for select 2 server search
    // url = /searching-user
    public function searchingUser(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = 30;
            $query = User::query();

            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%');
                   
            }

            $users = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'total_count' => $users->total(),
                'items' => $users->map(function ($users) {
                    return [
                        
                        'id' => $users->name,
                        'text' => "{$users->name}"
                    ];
                })
            ]);
        }
     
    }



              // get/notify/{id}
    public function fetchNotify($id){

        $id = registerComplaint::findOrFail($id);

        return response()->json($id, 200);
    }
}
