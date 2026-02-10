@extends('keen')
@section('content')

<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Journal card-->
        <div class="card card-flush shadow-sm">
            <div class="card-header align-items-center py-5 gap-4">
                <div class="card-title">
                    <h2>General Journal Vouchers</h2>
                </div>

                <div class="card-toolbar flex-wrap gap-2">
                    <form action="{{ route('export.journal') }}" method="GET" class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-light-primary d-flex align-items-center">
                            <i class="ki-duotone ki-upload fs-2 me-2"></i> Export
                        </button>

                        <div>
                            <label for="from" class="form-label mb-0 small">Export From</label>
                            <input id="from" name="from" type="date" class="form-control form-control-sm form-control-solid" required>
                        </div>

                        <div>
                            <label for="to" class="form-label mb-0 small">Export To</label>
                            <input id="to" name="to" type="date" class="form-control form-control-sm form-control-solid" required>
                        </div>
                    </form>

                    <div>
                        <label for="min-date" class="form-label mb-0 small">Filter From</label>
                        <input id="min-date" type="date" class="form-control form-control-sm form-control-solid">
                    </div>

                    <div>
                        <label for="max-date" class="form-label mb-0 small">Filter To</label>
                        <input id="max-date" type="date" class="form-control form-control-sm form-control-solid">
                    </div>

                    <div>
                        <label for="filterVoucher" class="form-label mb-0 small">Voucher</label>
                        <select id="filterVoucher" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option>Payment Voucher</option>
                            <option>Receipt Voucher</option>
                            <option>Invoice Package1</option>
                            <option>Invoice Package4</option>
                            <option>Typing Invoice</option>
                            <option>New Arrival</option>
                            <option>Credit Note</option>
                            <option value="debit_memo">Debit Memo</option>
                            <option value="invoice">Non-contract Invoice</option>
                            <option>Journal Voucher</option>
                            <option>Opening Balance</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-primary d-flex align-items-center ms-auto" data-bs-toggle="modal" data-bs-target="#add_journal_voucher">
                        <i class="ki-duotone ki-plus fs-2 me-2"></i> Add Voucher
                    </button>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="general-journal-voucher-table" class="table align-middle table-row-dashed fs-6 gy-5 w-100">
                        <thead class="text-muted fw-bold fs-7 text-uppercase gs-0">
                            <tr>
                                <th hidden>ID</th>
                                <th>Date</th>
                                <th>Voucher Type</th>
                                <th>Voucher Code</th>
                                <th>Maid</th>
                                <th>Account</th>
                                <th>Note</th>
                                <th>Txn Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows go here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Journal card-->

    </div>
</div>



{{-- ─────────────────────────────  ADD Journal Voucher MODAL  ───────────────────────────── --}}
@component('ERP.components.modal', [
    'modal_id'     => 'add_journal_voucher',
    'dataBackDrop' => 'true',
    'title'        => 'Add Journal Voucher'
])
<div>
    <form id="journalVoucherForm" class="px-3" enctype="multipart/form-data">
        @csrf

        {{-- Voucher Type --}}
        <div class="mb-3">
            <label for="voucher_type" class="form-label fw-semibold text-danger small">Voucher type</label>
            <select id="voucher_type" name="voucher_type" class="form-select form-select-sm" required>
                <option value="" selected disabled>Select Voucher</option>
                <option>Payment Voucher</option>
                <option>Receipt Voucher</option>
                <option>Journal Voucher</option>
                <option>Opening Balance</option>
                <option>Credit note</option>
                <option value="debit_memo">Debit memo</option>
                <option value="invoice">invoice</option>
                <option>Typing Invoice</option>
                <option>Invoice Package4</option>
                <option>Invoice Package1</option>
            </select>
        </div>

        {{-- File --}}

                <div class="col-12">
                  
                 <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">

                </div>


        {{-- Recurring + Add Entry --}}
        <div class="row gx-2 gy-2 align-items-end">
            <div class="col-md-8">
                <label for="RecurringNumber" class="form-label small">Recurring Number</label>
                <input id="RecurringNumber" name="Recurring" type="number" value="1" class="form-control form-control-sm">
            </div>
            <div class="col-md-4 text-md-end">
                <button type="button" id="addEntryButton" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-plus-circle me-1"></i> Add Entry
                </button>
            </div>
        </div>

        {{-- Dynamic Entries --}}
        <div id="entryContainer" class="my-3"></div>

        {{-- Connection --}}
        <div class="mb-3">
            <label for="connectionSelect" class="form-label small">Connection</label>
            <select id="connectionSelect" name="connection" class="form-select form-select-sm"></select>
        </div>

        {{-- Reference + Date --}}
        <div class="row gx-2 gy-2 mb-3 row-cols-1 row-cols-md-2">
            <div>
                <label for="refNumber" class="form-label small">Reference number</label>
                <input id="refNumber" name="refNumber" type="number" value="{{ $maxrefNumber }}" class="form-control form-control-sm" readonly>
            </div>
            <div>
                <label for="date" class="form-label small">Date</label>
                <input id="date" name="date_jv" type="date" value="{{ $currentDate }}" class="form-control form-control-sm">
            </div>
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-between mb-3 fw-bold small">
            <span>Total Debit: <span id="totalDebit" class="text-danger">0.00</span></span>
            <span>Total Credit: <span id="totalCredit" class="text-success">0.00</span></span>
        </div>

        {{-- Submit --}}
      <button type="submit" id="submit_add_journal_Voucher" class="btn btn-success btn-sm w-100">Submit</button>

    </form>
