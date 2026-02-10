@extends('keen')
@section('content')

@include('partials.nav_customer')


<!-- Modal -->

<div class="modal" id="makeJVModal"  tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
            <form id="makeJVForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="makeJVModalLabel">Create Journal Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                           <!--Post_type -->
                 <div class="mb-3">
                        <label for="voucherType" class="form-label fw-bold">Post type for customer</label>
                        <select class="form-select border border-dark rounded" id="postType" name="post_type" required>
                            <option value="" disabled selected>Select Voucher Type</option>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
               
                        </select>
                    </div>

                <div class="modal-body">
                    <!-- Customer Name -->
                    <div class="mb-3">
                        <label for="maidName" class="form-label fw-bold">Customer</label>
                        <input type="text" class="form-control border border-dark rounded" value="{{$name}}" name='customer_name'   readOnly>
                    </div>

                     <!-- other ledger Ledger -->
                            <div class="mb-3">
                        <label for="debitLedger" class="form-label fw-bold">Other Ledger</label>
                        <select class="form-select border border-dark rounded" id="otherLedger" name="other_account" style="width: 100%;" required>
                            <option value="" disabled selected>Select Debit Ledger</option>
                            <!-- Options should be populated dynamically -->
                        </select>
                    </div>


                        <!--Maid Name -->
                     <div class="mb-3">
                        <label for="maidName" class="form-label fw-bold">Maid name</label>
                        <select class="form-select  border border-dark rounded"  data-control="select2" id="maid_name" name="maid_name" style="width: 100%;">
                            <option value="" disabled selected>Select maid</option>
                            <!-- Options should be populated dynamically -->
                        </select>
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

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header d-flex flex-wrap justify-content-center gap-2">
        <h4 class="card-title mb-0" id="customer-name" data-name="{{ $name }}">
          Statement&nbsp;of&nbsp;Account:&nbsp;{{ $name }}
        </h4>
        <span class="badge badge-light-primary fs-6 align-self-center">Balance&nbsp;{{ number_format($balance,2) }}</span>
      </div>
    </div>

    {{-- ───────── SOA table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="datatable_soa"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th class="d-none">Created At</th>
                <th>Date</th>
                <th>Type</th>
                <th>Ref</th>
                <th>Service</th>
                <th>Note</th>
                <th>Maid</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Running&nbsp;Bal.</th>
              </tr>
            </thead>
            <tbody><!-- populated by DataTables --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container-xxl -->
</div><!-- /content wrapper -->

@if (auth()->user()->group === 'accounting')

<button type="button" class="btn btn-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#makeJVModal">
    add Jv for {{ $name }}
</button>


@endif





@endsection

@push('scripts')
    @vite('resources/js/customers/customer_soa.js')
@endpush
