@extends('keen')
@section('content')
@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container" >

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0" id="maid-name" data-name="{{ $name }}">
          Advance&nbsp;/&nbsp;Deduction for {{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Advance / Deduction form ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h5 class="card-title mb-0">Add&nbsp;Entry</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('storeAdvanceOrDeductionCntl') }}" method="POST" class="row gx-5 gy-4">
          @csrf

                 <!-- Maid Selection -->
                        <div class="mb-2">
                            <label for="maidSelect" class="form-label">Maid</label>
                            <input readOnly type='text' class="form-control form-control-sm" value="{{$name}}" id="maidSelectPayroll" name="maid" required>
                        </div>


          <div class="md-2">
            <label for="month" class="form-label fw-semibold mb-1">Month&nbsp;&amp;&nbsp;Year</label>
            <input type="month" id="month" name="date" class="form-control form-control-sm form-control-solid" required>
          </div>

          <div class="col-12">
            <label for="note" class="form-label fw-semibold mb-1">Note</label>
            <input type="text" id="note" name="note" class="form-control form-control-sm form-control-solid" required>
          </div>

          <div class="col-6">
            <label for="deduction" class="form-label fw-semibold mb-1">Deduction</label>
            <input type="number" id="deduction" name="deduction" class="form-control form-control-sm form-control-solid">
          </div>
          <div class="col-6">
            <label for="allowance" class="form-label fw-semibold mb-1">Allowance</label>
            <input type="number" id="allowance" name="allowance" class="form-control form-control-sm form-control-solid">
          </div>

          <div class="col-12 d-flex justify-content-end pt-3">
            <button type="submit" class="btn btn-primary btn-sm px-6">Submit</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ───────── Records table ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-header">
        <h5 class="card-title mb-0">Records</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="advance_maid_datatable" class="table table-hover table-row-dashed fs-6 w-100" data-name="{{ $name }}">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>For&nbsp;M-Y</th>
                <th>Maid</th>
                <th>Note</th>
                <th class="text-end">Deduction</th>
                <th class="text-end">Allowance</th>
                <th>Created&nbsp;At</th>
                <th>Updated&nbsp;At</th>
                <th>Created&nbsp;By</th>
                <th>Updated&nbsp;By</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody><!-- DataTables --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->



<!-- Modal -->
<div id="maid-dedction" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
            <form id="maidDeductionForm" class="px-3">
        @csrf

            <div class="form-group">
                <input type="hidden" id="idForDeduction" name="advanceDataId">
            </div>


            <div class="form-group">
                <label for="deductionInput">Deduction Amount</label>
                <input type="number" class="form-control" id="deductionInput" name="deductionMaid" placeholder="Enter deduction amount">
            </div>

            <div class="form-group">
                <label for="allowanceInput">Allowance Amount</label>
                <input type="number" class="form-control" id="allowanceInput" name="allowanceMaid" placeholder="Enter allowance amount">
            </div>

            <div class="form-group">
                <label for="noteInput">Note</label>
                <input type="text" class="form-control" id="noteInput" name="noteMaid" placeholder="Enter note">
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
         
        </form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@push('scripts')
@vite('resources/js/maid_payroll/advance_maid.js')
@endpush
