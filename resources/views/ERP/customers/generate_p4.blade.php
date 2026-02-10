@extends('keen')
@section('content')
@include('partials.nav_customer')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          Make&nbsp;Package&nbsp;Four&nbsp;Contract:&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Form card ───────── --}}
    <div class="card shadow-sm">
      <div class="card-header py-3">
        <h5 class="card-title mb-0">Package&nbsp;Four&nbsp;Contract</h5>
      </div>

      <div class="card-body">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('storeCategory4ContractCntl') }}" class="row g-4">
          @csrf

          {{-- Contract ref & start date --}}
          <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold">Contract&nbsp;Ref</label>
            <input type="text" name="contract_ref" value="P4_{{ $randomRefNumber }}" class="form-control form-control-sm form-control-solid" readonly>
          </div>

          <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold">Start&nbsp;Date</label>
            <input type="date" name="contract_date" value="{{ $today }}" class="form-control form-control-sm form-control-solid" readonly>
          </div>

          <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold">Customer</label>
            <input type="text" name="selected_customer" value="{{ $name }}" class="form-control form-control-sm form-control-solid" readonly>
          </div>

          {{-- Stripe switch --}}
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="stripePayment" name="stripe_payment">
              <label class="form-check-label fw-semibold" for="stripePayment">
                Pay via&nbsp;Stripe (only if customer subscribes)
              </label>
            </div>
          </div>

          {{-- Maid select --}}
          <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Maid</label>
            <select name="selected_maid" class="form-select form-select-sm form-select-solid" required>
              <option disabled selected value="">Select Maid</option>
              @foreach ($maids as $maid)
                <option value="{{ $maid->name }}">{{ $maid->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Initial installment block --}}
          <div class="col-12"><span class="fw-semibold">Initial Installment</span></div>
          <div class="col-6 col-md-3">
            <label class="form-label">Accrued&nbsp;Date</label>
            <input id="accruedDate" type="date" value="{{ $today }}" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Monthly&nbsp;Amount</label>
            <input id="accruedAmount" type="number" step="0.01" value="0" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Notes</label>
            <input id="note" type="text" value="no note" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Cheques</label>
            <input id="cheque" type="text" value="no cheque" class="form-control form-control-sm form-control-solid">
          </div>

          {{-- Dynamic container --}}
          <div id="entryContainer" class="col-12"></div>

          {{-- Recurring number --}}
          <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold">Recurring&nbsp;Number</label>
            <input type="number" id="RecurringNumber" name="Recurring" value="1" class="form-control form-control-sm form-control-solid">
          </div>

          <div class="col-12">
            <button type="button" id="addMore" onclick="RecurringJV()" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-plus-circle me-1"></i>Add&nbsp;Installment&nbsp;Entry
            </button>
          </div>

          {{-- Submit --}}
          <div class="col-12 text-end pt-2">
            <button type="submit" class="btn btn-success btn-sm">Submit&nbsp;Package&nbsp;4&nbsp;Contract</button>
          </div>
        </form>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->
@endsection

@push('scripts')
<script>
  $(function(){ $('select[name="selected_maid"]').select2({ width:'100%' }); })
</script>
@vite('resources/js/p4/add_contractp4.js')
@endpush