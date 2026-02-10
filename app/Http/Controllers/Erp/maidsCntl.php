<?php
namespace App\Http\Controllers\Erp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\MaidsDB;
use App\Models\ReturnedMaid;
use App\Models\Category4Model;
use App\Models\categoryOne;
use App\Models\maidReturnCat1;
use App\Models\All_account_ledger_DB;
use App\Models\General_journal_voucher;
use App\Models\maid_doc_expiry;
use App\Models\MaidAttachment;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\maidsFilter;
use App\Models\logsdt;
use App\Services\S3FileService;
use Illuminate\Validation\Rule;


class maidsCntl extends Controller

{

              //  /index/maid
    public function maidPage(){

           $agentNames = All_account_ledger_DB::where('group', 'maid agent')->get();

            return view('ERP.maids.maidsCv', compact('agentNames'));
    }
    // /maid/{id}
    public function getMaidById($id)
    {
    
        $maid = MaidsDB::findOrFail($id);

        return response()->json($maid);
    }



      // url = /all-maids
      public function getAllMaids(Request $request)
      {
          try {
              $query = MaidsDB::select(
                  'id',
                  'created_at', 
                  'img',         
                  'name',        
                  'maid_status',
                  'visa_status', 
                  'salary',  
                  'passport_number',    
                  'nationality', 
                  'payment',
                  'agency',      
                  'maid_type',   
                  'maid_booked', 
                  'passport_exp_date',
                  'visit_visa_expired',
                  'created_by',  
                  'updated_by',
                  'updated_at', 

              )
              ->with('maidsFilter') 
              ->orderBy('created_at', 'desc'); 
      
              if ($request->has('min_date') && $request->min_date != '') {
                  $query->whereDate('created_at', '>=', $request->min_date);
              }
      
              if ($request->has('max_date') && $request->max_date != '') {
                  $query->whereDate('created_at', '<=', $request->max_date);
              }

              if ($request->has('includeNullBook') && $request->includeNullBook == 'true') {
                $query->whereNull('maid_booked');
            }
    
      
              return DataTables::of($query)
              ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown">
                                <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <button data-maid="' . $row->name . '"
                                            data-nationality="' . $row->nationality . '"
                                            data-agency="' . $row->agency . '"
                                            data-salary="' . $row->salary . '"
                                            data-id="' . $row->id . '"
                                            data-img="' . $row->img . '"
                                            data-video="' . $row->video_link . '"
                                            data-type="' . $row->maid_type . '"
                                            data-maid_booked="' . $row->maid_booked . '"
                                            data-bs-toggle="modal" data-bs-target="#edit-cv-modal"
                                            class="dropdown-item rounded-pill waves-effect waves-light edit-modal-btn">
                                        <i class="fa fa-cog" aria-hidden="true"></i> Edit
                                    </button>
                                    <a class="dropdown-item rounded-pill waves-effect waves-light" target="_blank" href="' . env('maid_cv_url') . '/cv/' . $row->id . '">CV</a>
';
            

                if (!$row->maid_booked) {
                    $actions .= '<button rounded-pill waves-effect waves-light fa-sharp data-maid="' . $row->name . '" data-id="' . $row->id . '" 
                                    class="dropdown-item btn book-modal-btn" data-bs-toggle="modal" data-bs-target="#booked-cv-modal">Book</button>';
                }
   
                $actions .= '<button rounded-pill waves-effect waves-light fa-sharp data-maid-video="' . $row->name . '" data-video="' . $row->video_link . '" 
                                data-id-video="' . $row->id . '" class="dropdown-item btn video-btn" data-bs-toggle="modal" data-bs-target="#video-link-cv-modal">Update video URL</button>
                             <button rounded-pill waves-effect waves-light fa-sharp data-maid="' . $row->name . '" data-id="' . $row->id . '" 
                                class="dropdown-item btn expire-modal-btn" data-bs-toggle="modal" data-bs-target="#expiry-cv-modal">P4 Doc expiry</button>
                             <button rounded-pill waves-effect waves-light fa-sharp data-maid="' . $row->name . '" data-id="' . $row->id . '" 
                                class="dropdown-item btn filter-modal-btn">Edit Filters</button>
                         </div>
                     </div>';
            
                return $actions;
            })
            
