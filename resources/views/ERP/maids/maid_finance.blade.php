@extends('keen')
@section('content')


@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container" ">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0" id="maid-data" data-name="{{ $name }}">
          Finance&nbsp;Report&nbsp;for&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Filter card ───────── --}}
    <div class="card shadow-sm mb-5">
      <div class="card-body">
        <div class="row gx-4 gy-3 align-items-end">
          <div class="col-sm-6 col-lg-4">
            <label for="voucher_type" class="form-label fw-semibold mb-1">Voucher&nbsp;Type</label>
            <select id="voucher_type" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option>New arrival</option>
              <option>Payment Voucher</option>
              <option>Receipt Voucher</option>
              <option>Journal Voucher</option>
              <option>Credit note</option>
              <option value="debit_memo">Debit memo</option>
              <option value="invoice">Invoice</option>
              <option>Typing Invoice</option>
              <option>Invoice Package4</option>
              <option>Invoice Package1</option>
            </select>
          </div>
          <div class="col-sm-6 col-lg-3">
            <label for="vt" class="form-label fw-semibold mb-1">Post&nbsp;Type</label>
            <select id="vt" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="debit">Debit</option>
              <option value="credit">Credit</option>
            </select>
          </div>

          @if (auth()->user()->group === 'accounting')
            <div class="col-auto ms-auto">
              <button type="button" class="btn btn-primary btn-sm d-flex align-items-center"
                      data-bs-toggle="modal" data-bs-target="#makeJVModal">
                <i class="bi bi-plus-lg me-2"></i>Add&nbsp;JV&nbsp;({{ $name }})
              </button>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- ───────── Table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="maid_finance" class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Ref&nbsp;Code</th>
                <th>Voucher&nbsp;Type</th>
                <th>Post&nbsp;Type</th>
                <th>Account</th>
                <th class="text-end">Amount</th>
                <th>Note</th>
                <th>Created&nbsp;By</th>
                <th>Updated&nbsp;By</th>
                <th>Created&nbsp;At</th>
              </tr>
            </thead>
            <tbody><!-- DataTables load --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->

<!-- Modal -->
<div class="modal fade" id="makeJVModal" tabindex="-1" aria-labelledby="makeJVModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="makeJVForm" >
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="makeJVModalLabel">Create Journal Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Maid Name -->
                    <div class="mb-3">
                        <label for="maidName" class="form-label fw-bold">Maid Name</label>
                        <input type="text" class="form-control border border-dark rounded" value="{{$name}}" id="maidName" name="maid_name" readOnly>
                    </div>

                    <!-- Date -->
                    <div class="mb-3">
                        <label for="date" class="form-label fw-bold">Date</label>
                        <input type="date" class="form-control border border-dark rounded" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                               <!-- Voucher Type -->
                               <div class="mb-3">
                        <label for="voucherType" class="form-label fw-bold">Voucher Type</label>
                        <select class="form-select border border-dark rounded" id="voucherType" name="voucher_type" required>
                            <option value="" disabled selected>Select Voucher Type</option>
                            <option value="New arrival">New arrival</option>
                            <option value="Payment Voucher">Payment Voucher</option>
                            <option value="Receipt Voucher">Receipt Voucher</option>
                            <option value="Journal Voucher">Journal Voucher</option>
                            <option value="Credit note">Credit note</option>
                            <option value="debit_memo">Debit memo</option>
                            <option value="invoice">Invoice</option>
                            <option value="Typing Invoice">Typing Invoice</option>
                            <option value="Invoice Package4">Invoice Package4</option>
                            <option value="Invoice Package1">Invoice Package1</option>
                        </select>
                    </div>

                    <!-- Debit Ledger -->
                    <div class="mb-3">
                        <label for="debitLedger" class="form-label fw-bold">Debit Ledger</label>
                        <select class="form-select  border border-dark rounded" id="debitLedger" name="account_debit" style="width: 100%;" required>
                            <option value="" disabled selected>Select Debit Ledger</option>
                            <!-- Options should be populated dynamically -->
                        </select>
                    </div>

                    <!-- Credit Ledger -->
                    <div class="mb-3">
                        <label for="creditLedger" class="form-label fw-bold">Credit Ledger</label>
                        <select class="form-select  border border-dark rounded" id="creditledger" name="account_credit" style="width: 100%;" required>
                            <option value="" disabled selected>Select Credit Ledger</option>
                            <!-- Options should be populated dynamically -->
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Notes</label>
                        <textarea class="form-control border border-dark rounded" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <!-- Amount -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold">Amount</label>
                        <input type="number" class="form-control border border-dark rounded" id="amount" name="amount" required>
                    </div>

        
         
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@push('scripts')
    @vite(['resources/js/maid/maid_report.js'])
@endpush
