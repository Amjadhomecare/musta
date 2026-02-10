<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaidsDB;
use App\Models\UpcomingInstallment;

class api extends Controller
{


    // URL = api/sms-p4-cus
    public function messgaeSmsP4(Request $request)
    {
        $validatedData = $request->validate([
            'start_date' => 'required|date'
        ]);
    
        $query = UpcomingInstallment::with(['contractRef' => function ($query) {
                            $query->where('contract_status', 1);
                        }, 'customerInfo'])
                        ->where('accrued_date', $validatedData['start_date'])
                        ->whereHas('contractRef', function ($query) {
                            $query->where('contract_status', 1);
                        })
                        ->get();

        $phoneNumbers = $query->map(function ($installment) {
            return $installment->customerInfo->phone ?? null;
        })->filter()->unique()->values();
    
        return response()->json([
            'success' => true,
            'phone_numbers' => $phoneNumbers,
        ], 200);
    }


    // url = api/p-all
    public function allApprovedMaids(Request $request)
    {
        try {
          
            $search = $request->input('search');
            $page = $request->input('page', 1); 
            $perPage = $request->input('per_page', 10); 
            $sortBy = $request->input('sort_by', 'id'); 
            $sortDirection = $request->input('sort_direction', 'DESC');
            $nationality = $request->input('nationality'); 
    
     
            $query = MaidsDB::where('maid_status','approved')
                              ->whereIn('maid_type', ['hc', 'P1']);
    
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($nationality) {
                $query->where('nationality', $nationality);
            }
    
        
            $query->orderBy($sortBy, $sortDirection);
    
            $data = $query->paginate($perPage, ['*'], 'page', $page);
    
            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    // url = api/p4
    public function p4Maids(Request $request)
    {
        try {
          
            $search = $request->input('search');
            $page = $request->input('page', 1); 
            $perPage = $request->input('per_page', 10); 
            $sortBy = $request->input('sort_by', 'id'); 
            $sortDirection = $request->input('sort_direction', 'DESC');
            $nationality = $request->input('nationality'); 
    
     
            $query = MaidsDB::where('maid_status','approved')
                              ->where('maid_type', 'hc');
    
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($nationality) {
                $query->where('nationality', $nationality);
            }
    
        
            $query->orderBy($sortBy, $sortDirection);
    
            $data = $query->paginate($perPage, ['*'], 'page', $page);
    
            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // url = api/p1
    public function p1Maids(Request $request)
    {
        try {
          
            $search = $request->input('search');
            $page = $request->input('page', 1); 
            $perPage = $request->input('per_page', 10); 
            $sortBy = $request->input('sort_by', 'id'); 
            $sortDirection = $request->input('sort_direction', 'DESC');
            $nationality = $request->input('nationality'); 
    
     
            $query = MaidsDB::where('maid_status','approved')
                            ->where('maid_type',null)
                            ->where('maid_booked',null);
                             

    
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($nationality) {
                $query->where('nationality', $nationality);
            }
    
        
            $query->orderBy($sortBy, $sortDirection);
    
            $data = $query->paginate($perPage, ['*'], 'page', $page);
    
            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // url = api/outside
    public function outsidCvs(Request $request)
    {
        try {
          
            $search = $request->input('search');
            $page = $request->input('page', 1); 
            $perPage = $request->input('per_page', 10); 
            $sortBy = $request->input('sort_by', 'id'); 
            $sortDirection = $request->input('sort_direction', 'DESC');
            $nationality = $request->input('nationality'); 
    
     
            $query = MaidsDB::where('maid_status','pending')
                            ->where('maid_type',null)
                            ->where('maid_booked',null)
                            ->where('visa_status','for market');
                             

    
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($nationality) {
                $query->where('nationality', $nationality);
            }
    
        
            $query->orderBy($sortBy, $sortDirection);
    
            $data = $query->paginate($perPage, ['*'], 'page', $page);
    
            return response()->json([
                'success' => true,
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'sort_by' => $sortBy,
                    'sort_direction' => $sortDirection,
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}