                  ->addColumn('filters', function ($row) {
                
                      if ($row->maidsFilter) {
                          return '
                              <ul>
                                  <li>Has Dog: ' . ($row->maidsFilter->has_dog ? 'Yes' : 'No') . '</li>
                                  <li>Has Cat: ' . ($row->maidsFilter->has_cat ? 'Yes' : 'No') . '</li>
                                  <li>Working Days Off: ' . ($row->maidsFilter->working_days_off ? 'Yes' : 'No') . '</li>
                                  <li>Private Room: ' . ($row->maidsFilter->private_room ? 'Yes' : 'No') . '</li>
                                  <li>Elderly Care: ' . ($row->maidsFilter->elderly_care ? 'Yes' : 'No') . '</li>
                                  <li>Special Needs Care: ' . ($row->maidsFilter->special_needs_care ? 'Yes' : 'No') . '</li>
                                  <li>Knows Syrian, Lebanese Cuisine: ' . ($row->maidsFilter->knows_syrian_lebanese ? 'Yes' : 'No') . '</li>
                                  <li>Can Assist and Cook: ' . ($row->maidsFilter->can_assist_and_cook ? 'Yes' : 'No') . '</li>
                                  <li>Knows Gulf Food: ' . ($row->maidsFilter->knows_gulf_food ? 'Yes' : 'No') . '</li>
                                  <li>International Cooking: ' . ($row->maidsFilter->international_cooking ? 'Yes' : 'No') . '</li>
                              </ul>';
                      }
                      return 'No filters set';
                  })
                  ->editColumn('created_at', function ($row) {
                      return \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');
                  })
                 ->addColumn('img', function ($row) {
                        $imageUrl = $row->img ? asset($row->img) : asset('keen/assets/media/svg/avatars/blank.svg');
                        return '<a href="'.$imageUrl.'" target="_blank">
                                    <img src="'.$imageUrl.'" alt="Image" style="width: 100px; height: auto;" 
                                        onerror="this.src=\''.asset('keen/assets/media/svg/avatars/blank.svg').'\';">
                                </a>';
                    })

                            ->editColumn('name', function ($row) {
                      return '<a href="/maid-report/'.$row->name.'" target="_blank">'.$row->name.'</a>';
                  })
                  ->editColumn('maid_status', function ($row) {
                      if (auth()->user()->group === 'accounting') {
                          return '<button class="btn status-btn"
                                          data-maid-status="'.$row->name.'"
                                          data-id-status="'.$row->id.'"
                                          data-curr-status="'.$row->maid_status.'"
                                          data-bs-toggle="modal" data-bs-target="#changing-status-cv-modal">
                                      '.$row->maid_status.'
                                  </button>';
                      } else {
                          return $row->maid_status; 
                      }
                  })
                  ->editColumn('salary', function ($row) {
                      return 'AED'.number_format($row->salary, 2); 
                  })
                  ->editColumn('agency', function ($row) {
                      return '<a href="/agency-profile/'.$row->agency.'" target="_blank">'.$row->agency.'</a>';
                  })
                  ->editColumn('maid_type', function ($row) {
                      return ucfirst($row->maid_type);  
                  })
                  ->rawColumns(['action', 'img', 'name', 'maid_status', 'agency', 'filters'])  
             
                  ->make(true);
      
          } catch (\Exception $e) {
              Log::error('Error in getAllMaids:', ['error' => $e->getMessage()]);
              return response()->json(['error' => 'Internal Server Error'], 500);
          }
      }
      
