<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bulkJvModel;
use App\Models\General_journal_voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DataTables;
use Illuminate\Support\Facades\Log;
use Auth;
use Carbon\Carbon;

class BulkJVController extends Controller
{

  public function pageUpload(){

    return view('ERP.accounting.bulk_jv');
  }

  public function upload(Request $request)
    {
        // Modern validation method
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');

        try {
            // Open the file and read its content
            $fileHandle = fopen($file, 'r');
            if (!$fileHandle) {
                throw new Exception('Unable to open the file.');
            }

            // Skip the header row
            $header = fgetcsv($fileHandle);

            $data = [];
            $rowCount = 0;

            // Loop through the CSV and prepare data for bulk insert
            while ($row = fgetcsv($fileHandle)) {
                $rowCount++;

                // Prepare row data
                $data[] = [
                    'date' => isset($row[0]) ? date('Y-m-d', strtotime($row[0])) : null,
                    'voucher_type' =>$row[1] ?? null,
                    'ref' => $row[2] ?? null,
                    'account' => $row[3] ?? null,
                    'note' => $row[4] ?? null,
                    'post_type' => $row[5] ?? null,
                    'amount' => is_numeric($row[6]) ? floatval($row[6]) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Insert in chunks of 1000 rows
                if ($rowCount % 1000 == 0) {
                    bulkJvModel::insert($data);
                    $data = [];
                }
            }

            // Insert any remaining data
            if (!empty($data)) {
                bulkJvModel::insert($data);
            }

            // Close the file
            fclose($fileHandle);

            return redirect()->back()->with('success', 'CSV file uploaded and data imported successfully.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    public function pageTableJvBulk(){

        return view('ERP.accounting.jv_bulk');
    }

    // table/jv/bulk
    public function tableJvBulk(){

        try {

            $query = bulkJvModel::query();
            $data =  DataTables::of($query)
                            ->addColumn('action', function($row) {
                                return '<button class="btn btn-primary btn-sm open-modal" data-ref="'.$row->ref.'">View</button>';
                            })

                            ->addColumn('delete', function($row) {
                                return '<button class="btn btn-danger btn-sm open-modal-delete" data-ref="'.$row->ref.'">Delete</button>';
                            })
                            ->rawColumns(['action','delete'])
                            ->make(true);
                              
            return $data;
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ajaxAllTypingInvoicesCntl:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getTran($ref)
    {
        try {
            $data = bulkJvModel::where('ref', $ref)->get();
    
            if ($data->isEmpty()) {
                return response()->json(['message' => 'No data found for the given reference.'], 404);
            }
    
            return response()->json(['response' => $data], 200);
    
        } catch (\Exception $e) {
            Log::error('Error in getTran:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    


    public function storeBulk(Request $request)
        {
            $validatedData = $request->validate([
                'tranDate' => 'required|date',
                'tranV' => 'required|string|in:Payment Voucher,Receipt Voucher,Journal Voucher,Opening Balance,Invoice Package1,Invoice Package4,Credit note,New arrival,invoice,Typing Invoice,debit_memo',                                                       
                'tranAccount' => 'required|array',
                'tranAccount.*' => 'required|string',
                'tranNote' => 'nullable|array',
                'tranNote.*' => 'nullable|string',
                'tranAmount' => 'required|array',
                'tranAmount.*' => 'required|numeric',
                'postType' => 'required|array',
                'postType.*' => 'required|string|in:debit,credit', 
                'tranMaid' => 'nullable|array',
                'tranMaid.*' => 'nullable|string',
            ]);

            $dates = $validatedData['tranDate'];
            $voucher = $validatedData['tranV'];
            $accounts = collect($validatedData['tranAccount']);
            $notes = collect($validatedData['tranNote']);
            $amounts = collect($validatedData['tranAmount']);
            $postType = collect($validatedData['postType']);
            $maids = collect($validatedData['tranMaid']);
            $randomCode = Str::random(5);

            $ref = $request->input('group');

            $totalDebit = $amounts->filter(function ($amount, $index) use ($postType) {
                return $postType[$index] === 'debit';
            })->sum();

            $totalCredit = $amounts->filter(function ($amount, $index) use ($postType) {
                return $postType[$index] === 'credit';
            })->sum();

            if ($totalDebit !== $totalCredit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Total debit must equal total credit.'
                ], 422);
            }

            DB::transaction(function () use ($dates, $voucher, $accounts, $notes, $amounts, $postType, $maids, $randomCode) {
                $voucherData = $accounts->map(function ($account, $index) use ($dates, $voucher, $amounts, $postType, $maids, $notes, $randomCode) {
                    return [
                        'date' => $dates,
                        'refCode' => "blk_".$randomCode,
                        'refNumber' => 0,
                        'voucher_type' => $voucher,
                        'type' => $postType[$index],
                        'account' => $account,
                        'amount' => $amounts[$index],
                        'maid_name' => $maids[$index] ?? 'No data',
                        'notes' => $notes[$index],
                        'created_by' => Auth::user()->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                General_journal_voucher::insert($voucherData->toArray());
            });

        
            bulkJvModel::where('ref', $ref)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Journal Vouchers saved successfully'
            ], 201);
        }


//delete/bulk
public function deleteBulkJv(Request $request){
    $ref = $request->input('ref_delete');
    Log::info([ $ref ]);
    bulkJvModel::where('ref', $ref)->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Deleted successfully'
    ], 201);

}

}
