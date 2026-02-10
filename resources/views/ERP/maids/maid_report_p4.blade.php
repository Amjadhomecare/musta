@extends('keen')
@section('content')


@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container" >

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 text-center" id="maid-name" data-name="{{ $name }}">
          P4&nbsp;Contracts&nbsp;for&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Filter card ───────── --}}
    <div class="card shadow-sm mb-5">
      <div class="card-body">
        <div class="row gx-4 gy-3 align-items-end">
          <div class="col-sm-6 col-lg-4">
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

    {{-- ───────── Table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="p4dataTable" class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Date</th>
                <th>Return&nbsp;Date</th>
                <th>Reason</th>
                <th>Contract&nbsp;Ref</th>
                <th>Customer</th>
                <th class="text-end">Working&nbsp;Days</th>
                <th>Status</th>
                <th>Created&nbsp;By</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper 
<!-- Modal -->
<div id="return_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="maidReturnForm" class="px-3">
                    @csrf
                    <input readOnly type="text" id="maidNameInput" name="maidName" placeholder="Maid Name">
                    <input readOnly  type="text" id="contractRefInput" name="contractRef" placeholder="Contract Ref">
                    <input readOnly  type="text" id="customerInput" name="customer" placeholder="Customer">
                    <input type="text" id="reasonInput" name="reason" placeholder="Reason" required>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Modal for complain -->
<div id="comp_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="customerCompForm" class="px-3">
                    @csrf

                    <input readOnly type="text" id="maidName" name="maidName" placeholder="Maid Name">
                    <input readOnly type="text" id="contractRef" name="contractRef" placeholder="Contract Ref">
                    <input readOnly type="text" id="customerComp" name="customer" placeholder="Customer">

          
                    <textarea type="text" id="reasonInput" name="reason" placeholder="Reason" required></textarea>

            
                    <div class="form-group">
                        <label for="statusSelect">Status</label>
                        <select id="statusSelect" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in progress">In Progress</option>
                            <option value="done">Done</option>
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
                        <select id="assignedToSelect" name="assignedTo" class="form-control">
                            <option value="">Not Assigned</option>

                            <option value="staff1">Staff 1</option>
                            <option value="staff2">Staff 2</option>
                        </select>
                    </div>

            
                    <div class="form-group">
                        <label for="forwardToSelect">Forward To</label>
                        <select id="forwardToSelect" name="forwardTo" class="form-control">
                            <option value="">Not Forwarded</option>

                            <option value="department1">Department 1</option>
                            <option value="department2">Department 2</option>
                        </select>
                    </div>

             
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<!-- Modal -->
<div id="start_date_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="startDateForm" class="px-3">
                    @csrf
                    <input readOnly type="text" id="idContract" name="started_date_id" >
                    <input  type="date" id="startDatenput" name="started_date">
      
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@endsection

@push('scripts')
    @vite(["resources/js/maid/p4_contract.js"])
@endpush