// url: '/maid-filter/
public function showFilter($maidId)
{
    $maid = MaidsDB::with('maidsFilter')->findOrFail($maidId);

  
    $maidFilter = $maid->maidsFilter ?? [
        'has_dog' => false,
        'has_cat' => false,
        'babysitting' => null,
        'private_room' => false,
        'elderly_care' => false,
        'special_needs_care' => false,
        'knows_syrian_lebanese' => false,
        'can_assist_and_cook' => false,
        'knows_gulf_food' => false,
        'international_cooking' => false,

        'baby_0_to_6' => false,
        'baby_6_to_12' => false,
        'baby_1_to_2' => false,
        'baby_2_to_6' => false,
        'live_out' => false,
    ];

    return response()->json([
        'maid' => $maid,
        'maidFilter' => $maidFilter,
    ]);
}


// url update filter  /filter-update
public function updateOrCreateFilter(Request $request)
{

    $validatedData = $request->validate([
        'maid_id' => 'required|exists:maids_d_b_s,id',
        'has_dog' => 'boolean',
        'has_cat' => 'boolean',
        'working_days_off' => 'boolean',
        'babysitting' => 'nullable|string',
        'private_room' => 'boolean',
        'elderly_care' => 'boolean',
        'special_needs_care' => 'boolean',
        'knows_syrian_lebanese' => 'boolean',
        'can_assist_and_cook' => 'boolean',
        'knows_gulf_food' => 'boolean',
        'international_cooking' => 'boolean',

        'baby_0_to_6' => 'boolean',
        'baby_6_to_12' => 'boolean',
        'baby_1_to_2' => 'boolean',
        'baby_2_to_6' => 'boolean',
        'live_out' => 'boolean',

        'created_by' => 'nullable|string',
        'updated_by' => 'nullable|string',
    ]);


    $maidFilter = MaidsFilter::updateOrCreate(
        ['maid_id' => $validatedData['maid_id']], 
        $validatedData 
    );

    return response()->json([
        'message' => $maidFilter->wasRecentlyCreated ? 'Maid filter created successfully.' : 'Maid filter updated successfully.',
        'maidFilter' => $maidFilter,
    ]);
}

      
    //this for select 2
    // url = /all/maids
    public function viewMaidsCVCntl(Request $request){
        if ($request->ajax()) {
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = 30;

            $query = MaidsDB::query();

            if (!empty($search)) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('uae_id_maid', 'like', '%' . $search . '%');
            }

            $maids = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'total_count' => $maids->total(),
                'items' => $maids->map(function ($maids) {
                    return [
                        'system_id' => $maids->id,
                        'id' => $maids->name,
                        'text' => "{$maids->name} / UAE ID: {$maids->uae_id_maid}  "
                    ];
                })
            ]);
        }
        else {
            $agentNames = All_account_ledger_DB::where('group', 'maid agent')->get();

            return view('ERP.maids.maidsCv', compact('agentNames'));

        }
    }//end Method

    

    
    public function storeMaidsCvCntl(Request $request)
    {
        try {

            $request->merge([
                'name' => preg_replace('/\s+/', ' ', trim($request->input('name'))),
            ]);
    

            $validatedData = $request->validate([
                'name' => 'required|string|unique:maids_d_b_s,name',
                'uae_id_maid' => 'nullable|string',
                'salary' => 'nullable|integer',
                'maid_status' => 'nullable|string',
                'maid_type' => 'nullable|string',
                'maid_booked' => 'nullable|string',
                'visa_status' => 'nullable|string',
                'attachment' => 'nullable|string',
                'agency' => 'required|string',
                'img' => 'nullable|image|max:3000',
                'img2' => 'nullable|image|max:3000',
                'nationality' => 'nullable|string',
                'religion' => 'nullable|string',
                'age' => 'nullable|integer',
                'dob' => 'required|date',
                'marital_status' => 'nullable|string',
                'children' => 'nullable|string',
                'education' => 'nullable|string',
                'height' => 'nullable|string',
                'weight' => 'nullable|string',
                'lang_english' => 'nullable|string',
                'lang_arabic' => 'nullable|string',
                'cooking' => 'nullable|string',
                'assist_in_kitchen' => 'nullable|string',
                'baby_sitting' => 'nullable|string',
                'washing' => 'nullable|string',
                'cleaning' => 'nullable|string',
                'passport_number' => 'nullable|string',
                'passport_exp_date' => 'nullable|date',
                'note' => 'nullable|string',
                'exp_country' => 'nullable|string',
                'animal' => 'nullable|string',
                'visit_visa_expired' => 'nullable|date',
                'child' => 'nullable|string',
                'agent_ref' => 'nullable|string',
            ]);
    
            $validatedData['name'] = strtoupper($validatedData['name']);
            $maid = new MaidsDB();
    
            $maid->age = 0; // Default value, can be updated later if needed
            foreach ($validatedData as $key => $value) {
                $maid->$key = $value;
            }
    
            $maid->created_by = Auth::user()->name;


            $s3Service = new S3FileService();

         // Handle img upload
            if ($request->hasFile('img')) {
                $imageUrl = $s3Service->uploadToS3($request->file('img'), 'ahlia_maid_img', true);
                if (!$imageUrl) {
                    return response()->json(['message' => 'Image upload to S3 failed'], 500);
                }
                $maid->img = $imageUrl;
            }

            // Handle img2 upload
            if ($request->hasFile('img2')) {
                $imageUrl =  $s3Service->uploadToS3($request->file('img2'), 'ahlia_maid_img', true);
                if (!$imageUrl) {
                    return response()->json(['message' => 'Image upload to S3 failed'], 500);
                }
                $maid->img2 = $imageUrl;
            }

    
            $maid->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Maid information saved successfully!',
                'maid' => $maid
            ], 201);
    
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error($ex->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Database error occurred.'], 500);
        }
        
    }
    

    // URL/update-maid-cv
    public function editMaidCv(Request $request)
    {
        $validatedData = $request->validate([
            'maidName' => 'required|string',
            'pob' => 'nullable|string',
            'maidId' => 'required|exists:maids_d_b_s,id',
            'video_edit' => 'nullable|file',
            'maidSalary' => 'required|numeric',
            'editImg' => 'nullable|image|max:3000',
            'editImg2' =>'nullable|image|max:3000',
            'maid_type'=> 'required|string',
            'english_level'=> 'nullable|string',
            'arabic_level'=> 'nullable|string',
            'edit_agent' => 'required|string',
            'edit_note' => 'nullable|string',
            'exp_country' => 'nullable|string',
            'edit_period_country' => 'nullable|string',
            'edit_book'=>'nullable|string',
            'edit_visit_visa_expired'=>'nullable|date',
            'edit_visa_status'=>'nullable|string',
            'edit_cooking_level' => 'nullable|string',
            'edit_religion' => 'nullable|string',
            'edit_marital_status' => 'nullable|string',
            'edit_education' => 'nullable|string',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'passport_number' => 'nullable|string',
            'edit_age' => 'nullable|integer',
            'dob' => 'nullable|date',
            'edit_payment'=>'nullable|string',
            'edit_nationality'=>'required|string',
            'edit_passport_expired'=>'nullable|date',
            'edit_agent_ref' =>'nullable|string',
            'edit_phone' =>'nullable|string',
            'edit_child'=>'nullable|string',
            'edit_moi' => 'nullable|string',
            'edit_branch' => 'nullable|string',
            'edit_uae_id' => 'nullable|string',
            'start_as_p4' => 'nullable|date',
            'custom_book' => 'nullable|string',
            'uid' => [
            'nullable','string','max:30',
            Rule::unique('maids_d_b_s','uid')->ignore($request->input('maidId'))
        ],
        ]);
    
        try { 
            $maidRecord = MaidsDb::find($validatedData['maidId']);
    
            if (!$maidRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maid not found'
                ], 404);
            }
    
            $disk = 'beta';

            $s3Service = new S3FileService();
    
          if ($request->hasFile('editImg')) {
            $s3Service->deletePreviousFileFromS3($maidRecord->img, $disk);
            $imageUrl =   $s3Service->uploadToS3($request->file('editImg'), 'ahlia_maid_img', true);
            if (!$imageUrl) {
                return response()->json(['message' => 'Image upload to S3 failed'], 500);
            }
            $maidRecord->img = $imageUrl;
        }
        if ($request->hasFile('editImg2')) {
            $s3Service->deletePreviousFileFromS3($maidRecord->img2, $disk);
            $imageUrl =   $s3Service->uploadToS3($request->file('editImg2'), 'ahlia_maid_img', true);
            if (!$imageUrl) {
                return response()->json(['message' => 'Image upload to S3 failed'], 500);
            }
            $maidRecord->img2 = $imageUrl;
        }

      if ($request->hasFile('video_edit')) {
                $s3Service->deletePreviousFileFromR2($maidRecord->video_link, 'r2');

                $videoUrl = $s3Service->uploadVideo(
                    $request->file('video_edit'),
                    disk: 'r2',
                    directory: 'video',
                    maidId: $maidRecord->id,
                    maidName: $maidRecord->name ?? $request->input('maidName')
                );

                if (!$videoUrl) {
                    return response()->json(['message' => 'Video upload failed'], 500);
                }

                $maidRecord->video_link = $videoUrl;
            }

                if ($request->filled('pob')) {
     
                    MaidsDb::whereKey($maidRecord->id)
                        ->update(['meta->pob' => $request->input('pob')]);
                } elseif ($request->has('pob')) {
  
                    MaidsDb::whereKey($maidRecord->id)
                        ->update(['meta' => DB::raw("JSON_REMOVE(COALESCE(meta, JSON_OBJECT()), '$.pob')")]);
                }



        
            $maidRecord->update([
              'uid' => $validatedData['uid'] ,
              'meta->pob' => $request->input('pob'),
              'name' => strtoupper($validatedData['maidName']),
              'salary' => Auth::user()->group === 'accounting'
                    ? $validatedData['maidSalary']
                    : $maidRecord->salary, 
                'agency' => $validatedData['edit_agent'],
                'maid_type' =>  $validatedData['maid_type'],
                'lang_english' => $validatedData['english_level'],
                'lang_arabic' => $validatedData['arabic_level'],
                'note' => $validatedData['edit_note'],
                'maid_booked'=> $validatedData['custom_book'] ?? $validatedData['custom_book'] ?? $validatedData['edit_book'],
                'exp_country'=> $validatedData['exp_country'],
                'period_country'=> $validatedData['edit_period_country'],
                'visit_visa_expired'=>$validatedData['edit_visit_visa_expired'],
                'visa_status'=>$validatedData['edit_visa_status'],
                'cooking' => $validatedData['edit_cooking_level'],
                'religion' => $validatedData['edit_religion'],
                'marital_status' => $validatedData['edit_marital_status'],
                'education' => $validatedData['edit_education'] ?? 'secondary',
                'height' => $validatedData['height'],
                'weight' => $validatedData['weight'],
                'passport_number' => $validatedData['passport_number'],
                'dob' => $validatedData['dob'],
                'age' => $validatedData['edit_age'] ?? 0,
                'payment'=> $validatedData['edit_payment'],
                'nationality'=>$validatedData['edit_nationality'],
                'passport_exp_date'=>$validatedData['edit_passport_expired'],
                'agent_ref'=>$validatedData['edit_agent_ref'],
                'phone_maid'=>$validatedData['edit_phone'],
                'child'=>$validatedData['edit_child'],
                'moi'=>$validatedData['edit_moi'],
                'branch' => $validatedData['edit_branch'] ?? null,
                'uae_id_maid' => $validatedData['edit_uae_id'] ?? null,
                'start_as_p4' => $validatedData['start_as_p4'] ?? null,
                'updated_by' => Auth::user()->name,
                'updated_at' => Carbon::now()
            ]);

            logsdt::create([
                'user_name' => Auth::user()->name,
                'maid_name' => $request->input('maidName'),     
                'changes' => json_encode([ 
                    'name' => $validatedData['maidName'],
                    'salary' => $validatedData['maidSalary'],
                    'agency' => $validatedData['edit_agent'],
                    'maid_type' => $validatedData['maid_type'],
                    'note' => $validatedData['edit_note'],
                    'maid_booked'=> $validatedData['edit_book'],
             
                ]), 
            ]);
              
            return response()->json([
                'status' => 'success',
                'message' => 'Maid Updated!'
            ], 201);
    
        } catch (\Illuminate\Database\QueryException $ex) {
            Log::error($ex->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Database error occurred.'], 500);
        }
    }


