@extends('keen')
@section('content')


<div class="container mt-5">
    <!-- Button to Open Modal -->
    <button type="button" class="btn btn-blue" data-bs-toggle="modal" data-bs-target="#complaintModal">
        Register Complaint
    </button>

    
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">


                <table id="notification_table" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>AssignTo</th>
                            <th>status</th>
                            <th>Ref</th>
                            <th>Customer</th>
                            <th>Maid</th>
                            <th>Memo</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>By</th>
                            <th>Action Taken</th>
                            <th>Action</th>
                         
                        </tr>
                    </thead>
                </table>
            </div>
</div>


    <!-- Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="complaintModalLabel">Register Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formComplaint" >
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- First Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maidName" class="form-label">Maid Name</label>
                                    <select class="form-select" id="maidName" name="maidName">
                                   
                                    </select>
                                </div>
                          
                                <div class="mb-3">
                                    <label for="assignedTo" class="form-label">Assigned To</label>
                                    <select class="form-select" id="assignedTo" name="assignedTo">
                           
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="customerName" class="form-label">Customer Name</label>
                                    <select class="form-select" id="customerName" name="customer">
                             
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="memo" class="form-label">Memo</label>
                                    <textarea class="form-control" id="memo" name="reason" rows="3"></textarea>
                                </div>
                            </div>
                            <!-- Second Column -->
                            <div class="col-md-6">
                     
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="general">General</option>
                                        <option value="ranaway">Ranaway</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                           
                                <div class="mb-3">
                                    <label for="contractRef" class="form-label"> Reference</label>
                                    <input type="text" class="form-control" id="contractRef" name="contractRef">
                                </div>

                         
                    
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send notify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Modal for update the notify -->
    <div class="modal fade" id="updatecomplaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="complaintModalLabel">Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateNotify" >
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- First Column -->
                            <div class="col-md-6">

                            <div class="mb-3">
                                
                                    <input type="hidden" class="form-select" id="updateId" name="id">
                                   
                                  
                                </div>

                                <div class="mb-3">
                                    <label for="updatemaidName" class="form-label">Maid Name</label>
                                    <select class="form-select" id="updatemaidName" name="maidName">
                                   
                                    </select>
                                </div>
                          
                                <div class="mb-3">
                                    <label for="updateassignedTo" class="form-label">Assigned To</label>
                                    <select class="form-select" id="updateassignedTo" name="assignedTo">
                           
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="updatecustomerName" class="form-label">Customer Name</label>
                                    <select class="form-select" id="updatecustomerName" name="customer">
                             
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="updatememomemo" class="form-label">Memo</label>
                                    <textarea class="form-control" id="updatememo" name="memo" rows="3"></textarea>
                                </div>
                            </div>
                            <!-- Second Column -->
                            <div class="col-md-6">
                     
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="general">General</option>
                                        <option value="ranaway">Ranaway</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                             
                           
                                <div class="mb-3">
                                    <label for="contractRef" class="form-label"> Reference</label>
                                    <input type="text" class="form-control" id="contractRef" name="contractRef">
                                </div>

                                <div class="mb-3">
                                    <label for="statu" class="form-label">Status</label>
                                    <select class="form-select" id="statu" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="in progress">In progress</option>
                                        <option value="done">Done</option>
                                    </select>
                                </div>


                                <div class="mb-3">
                                    <label class="form-label"> Action taken </label>
                                    <input maxlength="22" type="text" class="form-control"  name="actionTaken">
                                </div>                 
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"> Update Notificication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    @vite(['resources/js/send_note/note_to.js'])
@endpush
