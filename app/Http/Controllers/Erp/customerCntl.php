<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\General_journal_voucher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\All_account_ledger_DB;
use App\Models\Customer;
use App\Models\categoryOne;
use App\Models\maidReturnCat1;
use App\Models\Category4Model;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use DataTables;
use Auth;
use App\Models\customerAttach;
use Illuminate\Support\Facades\DB;

use App\Services\S3FileService;

class customerCntl extends Controller
{

    // url /all-customers
    public function listOfcustomers(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $context = $request->input('context');
            $page = $request->input('page', 1);
            $perPage = 30;

            $query = Customer::query();

            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            }

            $customers = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'total_count' => $customers->total(),
                'items' => $customers->map(function ($customer) use ($context) {
                    if ($context === 'account-selection') {
                        return [
                            'id' => $customer->name,
                            'text' => $customer->name,
                            'erp_id' => $customer->id,
                            'ledger_id' => $customer->ledger_id,
                        ];
                    } else {
                        return [
                            'erp_id' => $customer->id,
                            'id' => $customer->name,
                            'text' => "{$customer->name} / Phone: {$customer->phone} / Closing balance: " . General_journal_voucher::calculateCustomerBalanceByLedgerId($customer->ledger_id)
                        ];
                    }
                })
            ]);
        }
        else
            return view('ERP.customers.allCustomers');
    }//End Method



    public function getAllCustomers(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::orderBy('created_at', 'desc')->latest();
            return DataTables::of($data)
                    ->addIndexColumn()
                    ->make(true);
        }
    } // end method

    public function saveCustomer(Request $request)
    {
       try{
        DB::beginTransaction();

        $userGroup = Auth::user()->group;

        
        $request->merge([
            'name' => preg_replace('/\s+/', ' ', trim($request->input('name'))),
        ]);

    
            $validatedData = $request->validate([
                'name' => 'required|unique:customers|max:255',
                'phone' => [
                    'required',
                    'unique:customers',
                    'regex:/^05\d{8}$/',
                    'max:255'
                ],
                
                'idImg' => ($userGroup === 'sales') ? 'required|image|max:7000' : 'nullable|image|max:7000',
                'secondaryPhone' => [                 
                    'max:255',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value === $request->phone) {
                            $fail('The secondary phone number must be different from the primary phone number.');
                        }
                    }
                ],
            ]);
    
       
            $customerData = [
                'name' => strtoupper($request->name),
                'related' => $request->related,
                'note' => $request->note,
                'phone' => $request->phone,
                'secondaryPhone' => $request->secondaryPhone,
                'idType' => $request->idType,
                'idNumber' => $request->idNumber,
                'nationality' => $request->nationality,
                'cusomerType' => $request->cusomerType,
                'email' => $request->email,
                'address' => $request->address,
                'created_by' => Auth::user()->name,
            ];
    
            if ($request->file('idImg')) {
                $customerData['idImg'] = $this->handleFileUploadS3($request->file('idImg'), 'customer_ID_', 'ahlia_customer_attach/');
            }

            $customer = Customer::create($customerData);
    
      
         
          $ledger = $this->createCustomerLedger($customer, $request);

            $customer->update([
                'ledger_id' => $ledger->id,
            ]);

    
    
            DB::commit();
    
            return response()->json(['status' => 'success', 'message' => 'Customer saved successfully', 'data' => $customer], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422); 
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (FileException $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'File upload error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error occurred: ' . $e->getMessage()], 500);
        }

    }


    protected function handleFileUploadS3($file, $prefix, $path)
            {
                $disk = 'beta';
                $fileName = uniqid($prefix) . '_' . $file->getClientOriginalName();
                $fullPath = Storage::disk($disk)->put($path . $fileName, file_get_contents($file));

                if (!$fullPath) {
                    Log::error("File upload to S3 failed.");
                    throw new \Exception('File upload to S3 failed');
                }

                return Storage::disk($disk)->url($path . $fileName);
            }
  
        protected function createCustomerLedger(Customer $customer, Request $request)
        {
            return All_account_ledger_DB::create([
                'ledger'     => strtoupper($request->name),
                'class'      => 'Account Receivable',
                'group'      => 'customer',
                'sub_class'  => 'Account Receivable',
                'note'       => $request->phone,
                'created_by' => Auth::user()->name,
            ]);
        }
           

        public function update(Request $request)
{
    try {
        $customer = Customer::findOrFail($request->edit_cus_id);

        $rules = [
            'edit_cus_name' => ['required','max:255'],
            'edit_cus_phone' => [
                'required','regex:/^05\d{8}$/','max:255',
                Rule::unique('customers','phone')->ignore($customer->id),
            ],
            'edit_cus_sPhone' => [
                'nullable','regex:/^05\d{8}$/','max:255',
                Rule::unique('customers','secondaryPhone')->ignore($customer->id),
                function ($attribute, $value, $fail) use ($request) {
                    if ($value !== null && $value === $request->edit_cus_phone) {
                        $fail(__('The secondary phone number must be different from the primary phone number.'));
                    }
                }
            ],
            'edit_cus_ID_type'       => 'nullable|max:255',
            'edit_cus_ID_num'        => ['nullable','max:255', Rule::unique('customers','idNumber')->ignore($customer->id)],
            'edit_cus_nationality'   => 'nullable|max:255',
            'edit_cus_ID_img'        => 'nullable|image|max:7000',
            'edit_cus_pass_img'      => 'nullable|image|max:7000',
        ];

        $validated = $request->validate($rules);

        // Prepare values
        $newName = strtoupper(trim($validated['edit_cus_name']));
        $oldName = $customer->getOriginal('name'); // keep for logs/audits if needed

        // Assign updates on the model (in-memory)
        $customer->name           = $newName;
        $customer->related        = $request->edit_cus_related;
        $customer->note           = $request->edit_cus_note;
        $customer->phone          = $validated['edit_cus_phone'];
        $customer->secondaryPhone = $validated['edit_cus_sPhone'] ?? null;
        $customer->idType         = $validated['edit_cus_ID_type'] ?? null;
        $customer->idNumber       = $validated['edit_cus_ID_num'] ?? null;
        $customer->nationality    = $validated['edit_cus_nationality'] ?? null;
        $customer->cusomerType    = $request->edit_cus_type;
        $customer->email          = $request->edit_cus_email;
        $customer->address        = $request->edit_cus_address;

        // Files
        $disk   = 'beta';
        $bucket = 'nextmetaerp';
        $this->handleImageReplacementS3($request, $customer, 'edit_cus_ID_img',   'idImg',       $disk, $bucket);
        $this->handleImageReplacementS3($request, $customer, 'edit_cus_pass_img', 'passportImg', $disk, $bucket);

        DB::transaction(function () use ($customer, $newName/*, $oldName*/) {
            // 1) Save the customer
            $customer->save();

            // 2) If name changed and there's a linked ledger, sync related records
            if ($customer->wasChanged('name') && $customer->ledger_id) {
                // 2a) Update the ledger name
                All_account_ledger_DB::where('id', $customer->ledger_id)
                    ->update(['ledger' => $newName]);
            }
        });

            // Check if blacklist SMS should be sent
            if ($request->has('send_blacklist_sms')) {

                try {
                    $appUrl = config('app.url');
                    $approvalLink = "{$appUrl}/blacklist/approve/{$customer->id}";
                    $smsText = "Blacklist approval request for customer: {$customer->name}. Click to approve: {$approvalLink}";

                    // Numbers to send SMS to
                    $numbers = ['504567241'];

                    // Send SMS to each number
                    $smsController = new \App\Http\Controllers\SmsRelayController();

                    foreach ($numbers as $number) {
                        $smsRequest = new Request([
                            'text' => $smsText,
                            'number' => $number
                        ]);

                        $smsController->send($smsRequest);
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to send blacklist SMS: " . $e->getMessage());
                }
            }


        return response()->json([
            'success' => true,
            'message' => __('Customer Account successfully updated')
        ]);

    } catch (ValidationException $ex) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $ex->errors(),
        ], 422);
    } catch (ModelNotFoundException $ex) {
        return response()->json([
            'success' => false,
            'message' => __('Customer not found'),
        ], 404);
    } catch (\Exception $ex) {
        return response()->json([
            'success' => false,
            'message' => __('Something went wrong: ') . $ex->getMessage(),
        ], 500);
    }
}


    
    
    /**
     * Handle image replacement by deleting the old image and uploading the new one.
     */
    private function handleImageReplacementS3($request, $customer, $inputName, $fieldName, $disk, $bucket)
    {
        if ($file = $request->file($inputName)) {
            $existingImageUrl = $customer->{$fieldName};
    
            if ($existingImageUrl) {
            
                $key = basename(parse_url($existingImageUrl, PHP_URL_PATH));
    

                if (Storage::disk($disk)->exists("ahlia_customer_attach/" . $key)) {
             
                    Storage::disk($disk)->delete("ahlia_customer_attach/" . $key);
                }
            }
    
            // Upload the new file to S3
            $fileName = uniqid($fieldName . '_') . '_' . $file->getClientOriginalName();
            $path = Storage::disk($disk)->put("ahlia_customer_attach/" . $fileName, file_get_contents($file));
    
            if (!$path) {
                Log::error("File upload to S3 failed.");
                throw new \Exception('File upload to S3 failed');
            }
    
            // Update the customer field with the new image URL
            $customer->{$fieldName} = Storage::disk($disk)->url("ahlia_customer_attach/" . $fileName);
        }
    }
    

