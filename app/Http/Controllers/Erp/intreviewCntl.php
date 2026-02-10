<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Interview;
use DataTables;
use Auth;
use App\Models\MaidsDB;

class intreviewCntl extends Controller
{
    public function index()
    {
        return view('ERP.interview.index');
    }

    public function store(Request $request)
    {
        $validtedData =  $request->validate([
            'maid_name' => 'required|string',
            'customer_name' => 'nullable|string',
            'note' => 'nullable|string',   
            'room' => 'required',
        ]);

        $maidType = MaidsDB::where('name', $validtedData['maid_name'])->first();

        Interview::create( 
             [
                'maid_name' => $validtedData['maid_name'],
                'customer_name' => $validtedData['customer_name'],
                'note' => $validtedData['note'],
                'type' => $maidType->maid_type,
                'room' => $validtedData['room'],
                'status' => 0,
                'created_by' => Auth()->user()->name,
            ]
             
        );

        return response()->json(['message' => 'Interview created successfully']);
    }

    public function tableList(Request $request)
    {
        $query = Interview::query()->orderBy('created_at', 'desc');

        if ($request->has('min_date') && $request->min_date != '') {
            $query->whereDate('created_at', '>=', $request->min_date);
        }

        if ($request->has('max_date') && $request->max_date != '') {
            $query->whereDate('created_at', '<=', $request->max_date);
        }
    
        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                return '
                    <button class="btn btn-blue btn-sm edit-btn" data-id="' . $row->id . '">Edit</button>
              
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    

    public function getInterviewById($id)
        {
            $interview = Interview::findOrFail($id);
            return response()->json($interview);
        }
        public function update(Request $request)
        {
            $validatedData = $request->validate([
                'edit_interview_id' => 'required|exists:interviews,id', 
                'maid_name' => 'required|string',
                'customer_name' => 'nullable|string',
                'note' => 'nullable|string',
                'room' => 'required',
                'status' => 'required|integer'
            ]);
        
            $interview = Interview::findOrFail($validatedData['edit_interview_id']); 
        
            $interview->update([
                'maid_name' => $validatedData['maid_name'],
                'customer_name' => $validatedData['customer_name'],
                'note' => $validatedData['note'],
                'room' => $validatedData['room'],
                'status' => $validatedData['status'],
                'updated_by' => Auth::user()->name
            ]);
        
            return response()->json(['message' => 'Interview updated successfully']);
        }
        

  
}
