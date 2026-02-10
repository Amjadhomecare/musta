@extends('keen')
@section('content')

@include('partials.nav_customer')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <!--begin::Content container-->
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Contracts card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-header d-flex align-items-center">
        <h4 class="card-title mb-0 flex-grow-1 text-center" id="customer-name" data-name="{{ $name }}">
            P4 contracts: {{ $name }}
        </h4>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="allp4dataTable"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Created&nbsp;At</th>
                <th>Maid&nbsp;Start</th>
                <th>Return&nbsp;At</th>
                <th class="text-end">Working&nbsp;Days</th>
                <th>Reason</th>
                <th>Maid</th>
                <th>Contract&nbsp;Ref</th>
                <th>Status</th>
                <th>Created&nbsp;By</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- ───────── /Contracts card ───────── --}}

  </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->


@endsection

@push('scripts')
    @vite('resources/js/customers/all_branchs.js')
@endpush
