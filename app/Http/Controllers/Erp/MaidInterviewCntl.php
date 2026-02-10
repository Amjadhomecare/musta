<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\MaidsDB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaidInterviewCntl extends Controller
{

   
    // url /maidinterview/view
   public function view()
    {
        return view('ERP/interview/report');
    }
    
    // url /maidinterview/report
    public function index(Request $request)
    {
        $search = $request->input('search');
        $nationality = $request->input('nationality');
        $perPage = $request->input('per_page', 10);
    
        $query = DB::table('maids_d_b_s')
            ->select(
                'maids_d_b_s.*',
                DB::raw("(
                    SELECT COUNT(*) FROM interviews 
                    WHERE interviews.maid_name COLLATE utf8mb4_general_ci = maids_d_b_s.name COLLATE utf8mb4_general_ci 
                    AND interviews.status = 0
                ) as pending_count"),
                DB::raw("(
                    SELECT COUNT(*) FROM interviews 
                    WHERE interviews.maid_name COLLATE utf8mb4_general_ci = maids_d_b_s.name COLLATE utf8mb4_general_ci 
                    AND interviews.status = 1
                ) as success_count"),
                DB::raw("(
                    SELECT COUNT(*) FROM interviews 
                    WHERE interviews.maid_name COLLATE utf8mb4_general_ci = maids_d_b_s.name COLLATE utf8mb4_general_ci 
                    AND interviews.status = 2
                ) as maid_rejected_count"),
                DB::raw("(
                    SELECT COUNT(*) FROM interviews 
                    WHERE interviews.maid_name COLLATE utf8mb4_general_ci = maids_d_b_s.name COLLATE utf8mb4_general_ci 
                    AND interviews.status = 3
                ) as customer_rejected_count")
            )
            ->where('maid_status', 'approved');
    
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nationality', 'like', '%' . $search . '%');
            });
        }
    
        if (!empty($nationality)) {
            $query->where('nationality', $nationality);
        }
    
        $maids = $query->orderByDesc('created_at')->paginate($perPage);
    
        return response()->json([
            'data' => $maids->items(),       
            'total' => $maids->total(),       
            'per_page' => $maids->perPage(),  
            'current_page' => $maids->currentPage(),
            'last_page' => $maids->lastPage()
        ]);
        
    }
    
    

}  