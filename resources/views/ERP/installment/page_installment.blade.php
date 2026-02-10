@extends('keen')
@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Filter card-->
        <div class="card shadow-sm mb-8">
            <div class="card-header ">
                <h5 class="card-title mb-0">Filter&nbsp;Contracts</h5>
            </div>

            <div class="card-body">
                <div class="row gx-5 gy-4">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="min-date" class="form-label fw-semibold mb-1">From&nbsp;Date</label>
                        <input type="date"
                               id="min-date"
                               class="form-control form-control-sm form-control-solid" />
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="max-date" class="form-label fw-semibold mb-1">To&nbsp;Date</label>
                        <input type="date"
                               id="max-date"
                               class="form-control form-control-sm form-control-solid" />
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="filterContractType" class="form-label fw-semibold mb-1">Maid&nbsp;Type</label>
                        <select id="filterContractType"
                                class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="HC">House Maid Center</option>
                            <option value="Direct hire">Package&nbsp;5&nbsp;contract</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button id="bulk-generate"
                        class="btn btn-primary px-10">
                    Generate&nbsp;Selected&nbsp;Invoices
                </button>
            </div>
        </div>
        <!--end::Filter card-->

        <!--begin::Upcoming installments card-->
        <div class="card card-flush shadow-sm">
            <div class="card-header bg-dark py-4">
                <h5 class="card-title text-white mb-0">Upcoming&nbsp;Installments</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="table_installment"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th style="width:3rem;">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>Accrued&nbsp;Date</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Maid</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th>Cheque</th>
                                <th>Contract</th>
                                <th class="text-end">Amount</th>
                                <th>Created&nbsp;By</th>
                                <th class="text-end">Action</th>
                                <th>Custom</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data rows rendered by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Upcoming installments card-->

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->



      <!-- Modal for Customized Invoice -->
    <div class="modal fade" id="custom-modal" tabindex="-1" aria-labelledby="typingPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="typingPaymentModalLabel">Customized Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <form id="customized-form">
                        <div class="mb-3">
                            <label for="id_installment" class="form-label">ID</label>
                            <input type="text" class="form-control" name="id_ins" id="id_installment" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="date_installment" class="form-label">Accrued Date</label>
                            <input type="date" class="form-control" name="date" id="date_installment" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="contract_ref" class="form-label">Contract</label>
                            <input type="text"  class="form-control" name="contract_ref" id="contract_ref" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="maid_name" class="form-label">Maid</label>
                            <input type="text" class="form-control" name="maid" id="maid_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <input type="text" class="form-control" name="note" id="note">
                        </div>
                        <div class="mb-3">
                            <label for="cheque" class="form-label">Cheque</label>
                            <input type="text" class="form-control" name="cheque" id="cheque">
                        </div>
                        <div class="mb-3">
                            <label for="maid_salary" class="form-label">Salary</label>
                            <input type="number" class="form-control" name="salary" id="maid_salary">
                        </div>
                        <div class="mb-3">
                            <label for="net_profit" class="form-label">Net Profit</label>
                            <input type="number" class="form-control" name="net_profit" id="net_profit">
                        </div>
                        <div class="mb-3">
                            <label for="customer_installment" class="form-label">Customer</label>
                            <input type="text" class="form-control" name="customer" id="customer_installment" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="maid_amount" class="form-label">Total Invoice Amount</label>
                            <input type="number" class="form-control" name="amount" id="total_amount" readonly>
                        </div>
                        <input type="hidden" name="id" id="id">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="btn-submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/installment/upcoming_installment.js')
@endpush
