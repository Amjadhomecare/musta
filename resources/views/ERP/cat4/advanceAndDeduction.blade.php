@extends('keen')

@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- ───────── Add Advance / Allowance ───────── --}}
        <div class="card shadow-sm mb-10">
            <div class="card-header py-4">
                <h3 class="card-title fw-bold mb-0">Add Advance / Allowance</h3>
            </div>

            <div class="card-body" id="entryContainer">
                <form action="{{ route('storeAdvanceOrDeductionCntl') }}"
                      method="POST"
                      class="row gx-5 gy-4">
                    @csrf

                    {{-- Month & year --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="month" class="form-label fw-semibold mb-1">Month & Year</label>
                        <input type="month"
                               id="month"
                               name="date"
                               required
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    {{-- Maid (Select2) --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="maidSelect" class="form-label fw-semibold mb-1">Maid</label>
                        <select id="maidSelect"
                                name="maid"
                                required
                                class="form-select form-select-sm form-select-solid">
                            {{-- options loaded via AJAX --}}
                        </select>
                    </div>

                    {{-- Note --}}
                    <div class="col-12 col-lg-4">
                        <label for="note" class="form-label fw-semibold mb-1">Note</label>
                        <input type="text"
                               id="note"
                               name="note"
                               required
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    {{-- Deduction / Allowance --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="deduction" class="form-label fw-semibold mb-1">Deduction</label>
                        <input type="number"
                               id="deduction"
                               name="deduction"
                               class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="allowance" class="form-label fw-semibold mb-1">Allowance</label>
                        <input type="number"
                               id="allowance"
                               name="allowance"
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    <div class="col-12 d-flex justify-content-end pt-4">
                        <button type="submit" class="btn btn-primary px-10">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ───────── Advance / Allowance list ───────── --}}
        <div class="card card-flush shadow-sm">
            <!-- Card header with table title + date range filters -->
            <div class="card-header d-flex flex-wrap align-items-center gap-4">
                <h5 class="card-title mb-0">Advance / Allowances</h5>

                <div class="ms-auto d-flex gap-4">
                    <input type="date"
                           id="min-date"
                           class="form-control form-control-sm form-control-solid"
                           placeholder="From Date">
                    <input type="date"
                           id="max-date"
                           class="form-control form-control-sm form-control-solid"
                           placeholder="To Date">
                </div>
            </div>

            <!-- DataTable -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="advance_datatable"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th>For M-Y</th>
                                <th>Maid</th>
                                <th>Note</th>
                                <th class="text-end">Deduction</th>
                                <th class="text-end">Allowance</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- filled by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Advance / Allowance list-->

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->

{{-- Select2 init --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    $('#maidSelect').select2({
        placeholder: 'Search for a maid',
        minimumInputLength: 1,
        ajax: {
            url: '/all/maids',
            dataType: 'json',
            delay: 250,
            data: params => ({
                search: params.term,
                page: params.page || 1
            }),
            processResults: (data, params) => ({
                results: data.items,
                pagination: {
                    more: (params.page * 30) < data.total_count
                }
            }),
            cache: true
        },
        allowClear: true,
        dropdownParent: $('#entryContainer')   // keeps dropdown inside the card
    });
});
</script>

{{-- Page-specific JS --}}
@vite('resources/js/maid_payroll/advance_maid.js')
@endpush
@endsection
