@extends('keen')
@section('content')






<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Card-->
        <div class="card card-flush shadow-sm mb-10">
            <!--begin::Card header-->
            <div class="card-header border-0 py-5">
                <div class="card-title w-100">
                    <div class="row g-3 align-items-end w-100">
                        <!--begin::Date picker : From-->
                        <div class="col-md-4">
                            <label for="min-date" class="form-label fw-semibold">From</label>
                            <input type="date" id="min-date" class="form-control form-control-solid form-control-sm" placeholder="YYYY-MM-DD" />
                        </div>
                        <!--end::Date picker : From-->

                        <!--begin::Date picker : To-->
                        <div class="col-md-4">
                            <label for="max-date" class="form-label fw-semibold">To</label>
                            <input type="date" id="max-date" class="form-control form-control-solid form-control-sm" placeholder="YYYY-MM-DD" />
                        </div>
                        <!--end::Date picker : To-->

                                    <!--begin::Status filter -->
                    <div class="col-md-4">
                    <label for="invoice-balance" class="form-label fw-semibold">Status</label>
                    <select id="invoice-balance" class="form-select form-select-solid form-select-sm"  data-hide-search="true">
                        <option value="">All</option>
                        <option value="zero">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                    </select>
                    </div>
                    <!--end::Status filter -->

                        <!--begin::Toolbar-->
                <div class="col-auto ms-auto d-flex flex-wrap gap-2">
                    <button type="button" id="btn_id" 
                            class="btn btn-light-primary d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#non_contract_add_transactions">
                        <i class="fas fa-plus-circle fs-6 me-2"></i>
                        <span>Add Transaction</span>
                    </button>

                    <button id="apply-credit-bulk" 
                            class="btn btn-light-primary d-flex align-items-center">
                        <i class="fas fa-check-circle fs-6 me-2"></i>
                        <span>Apply Credit</span>
                    </button>
                </div>
                <!--end::Toolbar-->
                        <!--end::Toolbar-->
                    </div>
                </div>
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table id="non_contract" class="table align-middle table-row-dashed fs-6 gy-5 gs-5 w-100">
                        <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th class="min-w-100px">Date</th>
                                <th class="min-w-125px">Reference</th>
                                <th class="min-w-150px">Customer</th>
                                <th class="min-w-150px">Maid</th>
                                <th class="min-w-150px">Service</th>
                                <th class="min-w-125px text-end">Total Invoice</th>
                                <th class="min-w-150px text-end">Invoice Balance</th>
                                <th class="min-w-200px">Note</th>
                                <th class="min-w-150px">Payment Status</th>
                                <th class="min-w-125px">Latest Receipt</th>
                                <th class="min-w-125px">Credit Note</th>
                                <th class="min-w-125px">Created At</th>
                                <th class="min-w-150px">Created By</th>
                                <th class="min-w-100px text-end">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content wrapper-->





<!-- Modal -->
<div id="non_contract_add_transactions" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editUsersLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsersLabel">Non contract invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nonContractTransactionsForm"  class="px-3">
                    @csrf
                    <div class="row mb-3">
                        <label for="voucher_type" class="col-sm-2 col-form-label">Voucher type:</label>
                        <div class="col-sm-10">
                            <select class="form-select" id="voucher_type" name="typing_invoice" required>
                                <option selected value="non-contract">non-contract</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-2">
                            <label for="connectionSelect" class="col-sm-2 col-form-label">Select Services:</label>
                            <div class="col-sm-10">
                                <select id="connectionSelect" class="form-control" name="connection_service"></select>
                            </div>
                      </div>

                    <div class="row mb-2">
                        <label for="selected_customer" class="col-sm-2 col-form-label">Select Customer:</label>
                        <div class="col-sm-10">
                            <select id="selected_customer" class="form-control" name="selected_customer"></select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="selected_maid" class="col-sm-2 col-form-label">Select Maid:</label>
                        <div class="col-sm-10">
                            <select id="selected_maid" class="form-control" name="maid"></select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="refNumber" class="form-label">Reference Number:</label>
                            <input type="text" value="" id="refNumber" name="refNumber" class="form-control" readonly>
                        </div>
                   
                        <div class="col">
                            <label for="date" class="form-label">Date:</label>
                            <input type="date" id="date" value="{{$currentDate}}" name="date_jv" class="form-control"  readonly >
                        </div>
                      
                    </div>
                    <div id="entryContainer" class="mb-3"></div>
                    
                    <div class="col mb-2">
                        <div name="total_of_invoice" class="fw-bold">Total Credit:
                            <input type="number" id="totalCredit" name="total_invoice" class="text-success form-control" readonly>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-primary'>Add Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="payment-modal-inv" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="paymentForm" class="px-3">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="dateInput">Date</label>
                        <input type="date" id="dateInput" class="form-control" value='{{$currentDate}}' name='date'>
                    </div>
                    <input type="hidden" name="transactionID" id='idInput'>
                    <div class="form-group mb-3">
                        <label for="ref_code">Reference Code</label>
                        <input readOnly type='text' name="refInv" id='ref_code'>
                    </div>
                    <div class="form-group mb-3">
                        <label for="accountInput">Customer Name</label>
                        <input readOnly type="text" id="accountInput" class="form-control" name='customerName'>
                    </div>
                    <div class="form-group mb-3">
                        <label for="maid_nameInput">Maid name</label>
                        <input readOnly type="text" id="maid_nameInput" class="form-control" name='maidName'>
                    </div>
                    <div class="form-group mb-3">
                        <label for="receivedPaymentSelect">Received Payment</label>
                        <select name="receivedFromLedger" id="receivedPaymentSelect" class="form-select">
                            @foreach($cashAndBank as $payment)
                            <option value="{{$payment->ledger}}">{{$payment->ledger}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="amountReceivedInput">Amount Received</label>
                        <input type="number" id="amountReceivedInput" class="form-control" name='amountReceived'>
                    </div>
                    <div class="form-group mb-3">
                        <label for="noteInput">Note</label>
                        <input type="text" id="noteInput" class="form-control" name='note'>
                    </div>
                    <button type='submit' class='btn btn-primary'>Add Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal For Credit Note -->
<div id="credit-note-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="creditNoteForm" class="px-3">
                    @csrf 
                    <div id="creditNoteModalContent">        
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Credit Note</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



@endsection

@push('scripts')
    @vite(['resources/js/non_contract_inv/non_contract.js'])
@endpush