// url /customer/upload-attachment
public function uploadCustomerAttachment(Request $request, S3FileService $s3FileService)
{
    try {
        $validated = $request->validate([
            'file'          => 'required|file|mimes:jpg,jpeg,png,mp4,avi,wmv,mov,ogg,qt,pdf,doc,docx|max:5000',
            'customer_name' => 'required|exists:customers,name',
            'note'          => 'required',
        ]);

        $file    = $validated['file'];
        $note    = $validated['note'];
        $disk    = 'beta';
        $folder  = 'ahlia_customer_attach/';
        $fileName = uniqid('maid_') . '_' . $file->getClientOriginalName();

        // Upload to S3
        $fileUrl = $s3FileService->uploadS3File($disk, $folder, $fileName, file_get_contents($file));
        if (!$fileUrl) {
            \Log::error('File upload to S3 failed.');
            return response()->json(['message' => 'File upload to S3 failed'], 500);
        }

    
        $customer = Customer::where('name', $validated['customer_name'])->firstOrFail();

        customerAttach::create([
            'customer_id' => $customer->id,  
            'note'        => $note,
            'file_name'   => $fileName,
            'file_type'   => $file->getClientMimeType(),
            'file_path'   => $fileUrl,
            'created_by'  => \Auth::user()->name,
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => 'File uploaded and saved successfully!',
            'file_url' => $fileUrl,
        ], 201);

    } catch (\Throwable $ex) {
        \Log::error('Error uploading file: ' . $ex->getMessage());
        return response()->json(['status' => 'error', 'message' => 'An unexpected error occurred.'], 500);
    }
}



public function pageCustomerAttachment(){


    return view ('ERP.customers.customer_attach');
}






}