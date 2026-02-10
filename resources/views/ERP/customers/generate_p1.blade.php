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
          Make&nbsp;Package&nbsp;One&nbsp;Contract:&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Form card ───────── --}}
    <div class="card shadow-sm">
      <div class="card-header py-3">
        <h5 class="card-title mb-0">Package&nbsp;One</h5>
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

        <form method="POST" action="{{ route('storeCateOneContract') }}" class="row g-4">
          @csrf

          {{-- Customer (read-only) --}}
          <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Customer</label>
            <input type="text" name="selected_customer" value="{{ $name }}" class="form-control form-control-sm form-control-solid" readonly>
          </div>

          {{-- Maid select --}}
          <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Maid</label>
            <select name="maid" class="form-select form-select-sm form-select-solid" required>
              <option disabled selected value="">Select Maid</option>
              @foreach ($maids as $maid)
                <option value="{{ $maid->name }}">{{ $maid->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Service / connection --}}
          <div class="col-12 col-lg-6">
            <label class="form-label fw-semibold">Service</label>
            <select name="connaction" id="connectionSelect" class="form-select form-select-sm form-select-solid" onchange="handleConnectionChange(this.value)" required>
              <option disabled selected value="">Select Service</option>
              @foreach ($selectConnection as $conn)
                <option value="{{ $conn->invoice_connection_name }}">{{ $conn->invoice_connection_name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Start / End dates --}}
          <div class="col-6 col-lg-3">
            <label class="form-label fw-semibold">Start&nbsp;Date</label>
            <input type="date" name="date_start" value="{{ $today }}" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6 col-lg-3">
            <label class="form-label fw-semibold">End&nbsp;Date</label>
            <input type="date" name="date_ended" value="{{ $twoYearsLater }}" class="form-control form-control-sm form-control-solid">
          </div>

          {{-- Trial dates --}}
          <div class="col-6 col-lg-3">
            <label class="form-label fw-semibold">Trial&nbsp;Start</label>
            <input type="date" name="trial_start" value="{{ $today }}" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6 col-lg-3">
            <label class="form-label fw-semibold">Trial&nbsp;End</label>
            <input type="date" name="trial_end" value="{{ $trialEnd }}" class="form-control form-control-sm form-control-solid">
          </div>

          {{-- Dynamic entry container (JS will populate) --}}
          <div id="entryContainer" class="col-12"></div>

          {{-- Total credit (auto) --}}
          <div class="col-12 col-lg-4">
            <label class="form-label fw-semibold">Total&nbsp;Credit</label>
            <input type="text" id="totalCredit" name="total_invoice" class="form-control form-control-sm form-control-solid text-success" readonly>
          </div>

          {{-- Submit --}}
          <div class="col-12 text-end pt-2">
            <button type="submit" class="btn btn-success btn-sm">Submit</button>
          </div>
        </form>
      </div><!-- /card-body -->
    </div><!-- /card -->

  </div><!-- /container-xxl -->
</div><!-- /content wrapper -->
@endsection

@push('scripts')
<script>
  $(function(){
    $('select[name="maid"], select[name="connaction"]').select2({ width:'100%' });
  });
</script>
@vite('resources/js/p1/add_contractp1.js')
@endpush