@extends('keen')
@section('content')


@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 text-center" id="maid-name" data-name="{{ $name }}">
          P1&nbsp;Contracts&nbsp;for&nbsp;{{ $name }}
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
          <table id="p1ContractDataTable" class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Contract&nbsp;Ref</th>
                <th>Started&nbsp;Date</th>
                <th>Invoice&nbsp;Ref</th>
                <th>Return&nbsp;Date</th>
                <th>Reason</th>
                <th>Customer</th>
                <th class="text-end">Contract&nbsp;Value</th>
                <th>Status</th>
                <th>Created&nbsp;By</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows injected here --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->

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
    @vite(["resources/js/maid/p1_contract.js"])
@endpush
