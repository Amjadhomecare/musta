@extends('keen')
@section('content')


<div class="container mt-5">

    
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">


                <table id="notification_table" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>AssignTo</th>
                            <th>status</th>
                            <th>Note</th>
                            <th>Customer</th>
                            <th>Maid</th>
                            <th>Ref</th>
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
                                    <label for="updatecustomerName" class="form-label">Customer Name</label>
                                    <select class="form-select" id="updatecustomerName" name="customer" readOnly>
                             
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="updatememomemo" class="form-label">Memo</label>
                                    <textarea class="form-control" id="updatememo" name="memo" rows="3" readOnly></textarea>
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
                                    <input type="text" class="form-control" id="contractRef" name="contractRef" readOnly>
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
                                    <input maxlength="100" type="text" class="form-control"  name="actionTaken">
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

    </div>
@endsection

@push('scripts')
    @vite(['resources/js/send_note/note_user.js'])
@endpush
