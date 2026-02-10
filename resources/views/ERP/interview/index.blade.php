@extends('keen')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-4 mb-3">
                    <label for="min-date" class="form-label">From Date:</label>
                    <input type="date" id="min-date" class="form-control" />
                </div>
                <div class="col-lg-3 col-md-4 mb-3">
                    <label for="max-date" class="form-label">To Date:</label>
                    <input type="date" id="max-date" class="form-control" />
                </div>
                <div class="form-group col-md-2 mb-1 mt-1">
                                <label for="status-id">Status</label>
                                <select type="text" class="form-control" id="status-id" >
                                    <option value="">All</option>
                                    <option value="0">Pending</option>
                                    <option value="2">Reject by maid</option>
                                    <option value="3">Reject by customer</option>
                                    <option value="1">Sucsess</option> 
                        </select>
                    </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">
                        
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary  btn-lg open-modal-btn btn-block mb-3" data-bs-toggle="modal" data-bs-target="#add-modal">Add Intreiew</button>
                </div>

                <table id="table-interview" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                       
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>




<!-- Add Interview Modal -->
<div class="modal fade" id="add-modal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="interview-form">
                    @csrf
                    <div class="mb-3">
                        <label for="maid_name" class="form-label">Maid Name</label>
                        <select class="form-control select2" id="maid_name" name="maid_name" style="width: 100%;" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <select class="form-control select2" id="customer_name" name="customer_name" style="width: 100%;"></select>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="room" class="form-label">Room</label>
                        <input type="text" class="form-control" id="room" name="room" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Interview</button>
                </form>
            </div>
        </div>
    </div>
</div>

                   
<!-- Edit Interview Modal -->
<div class="modal fade" id="edit-modal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-interview-form">
                    @csrf
                    <input type="hidden" name="edit_interview_id" id="edit_interview_id">

                    <div class="mb-3">
                        <label for="edit_maid_name" class="form-label">Maid Name</label>
                        <select class="form-control select2" id="edit_maid_name" name="maid_name" style="width: 100%;" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_customer_name" class="form-label">Customer Name</label>
                        <select class="form-control select2" id="edit_customer_name" name="customer_name" style="width: 100%;"></select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="0">Pending</option>
                            <option value="2">Reject by maid</option>
                            <option value="3">Reject by customer</option>
                            <option value="1">Sucsess</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_note" class="form-label">Note</label>
                        <textarea class="form-control" id="edit_note" name="note"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_room" class="form-label">Room</label>
                        <input type="text" class="form-control" id="edit_room" name="room" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Update Interview</button>
                </form>
            </div>
        </div>
    </div>
</div>


                
@endsection

@push('scripts')
    @vite(['resources/js/intreview/intreview.js'])
@endpush

