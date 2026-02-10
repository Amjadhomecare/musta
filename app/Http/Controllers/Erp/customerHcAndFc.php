<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category4Model;
use Illuminate\Support\Facades\DB; 
use DataTables;

class customerHcAndFc extends Controller
{

    
    public function p4ContractsForAllBranchs(Request $request, $name)
    {
        if ($request->ajax()) {
          
            $findCustomerId = DB::connection('mysql2')->table('customers')
            ->where('name', $name)
            ->first();

            if (!$findCustomerId) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            $fromOtherBranch = DB::connection('mysql2')->table('category4_models')
            ->leftjoin('customers as cu', 'category4_models.customer_id', '=', 'cu.id')
            ->leftJoin('maids_d_b_s as m', 'category4_models.maid_id', '=', 'm.id')
            ->leftJoin('returned_maids', 'category4_models.Contract_ref', '=', 'returned_maids.contract')
            ->select(
                'category4_models.id',
                'category4_models.created_at',
                'category4_models.date',
                'category4_models.Contract_ref',
                'm.name as maid_name',
                'category4_models.contract_status',
                'returned_maids.contract',
                'returned_maids.returned_date',
                'returned_maids.reason',
                'returned_maids.created_by',
                DB::raw('DATEDIFF(returned_maids.returned_date, category4_models.date) as date_difference')
            )
            ->where('category4_models.customer_id',  $findCustomerId->id)
            ->get();
        
    
            return DataTables::of($fromOtherBranch)
                ->addIndexColumn()
                ->make(true);
        }
    
        return view('ERP.customers.all_branches', compact('name'));
    }
    
    

}