// attach/maid/doc-expiry
public function addMaidDocExpiry(Request $request)
{
    $validatedData = $request->validate([
        'maid_id' => 'required|exists:maids_d_b_s,id|unique:maid_doc_expiries,maid_id',
        'labor_card_expiry' => 'nullable|date',
        'passport_expiry' => 'nullable|date',
        'visa_expiry' => 'nullable|date',
        'eid_expiry' => 'nullable|date'
    ]);

    $maidDocExpiry = maid_doc_expiry::create([
        'maid_id' => $validatedData['maid_id'],
        'labor_card_expiry' => $validatedData['labor_card_expiry'] ?? null,
        'passport_expiry' => $validatedData['passport_expiry'] ?? null,
        'visa_expiry' => $validatedData['visa_expiry'] ?? null,
        'eid_expiry' => $validatedData['eid_expiry'] ?? null,
        'created_by'=> Auth::user()->name
     
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Maid document expiry details added successfully.',
        'data' => $maidDocExpiry
    ], 201);
}



    public function viewMaidCv ($id){

         $cv = MaidsDB::findOrFail($id);

        return view('ERP.maids.template.cv', compact('cv'));
    }

// URL /book/maid

// URL /book/maid
public function bookMaid(Request $request)
{
    $request->validate([
        'id'   => 'required|exists:maids_d_b_s,id',
        'note' => 'nullable|string'
    ]);

    // make it atomic to prevent two users booking the same maid at the same time
    return DB::transaction(function () use ($request) {
        $id = (int) $request->input('id');

        // Re-check and update in one statement
        $bookingString = sprintf(
            'booked_by:%s_%s note:%s',
            Auth::user()->name,
            Carbon::now()->format('Y-m-d H:i:s'),
            (string) $request->input('note', '')
        );

        $updated = MaidsDB::where('id', $id)
            ->where(function ($q) {
                $q->whereNull('maid_booked')->orWhere('maid_booked', '');
            })
            ->update([
                'maid_booked' => $bookingString,
                'updated_by'  => Auth::user()->name,
                'updated_at'  => Carbon::now(),
            ]);

        if ($updated === 0) {
            // already booked by someone else
            return response()->json([
                'status'  => false,
                'message' => 'Maid booked or on hold with someone else. Please check!',
            ], 403);
        }

        // Reload the fresh model to log changes
        $maid = MaidsDB::findOrFail($id);

        // Build a small, explicit change log (no array_diff on nested values)
        $changes = [
            'maid_booked' => [
                'to' => $maid->maid_booked,
            ],
            'updated_by' => [
                'to' => $maid->updated_by,
            ],
            'updated_at' => [
                'to' => (string) $maid->updated_at,
            ],
        ];

        logsdt::create([
            'user_name' => Auth::user()->name,
            'maid_name' => $maid->name,
            'changes'   => json_encode($changes, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Maid Booked!',
        ], 201);
    });
}

//end Method

    public function updateMaidLink(Request $request){

        $video = MaidsDB::findOrFail($request->id);
        $video->video_link = $request->vide_link;
        $video->save();

        return response()->json([
            'status' => 'success',
            'message' => 'video link Updated!'
        ], 201);

    }

    public function updateMaidStatus(Request $request){

        $status = MaidsDB::findOrFail($request->id);
        $status->maid_status = $request->status;

        $status->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Updated!'
        ], 201);

    }

    public function maidReport($name)
    {
        $maid = MaidsDB::where('name', $name)->first();
    
   
        $latestP4And1 = MaidsDB::select('name')
            ->with([
                'p1Conts' => function ($query) {
                    $query->orderBy('created_at', 'desc')->get();  
                },
                'p4Conts' => function ($query) {
                    $query->orderBy('created_at', 'desc')->get(); 
                }
            ])
            ->where('name', $name)
            ->first();
    
 
        $p1Conts = $latestP4And1->p1Conts->map(function ($con) {
            $con->closing_balance = General_journal_voucher::calculateCustomerClosingBalance($con->customer);
            $con->return_date = maidReturnCat1::where('contract', $con->contract_ref)->first();
            return $con;
        });
    
     
        $p4Conts = $latestP4And1->p4Conts->map(function ($con) {
            $con->closing_balance = General_journal_voucher::calculateCustomerClosingBalance($con->customer);
            $con->return_date = ReturnedMaid::where('contract', $con->Contract_ref)->first();
            return $con;
        });
    
  
        $latestP1Cont = $p1Conts->first(); 
        $latestP4Cont = $p4Conts->first();
    
        return view('ERP.maids.maid_report', compact('p1Conts', 'p4Conts', 'name', 'maid', 'latestP1Cont', 'latestP4Cont'));
    }

     // url  /payroll/history/{name}
    public function maidPayRollHistory($name){

        $maid = MaidsDB::select('name','id')
        ->with('maidPayRoll')->where('name', $name)->first();

     
        return view('ERP.maids.maid_payroll', compact('maid', 'name'));
    }



    
