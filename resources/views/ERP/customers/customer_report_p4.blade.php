@extends('keen')
@section('content')

@include('partials.nav_customer')

{{-- Keen-style P4 Contracts page --}}
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          P4&nbsp;contracts:&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Filter card ───────── --}}
    <div class="card shadow-sm mb-8">
      <div class="card-header py-3">
        <h5 class="card-title mb-0">Filter Contracts</h5>
      </div>
      <div class="card-body">
        <div class="row gx-5 gy-4">
          <div class="col-12 col-md-6 col-lg-4">
            <label for="filterContracts" class="form-label fw-semibold mb-1">Contract&nbsp;Status</label>
            <select id="filterContracts" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="1">Active</option>
              <option value="0">Returned</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    {{-- ───────── /Filter card ───────── --}}

    {{-- ───────── Contracts table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="p4dataTable"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Created&nbsp;At</th>
                <th>Maid&nbsp;Start</th>
                <th>Date&nbsp;of&nbsp;Return</th>
                <th>Reason</th>
                <th>Maid</th>
                <th>nationality</th>
                <th class="text-end">Working&nbsp;Days</th>
                <th>Contract&nbsp;Ref</th>
                <th>Status</th>
                <th>Created&nbsp;By</th>
                <th class="text-end">Action</th>
                <th>Old&nbsp;Contract&nbsp;Details</th>
                <th>Join&nbsp;Note</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- ───────── /Contracts table card ───────── --}}

  </div><!-- /app-container -->
</div><!-- /content wrapper -->

<!-- Modal for Maid Return -->
<div id="return_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel">Maid Return Form</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="maidReturnForm">
                    @csrf
                    <div class="form-group">
                        <label for="maidNameInput">Maid Name</label>
                        <input readonly type="text" id="maidNameInput" name="maidName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contractRefInput">Contract Ref</label>
                        <input readonly type="text" id="contractRefInput" name="contractRef" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="customerInput">Customer</label>
                        <input readonly type="text" id="customerInput" name="customer" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="reasonInput">Reason</label>
                        <textarea id="reasonInput" name="reason" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="phone">Customer Phone</label>
                        <input type="text" id="phoneInput" name="phone" class="form-control" required>
                    </div>

                    <br>  
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Complaint -->
<div id="comp_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="compModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compModalLabel">Customer Complaint</h5>
                     
                </button>
            </div>
            <div class="modal-body">
                <form id="customerCompForm">
                    @csrf
                    <div class="form-group">
                        <label for="maidName">Maid Name</label>
                        <input readonly type="text" id="maidName" name="maidName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contractRef">Contract Ref</label>
                        <input readonly type="text" id="contractRef" name="contractRef" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="customerComp">Customer</label>
                        <input readonly type="text" id="customerComp" name="customer" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="reasonInput">Reason</label>
                        <textarea id="reasonInput" name="reason" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="statusSelect">Status</label>
                        <select id="statusSelect" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="typeSelect">Type</label>
                        <select id="typeSelect" name="type" class="form-control" required>
                            <option value="general">General</option>
                            <option value="ranaway">Ranaway</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignedToSelect">Assigned To</label>
                        <select style="width:100%" id="assignedToSelect" name="assignedTo" class="form-control" required>
                
                        </select>
                    </div>
                     <br>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div id="start_date_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Update Contract Dates</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form id="startDateForm">
          @csrf
          
          <!-- Hidden Contract ID -->
          <input type="hidden" id="idContract" name="started_date_id">

          <!-- Start Date -->
          <div class="mb-3">
            <label for="startDatenput" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="startDatenput" name="started_date" required>
          </div>

          <!-- Return Date -->
          <div class="mb-3">
            <label for="returnDatenput" class="form-label">Return Date</label>
            <input type="date" class="form-control" id="returnDatenput" name="returned_date">
          </div>

          <!-- reason -->
          <div class="mb-3">
            <label for="reasonInput" class="form-label">Reason</label>
            <textarea id="reasonInput2" name="reason" class="form-control" ></textarea>
          </div>
          


          <!-- Submit -->
          <div class="text-end">
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@push('scripts')
    @vite('resources/js/customers/p4_report.js')
@endpush
