@extends('keen')
@section('content')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        {{-- ───────── Filter card ───────── --}}
        <div class="card shadow-sm mb-8">
            <div class="card-body">
                <div class="row gx-5 gy-4 align-items-end">
                    {{-- Date From --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="min-date" class="form-label fw-semibold mb-1">Date&nbsp;From</label>
                        <input type="date"
                               id="min-date"
                               name="dateFrom"
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    {{-- Date To --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="max-date" class="form-label fw-semibold mb-1">Date&nbsp;To</label>
                        <input type="date"
                               id="max-date"
                               name="dateTo"
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    {{-- Payment Way --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="paymentWay" class="form-label fw-semibold mb-1">Payment&nbsp;Way</label>
                        <select id="paymentWay"
                                class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        {{-- ───────── /Filter card ───────── --}}

        {{-- ───────── Payroll table ───────── --}}
        <div class="card card-flush shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="payroll-maids"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th>For&nbsp;Month</th>
                                <th>Maid</th>
                                <th class="text-end">Basic&nbsp;Salary</th>
                                <th>Maid&nbsp;Type</th>
                                <th>MOL</th>
                                <th>Branch</th>
                                <th class="text-end">Working&nbsp;Days</th>
                                <th>Status&nbsp;on&nbsp;Paid</th>
                                <th>Payment&nbsp;Way</th>
                                <th class="text-end">Deduction</th>
                                <th class="text-end">Allowance</th>
                                <th>Note</th>
                                <th class="text-end">Net&nbsp;Salary</th>
                                <th>Created&nbsp;By</th>
                                <th>Created&nbsp;At</th>
                                <th class="text-end">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- filled by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- ───────── /Payroll table ───────── --}}

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->

@endsection

@push('scripts')
    @vite('resources/js/maid_payroll/paid_maid.js')
@endpush