</div>
@endcomponent

{{-- ─────────────────────────────  EDIT Journal Voucher MODAL  ───────────────────────────── --}}
@component('ERP.components.modal', [
    'modal_id'     => 'edit_journal_voucher',
    'dataBackDrop' => 'true',
    'title'        => 'Edit Journal Voucher',
    'modal_class'  => 'modal-xl'
])
<div>
    <form id="editJournalVoucherForm" class="px-3" enctype="multipart/form-data">
        @csrf

        {{-- Meta Fields --}}
        <div class="row gx-2 gy-2 mb-3 row-cols-1 row-cols-md-3">
            <div>
                <label class="form-label small">Voucher type</label>
                <select id="edit_voucher_type" name="voucher_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option>Payment Voucher</option>
                    <option>Receipt Voucher</option>
                    <option>Invoice Package1</option>
                    <option>Invoice Package4</option>
                    <option>Typing Invoice</option>
                    <option>New arrival</option>
                    <option>Credit note</option>
                    <option value="debit_memo">Debit memo</option>
                    <option value="invoice">invoice</option>
                    <option>Journal Voucher</option>
                    <option>Opening Balance</option>
                </select>
            </div>
            <div>
                <label class="form-label small">Reference number</label>
                <input name="refNumber" type="text" class="form-control form-control-sm" readonly>
            </div>
            <div>
                <label class="form-label small">Date</label>
                <input name="date" type="date" class="form-control form-control-sm">
            </div>
        </div>

        {{-- Details Table --}}
        <div class="table-responsive mb-3">
            <table id="voucher-details-table" class="table table-sm table-striped align-middle edit-voucher-table">
                <thead class="table-light small">
                    <tr>
                        <th style="width:9%">ID</th>
                        <th style="width:12%">Type</th>
                        <th style="width:20%">Account</th>
                        <th style="width:20%">Maid</th>
                        <th style="width:12%">Amount</th>
                        <th style="width:12%">Invoice balance</th>
                        <th style="width:20%">Notes</th>
                        <th style="width:12.5%">Created At</th>
                        <th style="width:12.5%">Updated At</th>
                    </tr>
                </thead>
                <tbody><!-- dynamic rows --></tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-between fw-bold mb-3 small">
            <span>Total Debit: <span id="edit_total_Debit" class="text-danger">0.00</span></span>
            <span>Total Credit: <span id="edit_total_credit" class="text-success">0.00</span></span>
        </div>

        {{-- Footer --}}
        <div class="text-center mb-2 text-muted small">
            &copy; {{ date('Y') }} Homecare.
        </div>
                 <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx ,.zip" >


        <button id="submit_edit_jv" class="btn btn-success btn-sm w-100">Submit</button>
    </form>
</div>
@endcomponent

@endsection

@push('scripts')
<script>
(function() {
    let isModalOpen = false;

    // Listen for modal open/close to track state
    const addJournalModalEl = document.getElementById('add_journal_voucher');
    const addJournalModal = new bootstrap.Modal(addJournalModalEl);

    addJournalModalEl.addEventListener('shown.bs.modal', function() {
        isModalOpen = true;
    });
    addJournalModalEl.addEventListener('hidden.bs.modal', function() {
        isModalOpen = false;
    });

    document.addEventListener('keydown', function(event) {

        if (
            event.key.toLowerCase() === 'm' &&
            !event.target.closest('input, textarea, select') &&
            !isModalOpen
        ) {
            event.preventDefault();
            addJournalModal.show();

        }
    });


})();
</script>

</script>

    @vite('resources/js/accounts/journal_voucher.js')
@endpush