// url /maids/upload-attachment name uploadMaidAttachment
public function uploadMaidAttachment(Request $request)
{
    try {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,avi,wmv,mov,ogg,qt,pdf,doc,docx|max:5000',
            'maid_name' => 'required|string',
            'note' => 'required'
        ]);

        // Find maid ID from the name
        $maid = MaidsDB::where('name', $request->maid_name)->firstOrFail();
        $maidId = $maid->id;

        $file = $request->file('file');
        $note = $request->input('note');
        $disk = 'beta';
        $fileName = uniqid('maid_') . '_' . $file->getClientOriginalName();

        $folder = 'ahlia_maid_attach/';
        $path = Storage::disk($disk)->put($folder . $fileName, file_get_contents($file));

        if (!$path) {
            Log::error("File upload to S3 failed.");
            return response()->json(['message' => 'File upload to S3 failed'], 500);
        }

        $fileUrl = Storage::disk($disk)->url($folder . $fileName);

        $attachment = new MaidAttachment();
        $attachment->maid_id = $maidId; // store ID instead of name
        $attachment->note = $note;
        $attachment->file_name = $fileName;
        $attachment->file_type = $file->getClientMimeType();
        $attachment->file_path = $fileUrl;
        $attachment->created_by = Auth::user()->name ?? null;
        $attachment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'File uploaded and saved successfully!',
            'file_url' => $fileUrl,
        ], 201);

    } catch (\Exception $ex) {
        Log::error("Error uploading file: " . $ex->getMessage());
        return response()->json(['status' => 'error', 'message' => 'An unexpected error occurred.'], 500);
    }
}

