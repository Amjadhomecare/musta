<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use DataTables;
use Illuminate\Support\Facades\DB;
use Auth;

class super_admin extends Controller
{
  
    public function view_add_user (){

        return view('ERP.admin.add_new_user');
    }

    public function add_new_user(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_group' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        // if (!in_array(Auth::user()->name, ['ahmad_suheel', 'Ahmad asawdeh'])) {
        //     return response()->json(['message' => 'Unauthorized Only Ahmad sohail can Add a new user'], 403);
        // }
        

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'group' => $request->user_group,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        // Return a JSON response
        return response()->json(['message' => 'User added successfully!'], 201);
    }
    

    public function getAllusers(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query();
    
            if ($request->has('min_date') && $request->min_date != '') {
                $query->whereDate('created_at', '>=', $request->min_date);
            }
    
            if ($request->has('max_date') && $request->max_date != '') {
                $query->whereDate('created_at', '<=', $request->max_date);
            }
    
            $data = $query->latest('id')->get();
    
            return DataTables::of($data)
                ->addIndexColumn()
                ->make(true);
        }
    }
    

    public function updateUser(Request $request)
    {
  

        $validatedData = $request->validate([
            'group' => 'string',
            'status' => 'required'
        
        ]);

        Log::info( $validatedData);
        try {
            $record = User::findOrFail($request->idInput);

            $record->update([
                'group'=>$validatedData['group'],
                'active'  => (int) $validatedData['status'],
                'updated_by'=>Auth::user()->name

            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Updated!'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error in file upload: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user'
            ], 500);
        }
    }//End method For editing
    


}
