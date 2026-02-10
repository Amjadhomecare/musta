@extends('keen')
@section('content')

@include('partials.nav_customer')

{{-- Keen-style P1 Contracts page --}}
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          P1&nbsp;Contracts:&nbsp;{{ $name }}
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
          <table id="p1ContractDataTable"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Started&nbsp;Date</th>
                <th>Return&nbsp;Date</th>
                <th>Reason</th>
                <th>Maid</th>
                <th>Contract&nbsp;Ref</th>
                <th>Invoice&nbsp;Ref</th>
                <th class="text-end">Contract&nbsp;Value</th>
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
    {{-- ───────── /Contracts table card ───────── --}}

  </div><!-- /app-container -->
</div><!-- /content wrapper -->


<!-- Passport Modal -->
<div id="passport-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Maid Passport</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="passportForm">
          @csrf
          <input type="hidden" id="passportRefCode" name="ref_code">

          <div class="form-group mb-3">
            <label for="passportStatusInput" class="form-label">Passport Status</label>
            <input type="text" class="form-control" id="passportStatusInput" name="maid_passport" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>




<!-- Edit Modal -->
<div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Contract Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editContractForm">
          @csrf
          <input type="hidden" id="editContractId" name="contract_id">

          <div class="form-group mb-3">
            <label for="editStartedDate" class="form-label">Started Date</label>
            <input type="date" class="form-control" id="editStartedDate" name="started_date" required>
          </div>

          <div class="form-group mb-3">
            <label for="editEndDate" class="form-label">End of contract Date</label>
            <input type="date" class="form-control" id="editEndDate" name="ended_date">
          </div>
          <div class="form-group mb-3">
            <label for="editReturnDate" class="form-label">Return date</label>
            <input type="date" class="form-control" id="editReturnDate" name="return_date">
          </div>

          <div class="form-group mb-3">
            <label for="editReturnNote" class="form-label">Reason</label>
            <textarea class="form-control" id="editReturnNote" name="reason"></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Return Modal -->
<div id="return-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Maid Return Form</h5>
              
                </button>
            </div>
            <div class="modal-body">
                <form id="maidReturnForm">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="daysDifference" class="form-label">Days Since Start</label>
                        <input type="text" readOnly class="form-control" id="daysDifference" name="daysDifference">
                    </div>
                    <div class="form-group mb-3">
                        <label for="maidNameInput" class="form-label">Maid Name</label>
                        <input type="text" readOnly class="form-control" id="maidNameInput" name="maidName">
                    </div>
                    <div class="form-group mb-3">
                        <label for="contractRefInput" class="form-label">Contract Ref</label>
                        <input type="text" readOnly class="form-control" id="contractRefInput" name="contractRef">
                    </div>
                    <div class="form-group mb-3">
                        <label for="started_date" class="form-label">Started Date</label>
                        <input type="text" readOnly class="form-control" id="started_date">
                    </div>
                    <div class="form-group mb-3">
                        <label for="customerInput" class="form-label">Customer</label>
                        <input type="text" readOnly class="form-control" id="customerInput" name="customer">

                     <div class="form-group mb-3 d-none" id="passportStatusGroup">
                        <label for="passportStatus" class="form-label">Passport Status</label>
                        <select class="form-select" id="passportStatus" name="passport_status">
                            <option value="">Select status</option>
                            <option value="with_staff">Passport received by staff</option>
                            <option value="with_customer">Still with customer</option>
                        </select>
                    </div>

      
                    <div class="form-group mb-4">
                        <label class="form-label">Amount For Company</label>
                        <input type="number" value="0" class="form-control" name="amount_for_com">
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Amount For Maid</label>
                        <input type="number" value="0" class="form-control" name="amount_for_maid">
                    </div>
                    <div class="form-group mb-4">
                        <label for="reasonInput" class="form-label">Reason</label>
                        <textarea class="form-control" id="reasonInput" name="reason" required></textarea>
                    </div>

            

                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>




@endsection

@push('scripts')
    @vite('resources/js/customers/customer_report_p1.js')
@endpush