// url /maids/delete-attachment
public function deleteMaidAttachment(Request $request)
{
    try {
        $request->validate([
            'attachment_id' => 'required|exists:maid_attachments,id',
        ]);

  
        $attachment = MaidAttachment::findOrFail($request->input('attachment_id'));

        // Define the disk and the file path
        $disk = 'beta';
        $filePath = str_replace(Storage::disk($disk)->url(''), '', $attachment->file_path);

        // Delete the file from S3
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }


        $attachment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'File deleted successfully!',
        ], 200);

    } catch (\Exception $ex) {
        Log::error("Error deleting file: " . $ex->getMessage());
        return response()->json(['status' => 'error', 'message' => 'An unexpected error occurred.'], 500);
    }
}

public function pageMaidAttachment(){


    return view ('ERP.maids.maid_attach');
}

public function dataTableMaidAttachment(Request $request)
{
    $search  = $request->input('search');
    $status  = $request->input('status'); // keep same behavior as your original "if ($status)"
    $page    = (int) $request->input('page', 1);
    $perPage = (int) $request->input('per_page', 10);

    // Base query using Query Builder
    $base = DB::table('maid_attachments as ma')
        ->leftJoin('maids_d_b_s as m', 'm.id', '=', 'ma.maid_id')
        ->when($status, function ($q) use ($status) {
            $q->where('m.maid_status', $status);
        })
        ->when($search, function ($q) use ($search) {
            $q->where('m.name', 'like', "%{$search}%");
        });

    // Get total BEFORE pagination
    $total = (clone $base)->count();

    // Page data
    $rows = $base
        ->orderByDesc('ma.id')
        ->select([
            'ma.id',
            'ma.maid_id',
            'ma.note',
            'ma.file_name',
            'ma.file_type',
            'ma.file_path',
            'ma.created_by',
            'ma.updated_by',
            'ma.created_at',
            'ma.updated_at',
            'm.name as maid_name',
            'm.maid_status',
        ])
        ->forPage($page, $perPage)
        ->get();

    // Transform to keep the same shape you expect (with maidInfo nested)
    $data = $rows->map(function ($r) {
        return [
            'id'          => $r->id,
            'maid_id'     => $r->maid_id,
            'note'        => $r->note,
            'file_name'   => $r->file_name,
            'file_type'   => $r->file_type,
            'file_path'   => $r->file_path,
            'created_by'  => $r->created_by,
            'updated_by'  => $r->updated_by,
            'created_at'  => $r->created_at,
            'updated_at'  => $r->updated_at,
            'maidInfo'    => [
                'id'          => $r->maid_id,
                'name'        => $r->maid_name,
                'maid_status' => $r->maid_status,
            ],
        ];
    });

    // Compute last page like LengthAwarePaginator
    $lastPage = (int) ceil($total / max($perPage, 1));

    return response()->json([
        'data' => $data,
        'meta' => [
            'current_page' => $page,
            'last_page'    => $lastPage,
            'total'        => $total,
            'per_page'     => $perPage,
        ],
    ]);
}

///doc-expire/{id}
public function getMaidWithDocExpiry($id)
{
    $maid = MaidsDB::with('maidDocExpiry')->findOrFail($id);

    if (!$maid->maidDocExpiry) {
        return response()->json(['error' => 'Document expiry details not found'], 404);
    }

    return response()->json($maid);
}



    //Url /maid-doc-expiry/{id}
    public function showMaidDocExpiry($id)
    { 

        $maid = MaidsDB::with('maidDocExpiry')  
        ->select('id', 'name') 
        ->findOrFail($id);

        $name = $maid->name;

        if (!$maid) {
            return redirect()->back()->withErrors('Maid not found.');
        }
     
   
   
    
        return view('ERP.maids.maid_doc_expiry', compact('maid','name'));
    }
    
}//End the class


