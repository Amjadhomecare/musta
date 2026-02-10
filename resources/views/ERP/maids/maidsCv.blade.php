@extends('keen')
@section('content')


<!--begin::Content wrapper-->

<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

    <!--begin::Filter card-->
    <div class="card shadow-sm mb-7">
        <div class="card-body">
            <!-- Filters grid -->
            <div class="row gx-5 gy-4">
                <!-- Agent -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filterAgent" class="form-label fw-semibold mb-1">Agent</label>
                    <select id="filterAgent" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All Agents</option>
                        @foreach ($agentNames as $agent)
                            <option value="{{ $agent->ledger }}">{{ $agent->ledger }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Nationality -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filterNationality" class="form-label fw-semibold mb-1">Nationality</label>
                    <select id="filterNationality" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All Nationalities</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Ethiopia">Ethiopia</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Myanmar">Myanmar</option>
                        <option value="Kenya">Kenya</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Sri_Lanka">Sri Lanka</option>
                        <option value="Tanzanian">Tanzanian</option>
                        <option value="India">India</option>
                        <option value="Ghana">Ghana</option>
                        <option value="nepal">Nepal</option>
                        <option value="pakistan">Pakistan</option>
                        <option value="zimbabwe">Zimbabwe</option>
                        <option value="Madagascar">Madagascar</option>
                        <option value="Burundi">Burundi</option>
                    </select>
                </div>

                <!-- Package -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filterPackage" class="form-label fw-semibold mb-1">Package</label>
                    <select id="filterPackage" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All Packages</option>
                        <option value="PV">Private Visa</option>
                        <option value="p1">Package 1</option>
                        <option value="HC">Package 4</option>
                        <option value="Direct hire">Direct Hire</option>
             
                    </select>
                </div>

                <!-- Status -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filterStatus" class="form-label fw-semibold mb-1">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All Status</option>
                        <option value="Approved">Approved</option>
                        <option value="Pending">Pending</option>
                        <option value="Hired">Hired</option>
                        <option value="ran away inside guaranty">ran away inside guaranty</option>
                        <option value="ran away outside guaranty">ran away outside guaranty</option>
                        <option value="send back agent">send back agent(charge the agent)</option>
                        <option value="transferred">transferred</option>
                        <option value="released">released(this mean no charge to the agent)</option>
                        <option value="visa rejected">Visa rejected (charge the agent the vist visa)</option>
                    </select>
                </div>

                <!-- Booking / Remark -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter_book" class="form-label fw-semibold mb-1">Booking / Remark</label>
                    <select id="filter_book" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All</option>
                        <option value="Hold">Hold</option>
                        <option value="Sick">Sick</option>
                        <option value="Booked">Booked</option>
                        <option value="Vacation">Vacation</option>
                        <option value="Archive">Archive</option>
                    </select>
                </div>

                <!-- Visa -->
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="visa_filter" class="form-label fw-semibold mb-1">Visa</label>
                    <select id="visa_filter" class="form-select form-select-sm form-select-solid w-100">
                        <option value="">All</option>
                        <option value="with visa">With Visa</option>
                        <option value="without visa">Without Visa</option>
                        <option value="for market">Show in Website</option>
                    </select>
                </div>

                <!-- Toggle -->
                <div class="col-12 col-md-6 col-xl-3 d-flex align-items-end">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="includeNullBook">
                        <label class="form-check-label ms-2 fw-semibold" for="includeNullBook">
                            Exclude Booked / Notes
                        </label>
                    </div>
                </div>
            </div>
            <!-- Add button bottom right -->
            <div class="d-flex justify-content-end mt-5">
                <button type="button" class="btn btn-primary btn-lg px-6" data-bs-toggle="modal" data-bs-target="#add-cv-modal">
                    Add Maid CV
             

                    </button>
                </div>
            </div>
        </div>
        <!--end::Filter card-->


    <!--begin::Table card-->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="cv_datatable" class="table table-hover table-row-dashed fs-6 gy-5 gs-5 w-100">
                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                        <tr>
                            <th>Action</th>
                            <th>Created</th>
                            <th>Img</th>
                            <th>Name</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Nationality</th>
                            <th>Agent</th>
                            <th>Type</th>
                            <th>Book</th>
                            <th>Pay</th>
                            <th>Visa</th>
                            <th>Filters</th>
                            <th>P1 PP Exp</th>
                            <th>Visit Visa Exp</th>
                            <th>Created by</th>
                            <th>Updated by</th>
                            <th>Created at</th>
                            <th>Updated at</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--end::Table card-->

</div>
<!--end::Content container-->


   
<!--Modal -->
<div id="add-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add new maid CV</h5>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="maidForm" class="px-3">
                    @csrf

                    <!-- Modal Body -->
                    <div class="container">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-4">
                                <!-- Name, Age, Nationality, Agency -->
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input    pattern="[A-Za-z\s.,!?]*" title="Only English letters, spaces, and basic punctuation are allowed."  type="text" class="form-control" id="name" name="name" required>
                                </div>


                                <div class="form-group mb-3">
                                    <label for="agent_ref" class="form-label">Agency Referance</label>
                                    <input type="text" class="form-control"  name="agent_ref" >
                                </div>

                           
                                <div class="form-group mb-3">
                                    <label for="dob" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" value="0" class="form-control"  name="age" >
                                </div>


                                <div class="form-group mb-3">
                                    <label for="nationality" class="form-label">Nationality</label>
                                    <select class="form-control" id="nationality" name="nationality">
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Sri_Lanka">Sri_Lanka</option>
                                        <option value="Tanzanian">Tanzanian</option>
                                        <option value="India">India</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="nepal">nepal</option>
                                        <option value="pakistan">pakistan</option>
                                        <option value="zimbabwe"> zimbabwe</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Burundi">Burundi</option> 

                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="agency" class="form-label">Agency</label>
                                    <select class="form-control" id="agency" name="agency" required>
                                        @foreach ( $agentNames as $agent )
                                        <option value="{{$agent->ledger}}">{{$agent->ledger}}</option>
                                        @endforeach

                                    </select>
                                </div>

                                   <!-- Salary, Maid Type -->
                                   <div class="form-group mb-3">
                                    <label for="salary" class="form-label">Salary</label>
                                    <input type="number" class="form-control" id="salary" name="salary" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="maid_type" class="form-label">Maid Type</label>
                                    <select class="form-control" id="maid_type" name="maid_type">
                                       <option value="p1">Package 1</option>
                                       <option value="PV">Private visa (used for visa maids p1 under sponsor visa)</option>
                                        <option value="p1">Package 1</option>
                              
                                        <option value="Direct hire">direct hire</option>
                            
                                    </select>
                                </div>

                          

                                <div class="form-group mb-3">
                                    <label for="visa_status" class="form-label">Visa Status</label>
                                    <select class="form-control" id="visa_status" name="visa_status">
                                        <option value="without visa">Without Visa</option>
                                        <option value="with visa">With Visa</option>
                                        <option value="for market">show her in website outside county</option>
                                    </select>
                                </div>

                                <!-- Maid Booked, Visa Status -->
                                <div class="form-group mb-3">
                                    <label for="maid_booked" class="form-label">Maid Booked</label>
                                    <select class="form-control" id="maid_booked" name="maid_booked">
                                        <option value="">No Booked</option>
                                        <option value="Booked">Booked</option>
                                        <option value="Hold">Hold</option>
                                        <option value="archive">Archive</option>

                                    </select>
                                </div>

                                      <!-- UAE ID, Salary, Maid Type -->
                                      <div class="form-group mb-3">
                                    <label for="uae_id_maid" class="form-label">UAE ID (Optional)</label>
                                    <input type="text" class="form-control" id="uae_id_maid" name="uae_id_maid">
                                </div>
                            
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-4">
                                <!-- Cooking Option -->
                                <div class="form-group mb-3">
                                    <label for="cooking" class="form-label">cooking level</label>
                                    <select class="form-control" id="cooking" name="cooking">
                                        <option value="basic">basic</option>
                                        <option value="intermediate">intermediate</option>
                                        <option value="advance">advance</option>
                                    </select>
                                </div>

                                         <!-- Languages -->
                                <div class="form-group mb-3">
                                    <label for="lang_english" class="form-label">Speaks English</label>
                                    <select class="form-control" id="lang_english" name="lang_english">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="fluent">Fluent</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="lang_arabic" class="form-label">Speaks Arabic</label>
                                    <select class="form-control" id="lang_arabic" name="lang_arabic">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="fluent">Fluent</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="img" class="form-label">Image 1 (Optional)</label>
                                    <input type="file" class="form-control" id="img" name="img">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="img2" class="form-label">Image 2 (Optional)</label>
                                    <input type="file" class="form-control" id="img2" name="img2">
                                </div>

                  

                                <div class="form-group mb-3">
                                    <label for="note" class="form-label">Note (Optional)</label>
                                    <input type="text" class="form-control" id="note" name="note">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="exp_country" class="form-label">Country (Optional)</label>
                                    <input type="text" class="form-control" id="exp_country" name="exp_country">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="period_country" class="form-label">years of exp (Optional)</label>
                                    <input type="text" class="form-control" id="period_country" name="period_country">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="animal" class="form-label">Animal (Optional)</label>
                                    <input type="text" class="form-control" name="animal">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="visit_visa_expired" class="form-label">Visit Visa Expire date (Optional)</label>
                                    <input type="date" class="form-control"  name="visit_visa_expired">
                                </div>

                            </div>  <!-- End of second Column -->


                                 <!-- New Third Column -->
                         <div class="col-md-4">


                         <div class="form-group mb-3">
                                    <label for="religion" class="form-label">religion</label>
                                    <select class="form-control" id="religion" name="religion">
                                        <option value="christian">christian </option>
                                        <option value="muslim">muslim</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Budhist">Budhist</option>

                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="marital_status" class="form-label"> marital_status</label>
                                    <select class="form-control" id="marital_status" name="marital_status">
                                        <option value="single">single </option>
                                        <option value="married">married</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="child" class="form-label">Number Of chlid (Optional)</label>
                                    <input type="text" class="form-control" name="child">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="education" class="form-label">education</label>
                                    <select class="form-control" id="education" name="education">
                                        <option value="High school">High school </option>
                                        <option value="University study">University study</option>
                                        <option value="Elementary">Elementary</option>
                                        <option value="Secandary">Secandary</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="height" class="form-label">height(Optional)</label>
                                    <input type="text" class="form-control" id="height" name="height">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="weight" class="form-label">weight (Optional)</label>
                                    <input type="text" class="form-control" id="weight" name="weight">
                                </div>

                              <div class="form-group mb-3">
                                    <label for="washing" class="form-label">washing</label>
                                    <select class="form-control" id="washing" name="washing">
                                        <option value="yes">yes </option>
                                        <option value="no">no</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="cleaning" class="form-label">cleaning</label>
                                    <select class="form-control" id="cleaning" name="cleaning">
                                        <option value="yes">yes</option>
                                        <option value="no">no</option>
                                    </select>
                                </div>

                               <div class="form-group mb-3">
                                    <label for="baby_sitting" class="form-label">baby_sitting (Optional)</label>
                                    <input type="text" class="form-control" id="baby_sitting" name="baby_sitting">
                                </div>

                                <div class="form-group mb-3">
                                    <label for="passport_number" class="form-label">passport_number (Optional)</label>
                                    <input type="text" class="form-control" id="passport_number" name="passport_number">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="passport_exp_date" class="form-label">P1 passport_exp_date (Optional)</label>
                                    <input type="date" class="form-control" id="passport_exp_date" name="passport_exp_date">
                                </div>



                              <!-- Submit Button -->
                              <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>

                        </div>  <!-- End of the  Third Column -->

                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="edit-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Edit the CV</h5>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="maidFormEdit" class="px-3" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="maidId" id="maidIdInput" class="form-control mb-3">

                        <!-- Column 1 -->
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="maidNameInput">Name</label>
                                <input  type="text" class="form-control" id="maidNameInput" name="maidName">
                            </div>

                         <div class="form-group mb-3">
                            <label for="edit_pob">UID</label>
                            <input type="text" class="form-control" id="uid" name="uid">
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_dob">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_dob" name="dob">
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_pob">Place of Birth</label>
                            <input type="text" class="form-control" id="edit_pob" name="pob">       
                        </div>
                         <div class="form-group mb-3">
                            <label for="edit_age">Age</label>
                            <input type="number" value="0" class="form-control" id="edit_age" name="edit_age">
                        </div>


                            <div class="form-group mb-3">
                                    <label for="edit_moi" class="form-label">MOL</label>
                                    <input type="text" class="form-control"  id="edit_moi"  name="edit_moi" >
                                </div>

                                <div class="form-group mb-3">
                                    <label for="edit_moi" class="form-label">UAE ID</label>
                                    <input type="text" class="form-control"  id="edit_uae_id"  name="edit_uae_id" >
                                </div>

                                <div class="form-group mb-3">
                                    <label for="edit_branch" class="form-label"> visa under</label>
                                     <select class="form-control" id="edit_branch" name="edit_branch">
                                        <option value=""> with out visa</option>
                                        <option value="customer"> under customer visa</option>
                                        <option value="h">homecare</option>
                                        <option value="fc"> familycare</option>
                                        <option value="kh">khorfakan</option>
                                        <option value="ahl">ahlia</option>

                                      </select>  
                                     
                                </div>



                            <div class="form-group mb-3">
                                    <label for="edit_agent_ref" class="form-label">Agency Referance</label>
                                    <input type="text" class="form-control"  id="edit_agent_ref"  name="edit_agent_ref" >
                                </div>


                            <div class="form-group mb-3">
                                <label for="maidNationalityInput">Nationality</label>
                                <select type="text" class="form-control" id="maidNationalityInput" name="edit_nationality">
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Sri_Lanka">Sri Lanka</option>
                                    <option value="Tanzanian">Tanzanian</option>
                                    <option value="India">India</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="nepal">nepal</option>
                                    <option value="pakistan">pakistan</option> 
                                    <option value="zimbabwe"> zimbabwe</option>  
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Burundi">Burundi</option> 
                                </select>
                            </div>

                            <label for="visa_status" class="form-label">Visa Status</label>
                            <select class="form-control" id="edit_visa_status" name="edit_visa_status">
                                <option value="without visa">Without Visa</option>
                                <option value="with visa">With Visa</option>
                                <option value="c">Under Customer visa</option>

                                <option value="for market">Show CV in website outside country</option>
                                <option value="archive">Archive</option>
                            </select>

                            <div class="form-group mb-3">
                                <label for="maidSalaryInput">Salary</label>
                                <input type="text" class="form-control" id="maidSalaryInput" name="maidSalary">
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_exp_country">Exp Country</label>
                                <input type="text" class="form-control" id="edit_exp_country" name="exp_country">
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_period_country">Years Exp Country</label>
                                <input type="text" class="form-control" id="edit_period_country" name="edit_period_country">
                            </div>



                            <div class="form-group mb-3">
                                <label for="editImg">Image</label>
                                <input type="file" class="form-control-file" id="editImg" name="editImg">
                                <img id="maidImgInput" class="img-fluid mt-2" style="max-width: 100px;">
                            </div>

                            <div class="form-group mb-3">
                                <label for="video">Video</label>
                                <input type="file" accept="video/*" class="form-control-file" id="video" name="video_edit">
                            </div>

                            <div class="form-group mb-3">
                                <label for="editImg2">Image2</label>
                                <input type="file" class="form-control-file" id="editImg2" name="editImg2">
                                <img id="maidImg2Input" class="img-fluid mt-2" style="max-width: 100px;">
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="edit_maid_type">Maid Type</label>
                                <select class="form-control" id="edit_maid_type" name="maid_type">
                                  <option value="PV">Private visa (used for visa maids p1 under sponsor visa)</option>
                                    <option value="p1">Package1</option>
                                    <option value="HC">HC</option>
                                    <option value="direct hire">direct Hire</option>
                         
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="maidAgencyInput">Agency</label>
                                <select class="form-control" id="maidAgencyInput" name="edit_agent">
                                    @foreach ($agentNames as $agent)
                                        <option value="{{$agent->ledger}}">{{$agent->ledger}}</option>
                                    @endforeach
                                </select>
                            </div>

                 
                            <div class="form-group mb-3">
                                <label for="edit_visit_visa_expired">Visit Visa Expired Date</label>
                                <input type="date" class="form-control" id="edit_visit_visa_expired" name="edit_visit_visa_expired">
                            </div>


                            <div class="form-group mb-3">
                                <label for="edit_passport_expired"> P1 Passport Expired Date</label>
                                <input type="date" class="form-control" id="edit_passport_expired" name="edit_passport_expired">
                            </div>
       

                            <div class="form-group mb-3">
                                <label for="edit_english">English Level</label>
                                <select class="form-control" id="edit_english" name="english_level">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="fluent">Fluent</option>
                                </select>
                            </div>

                            
                            <div class="form-group mb-3">
                                <label for="edit_arabic">Arabic Level</label>
                                <select class="form-control" id="edit_arabic" name="arabic_level">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="fluent">Fluent</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_payment">p4 payment method</label>
                                <select class="form-control" id="edit_payment" name="edit_payment">
                                    <option value="cash">cash</option>
                                    <option value="bank">bank</option>
                                
                                </select>
                            </div>

                        
                            <div class="form-group mb-3">
                                <label for="edit_start_as_p4">Start as P4</label>
                                 <input type="date" class="form-control" id="start_as_p4" name="start_as_p4">

                            </div>


                            <div class="form-group mb-3">
                                <label for="edit_note">Note</label>
                                <input type="text" class="form-control" id="edit_note" name="edit_note">
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_phone">Phone</label>
                                <input type="text" class="form-control" id="edit_phone" name="edit_phone">
                            </div>



                            <div class="form-group mb-3">
                                <label for="edit_cooking_level">Edit the book</label>
                                <select class="form-control"  id="edit_book"name="edit_book" >
                                         <option id="current_book"> </option>
                                         <option value="">Remove Booking</option>
                                        <option value="Hold">Hold</option>
                                        <option value="Sick">Sick</option>
                                        <option value="Vacation">Vacation</option>
                                        <option value="Hold salary">Hold Salary</option>
                                        <option value="under training">under training</option>
                                        <option value="under process visa">under process visa</option>
                                        <option value="for cancellation">for cancellation</option>

                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="custom_book">Custom Book</label>
                                <input  class="form-control"  name="custom_book" >
                            </div>

                             <h3 style="color:red" id="thebook"></h3>
                        </div>

                        <!-- Column 3 (New Column) -->
                        <div class="col-md-4">
                 
                    
                            <div class="form-group mb-3">
                                <label for="edit_cooking_level">Cooking level</label>
                                <select class="form-control" id="edit_cooking_level" name="edit_cooking_level">
                                    <option value="basic">Basic</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advance">Advance</option>
                                </select>
                            </div>

                            <!-- New Select Fields -->
               
                            <div class="form-group mb-3">
                                <label for="edit_religion">Religion</label>
                                <select class="form-control" id="edit_religion" name="edit_religion">
                                <option value="christian">christian </option>
                                        <option value="muslim">muslim</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Budhist">Budhist</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_marital_status">Marital Status</label>
                                <select class="form-control" id="edit_marital_status" name="edit_marital_status">
                                           <option value="single">single </option>
                                            <option value="married">married</option>
                                            <option value="Divorced">Divorced</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_education">Education</label>
                                <select class="form-control" id="edit_education" name="edit_education">    
                                    <option value="primary">Primary</option>
                                    <option value="secondary">Secondary</option>
                                    <option value="college">College</option>
                                </select>
                            </div>

                        <div class="form-group mb-3">
                            <label for="edit_height">Height (cm)</label>
                            <input type="number" class="form-control" id="edit_height" name="height">
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_weight">Weight (kg)</label>
                            <input type="number" class="form-control" id="edit_weight" name="weight">
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_passport_number">Passport Number</label>
                            <input type="text" class="form-control" id="edit_passport_number" name="passport_number">
                        </div>

              
              
                  

                        <div class="form-group mb-3">
                            <label for="edit_age">Numner of child</label>
                            <input type="number" class="form-control" id="edit_child" name="edit_child">
                        </div>

                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Modal 3 For Booking Maid -->
<div id="booked-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Book CV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="maidFormBooked" class="px-3">
                    @csrf
                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="booked_name" class="form-label">Name</label>
                        <input type="text" id="booked_name" class="form-control" readOnly>
                    </div>

                    <!-- ID Field (Hidden) -->
                    <input type="hidden" name="id" id="booked_id">

                    <!-- Note Field -->
                    <div class="mb-3">
                        <label for="note_book" class="form-label">Note</label>
                        <input type="text" name="note" id="note_book" class="form-control" maxlength="45" placeholder="Enter a note (max 45  characters)">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-blue w-100">Book</button>
                </form>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->



    <div id="video-link-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update the link</h5>

                                    </div>

                                    <!-- Modal Body -->
                             <div class="modal-body">
                                        <form id="maidVideoLink" class="px-3">
                                            @csrf

                                                <input readOnly id="video_name" name="name"> </input>

                                                <input hidden name="id" id="video_id"> </input>

                                                <input name="vide_link" id="video_link"> </input>

                                                <button type="submit" class="btn btn-primary">Update</button>

                                        </form>
                                            </div>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->



    <div id="changing-status-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Maid status</h5>

                                    </div>

                                    <!-- Modal Body -->
                             <div class="modal-body">
                                        <form id="maidStatus" class="px-3">
                                            @csrf

                                                <input readOnly id="status_maid_name" name="name"> </input>

                                                <input readONly  name="id" id="status_id"> </input>

                                                <select name="status" id="current_status">
                                                  <option value="approved"> approved </option>
                                                  <option value="hired"> hired </option>
                                                  <option value="pending"> pending </option>
                                                  <option value="private"> private </option>
                                                    <option value="ran away inside guaranty">ran away inside guaranty</option>
                                                    <option value="ran away outside guaranty">ran away outside guaranty</option>
                                                    <option value="send back agent">send back agent(charge the agent)</option>
                                                    <option value="transferred">transferred</option>
                                                    <option value="released">released(this mean no charge to the agent)</option>
                                                    <option value="visa rejected">Visa rejected (charge the agent the vist visa)</option>
                                               
                                               </select>


                                                <button type="submit" class="btn btn-primary">Update Status</button>

                                        </form>
                                            </div>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->



                    
<div id="expiry-cv-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Document Expiry Date</h5>
             
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="maidDoc" class="px-3">
                    @csrf

                    <input type="hidden" id="maid_id" name="maid_id">
                

        
                    <div class="form-group">
                        <label for="passport_expiry">Passport Expiry Date</label>
                        <input type="date" id="passport_expiry" name="passport_expiry" class="form-control">
                    </div>

         
                    <div class="form-group">
                        <label for="eid_expiry">EID Expiry Date</label>
                        <input type="date" id="eid_expiry" name="eid_expiry" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="modal fade" id="maidFilterModal" tabindex="-1" aria-labelledby="maidFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="maidFilterModalLabel">Add Maid Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="maidFilter" method="POST" action="/filter-update">
                @csrf
                <div class="modal-body">
                    <!-- Maid ID -->
                    <input type="hidden" name="maid_id" id="filter_maid_id">

                    <div class="row">
                        <!-- General Filters -->
                        <div class="col-md-4">
                            <h6>General Preferences</h6>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="has_dog" value="0">
                                <input type="checkbox" class="form-check-input" id="has_dog" name="has_dog" value="1">
                                <label class="form-check-label" for="has_dog">Has Dog</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="has_cat" value="0">
                                <input type="checkbox" class="form-check-input" id="has_cat" name="has_cat" value="1">
                                <label class="form-check-label" for="has_cat">Has Cat</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="working_days_off" value="0">
                                <input type="checkbox" class="form-check-input" id="working_days_off" name="working_days_off" value="1">
                                <label class="form-check-label" for="working_days_off">Working Days Off</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="private_room" value="0">
                                <input type="checkbox" class="form-check-input" id="private_room" name="private_room" value="1">
                                <label class="form-check-label" for="private_room">Private Room</label>
                            </div>
                        </div>

                        <!-- Care Filters -->
                        <div class="col-md-4">
                            <h6>Care Preferences</h6>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="elderly_care" value="0">
                                <input type="checkbox" class="form-check-input" id="elderly_care" name="elderly_care" value="1">
                                <label class="form-check-label" for="elderly_care">Elderly Care</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="special_needs_care" value="0">
                                <input type="checkbox" class="form-check-input" id="special_needs_care" name="special_needs_care" value="1">
                                <label class="form-check-label" for="special_needs_care">Special Needs Care</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="live_out" value="0">
                                <input type="checkbox" class="form-check-input" id="live_out" name="live_out" value="1">
                                <label class="form-check-label" for="live_out">Live Out</label>
                            </div>
                        </div>

                        <!-- Cooking Filters -->
                        <div class="col-md-4">
                            <h6>Cooking Preferences</h6>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="knows_syrian_lebanese" value="0">
                                <input type="checkbox" class="form-check-input" id="knows_syrian_lebanese" name="knows_syrian_lebanese" value="1">
                                <label class="form-check-label" for="knows_syrian_lebanese">Knows Syrian, Lebanese Cuisine</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="can_assist_and_cook" value="0">
                                <input type="checkbox" class="form-check-input" id="can_assist_and_cook" name="can_assist_and_cook" value="1">
                                <label class="form-check-label" for="can_assist_and_cook">Can Assist and Cook</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="knows_gulf_food" value="0">
                                <input type="checkbox" class="form-check-input" id="knows_gulf_food" name="knows_gulf_food" value="1">
                                <label class="form-check-label" for="knows_gulf_food">Knows Gulf Food</label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="hidden" name="international_cooking" value="0">
                                <input type="checkbox" class="form-check-input" id="international_cooking" name="international_cooking" value="1">
                                <label class="form-check-label" for="international_cooking">International Cooking</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Baby Filters -->
                        <div class="col-md-12">
                            <h6>Baby Care Preferences</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3 form-check">
                                        <input type="hidden" name="baby_0_to_6" value="0">
                                        <input type="checkbox" class="form-check-input" id="baby_0_to_6" name="baby_0_to_6" value="1">
                                        <label class="form-check-label" for="baby_0_to_6">Baby less than 1 year</label>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="hidden" name="baby_6_to_12" value="0">
                                        <input type="checkbox" class="form-check-input" id="baby_6_to_12" name="baby_6_to_12" value="1">
                                        <label class="form-check-label" for="baby_6_to_12">1 year and above</label>
                                    </div>
                                </div>
                        
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-blue">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

                
@endsection

@push('scripts')
    @vite(['resources/js/maid/maid.js'])
@endpush

