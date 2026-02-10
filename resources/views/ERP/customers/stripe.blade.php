@extends('keen')
@section('content')

@include('partials.nav_customer')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Header card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header d-flex align-items-center gap-4 flex-wrap">
        <h4 class="card-title mb-0 flex-grow-1" id="customer-name" data-name="{{ $name }}">
          Stripe&nbsp;Payment&nbsp;Links&nbsp;for&nbsp;{{ $name }}
        </h4>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center"
                data-bs-toggle="modal" data-bs-target="#addStripe">
          <i class="bi bi-plus-lg me-2"></i>Add&nbsp;Stripe&nbsp;Link
        </button>
      </div>
    </div>

    {{-- ───────── Stripe link table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="url_table" class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Maid</th>
                <th>URL</th>
                <th class="text-end">Amount</th>
                <th>Created&nbsp;By</th>
                <th>Created&nbsp;At</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows injected here --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container-xxl -->
</div><!-- /content wrapper -->

       

<!-- Modal Structure -->
<div class="modal fade" id="addStripe" tabindex="-1" aria-labelledby="addStripe" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-secondary text-white rounded-top">
                <h5 class="modal-title" id="cashierReceiptVoucherModalLabel">Make Stripe subscription</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stripeForm" class="p-3">
                    <!-- Debit Ledger -->
                    <div class="mb-4">
                        <label for="debitLedger" class="form-label fw-bold">Selected Customer</label>
                            <input type="text" value="{{$name}}" name="customerName" class="form-control border border-dark rounded" readOnly>
                    </div>

                    <!-- Maid Name -->
                    <div class="mb-4">
                        <label for="maidName" class="form-label fw-bold">Maid Name</label>
                        <select class="form-select  border border-dark rounded" id="maidName" name="maidName" style="width: 100%;">
                            <option value="" disabled selected>Select Maid</option>
                        </select>
                    </div>

                    <!-- Dropdown menu for monthly and one_time_payment -->

                    <div class="mb-4">
                        <label for="paymentType" class="form-label fw-bold">Payment Type</label>
                        <select class="form-select border border-dark rounded" id="paymentType" name="payment_type" required>
                          
                            <option value="monthly">Monthly</option>
                            <option value="one_time">One Time</option>
                        </select>
                    </div>
                    

                    <!-- Note Input -->
                    <div class="mb-4">
                        <label for="note" class="form-label fw-bold">Note</label>
                        <textarea class="form-control border border-dark rounded" id="note" name="note" rows="3" placeholder="Add any note"></textarea>
                    </div>

              <!-- Amount Received -->
                            <div class="mb-4">
                                <label for="amountReceived" class="form-label fw-bold">Stripe Monthly Amount</label>
                                <input type="number" class="form-control border border-dark rounded" 
                                    id="amountReceived" 
                                    name="amount" 
                                    placeholder="Enter Amount" 
                                    step="any" 
                                    required>
                            </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border rounded" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success shadow-sm rounded">Make URL</button>
                    </div>
                </form>
            </div>
        </div>

        </div>
    </div>

    </div>

@endsection

@push('scripts')
    @vite('resources/js/stripe/add.js')
  
@endpush

