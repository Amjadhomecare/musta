@extends('keen')
@section('content')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Filters + Add button card-->
        <div class="card shadow-sm mb-7 border-0">
            <div class="card-body d-flex flex-wrap flex-stack align-items-center p-4">

                <!-- Add Receipt Voucher -->
                <button type="button"
                        class="btn btn-primary btn-lg px-5 rounded-pill"
                        data-bs-toggle="modal"
                        data-bs-target="#cashierReceiptVoucherModal">
                    <i class="bi bi-plus-lg me-2"></i>Add Receipt Voucher
                </button>

                <!-- Date filters -->
                <div class="d-flex flex-wrap align-items-end gap-4">
                    <div>
                        <label for="min-date" class="form-label fw-semibold mb-1">From&nbsp;Date</label>
                        <input type="date"
                               id="min-date"
                               class="form-control form-control-sm form-control-solid rounded-pill"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div>
                        <label for="max-date" class="form-label fw-semibold mb-1">To Date</label>
                        <input type="date"
                               id="max-date"
                               class="form-control form-control-sm form-control-solid rounded-pill"
                               placeholder="YYYY-MM-DD">
                    </div>
                </div>
            </div>
        </div>
        <!--end::Filters + Add button card-->

        <!--begin::DataTable card-->
        <div class="card card-flush shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="general-journal-voucher-table"
                           class="table table-hover table-bordered table-row-dashed fs-6 gy-5 gs-5 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th hidden>ID</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Voucher&nbsp;Code</th>
                                <th>Maid</th>
                                <th>Account</th>
                                <th>Note</th>
                                <th>Txn&nbsp;Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!--end::DataTable card-->

    </div>
    <!--end::Content container-->
</div>
<!--end::Content wrapper-->


<!-- Modal Structure -->
<div class="modal fade" id="cashierReceiptVoucherModal" tabindex="-1" aria-labelledby="cashierReceiptVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-secondary text-white rounded-top">
                <h5 class="modal-title" id="cashierReceiptVoucherModalLabel">Cashier Receipt Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="receiptVoucherForm" class="p-3">
                    <!-- Debit Ledger -->
                    <div class="mb-4">
                        <label for="debitLedger" class="form-label fw-bold">Debit Ledger</label>
                        <select class="form-select  border border-dark rounded" id="debitLedger" name="debit_ledger" style="width: 100%;">
                            <option value="" disabled selected>Select Debit Ledger</option>
                        </select>
                    </div>

                    <!-- Credit Ledger -->
                    <div class="mb-4">
                        <label for="creditLedger" class="form-label fw-bold">Credit Ledger</label>
                        <select class="form-select  border border-dark rounded" id="selected_ledger" name="credit_ledger" style="width: 100%;">
                            <option value="" disabled selected>Select Credit Ledger</option>
                        </select>
                    </div>

                    <!-- Maid Name -->
                    <div class="mb-4">
                        <label for="maidName" class="form-label fw-bold">Maid Name</label>
                        <select class="form-select  border border-dark rounded" id="maidName" name="maid_name" style="width: 100%;">
                            <option value="" disabled selected>Select Maid</option>
                        </select>
                    </div>

                    <!-- Note Input -->
                    <div class="mb-4">
                        <label for="note" class="form-label fw-bold">Note</label>
                        <textarea class="form-control border border-dark rounded" id="note" name="note" rows="3" placeholder="Add any note"></textarea>
                    </div>

              <!-- Amount Received -->
<div class="mb-4">
    <label for="amountReceived" class="form-label fw-bold">Amount Received</label>
    <input type="number" class="form-control border border-dark rounded" 
           id="amountReceived" 
           name="amount_received" 
           placeholder="Enter Amount" 
           step="any" 
           required>
</div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border rounded" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success shadow-sm rounded">Save Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
 @vite('resources/js/accounts/cashier.js')
@endpush
