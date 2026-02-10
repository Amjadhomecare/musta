@extends('keen')
@section('content')


<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- ───────── Filter card ───────── --}}
        <div class="card shadow-sm mb-8">
            <div class="card-header  py-4">
                <h5 class="card-title fw-bold mb-0">Filter&nbsp;Contracts</h5>
            </div>

            <div class="card-body">
                <div class="row gx-5 gy-4 align-items-end">

                    {{-- From date --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="min-date" class="form-label fw-semibold mb-1">From&nbsp;Date</label>
                        <input type="date"
                               id="min-date"
                               class="form-control form-control-sm form-control-solid" />
                    </div>

                    {{-- To date --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="max-date" class="form-label fw-semibold mb-1">To&nbsp;Date</label>
                        <input type="date"
                               id="max-date"
                               class="form-control form-control-sm form-control-solid" />
                    </div>

                    {{-- Contract status --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="filterContracts" class="form-label fw-semibold mb-1">Contract&nbsp;Status</label>
                        <select id="filterContracts"
                                class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Returned</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="card-footer text-end py-3">
                <small class="text-muted">Select date range and contract status to filter the results.</small>
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
                                <th>Contract&nbsp;Ref</th>
                                <th>Started&nbsp;Date</th>
                                <th>Invoice&nbsp;Ref</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Maid</th>
                                <th class="text-end">Contract&nbsp;Value</th>
                                <th>Status</th>
                                <th>Created&nbsp;By</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- populated by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- ───────── /Contracts table card ───────── --}}

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->


<div id="return-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="maidReturnForm" class="px-3 py-4">
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
                        <label for="started_date" class="form-label">Started date</label>
                        <input type="text" readOnly class="form-control" id="started_date">
                    </div>
                 
                    <div class="form-group mb-3">
                        <label for="customerInput" class="form-label">Customer</label>
                        <input type="text" readOnly class="form-control" id="customerInput" name="customer">
                    </div>
                    <div class="form-group mb-4">
                        <label  class="form-label">Amount For Company</label>
                        <input type="number" value="0"  class="form-control"  name="amount_for_com">
                    </div>
                    <div class="form-group mb-4">
                        <label  class="form-label">Amount For Maid</label>
                        <input type="number" value="0" class="form-control"  name="amount_for_maid">
                    </div>
                    <div class="form-group mb-4">
                        <label for="reasonInput" class="form-label">Reason</label>
                        <input type="text" class="form-control" id="reasonInput" name="reason" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


@endsection



@push('scripts')
    @vite('resources/js/p1/contract.js')
@endpush

