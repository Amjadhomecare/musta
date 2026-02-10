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
                            <input type="date" id="min-date" class="form-control form-control-solid form-control-sm" placeholder="YYYY-MM-DD">
                        </div>
                        <!--end::Date picker : From-->

                        <!--begin::Date picker : To-->
                        <div class="col-md-4">
                            <label for="max-date" class="form-label fw-semibold">To</label>
                            <input type="date" id="max-date" class="form-control form-control-solid form-control-sm" placeholder="YYYY-MM-DD">
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
                                <!--begin::Toolbar-->
            <!--begin::Toolbar-->
                <div class="col-auto ms-auto d-flex flex-wrap gap-2">
                    <button type="button" id="btn_id" 
                            class="btn btn-light-primary d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#add_transactions">
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="all_typing" class="table w-100 align-middle table-row-dashed fs-6 gy-5 gs-5">
                        <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                            <tr>

                               <th>
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th class="min-w-100px">Date</th>
                                <th class="min-w-125px">Reference</th>
                                <th class="min-w-150px">Customer</th>
                                <th class="min-w-150px">Service</th>
                                <th class="min-w-100px text-end">Total</th>
                                <th class="min-w-100px text-end">Balance</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-200px">Note</th>
                                <th class="min-w-125px">Receipt</th>
                                <th class="min-w-125px">Credit Note</th>
                                <th class="min-w-150px">Created By</th>
                                <th class="text-end min-w-100px">Action</th>
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

<!--begin::Modal-->

<!-- Add Transactions Modal -->
<div class="modal fade" id="add_transactions" tabindex="-1" role="dialog" aria-labelledby="add_transactionsLabel" aria-hidden="true" data-backdrop="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_transactionsLabel">Add Transactions</h5>
              
            </div>
            <div class="modal-body">
                <form id="transactionsForm" enctype="multipart/form-data" class="px-3">
                    @csrf
                    <!-- Voucher Type (Hidden) -->
                    <div class="form-group row mb-3" hidden>
                        <label for="voucher_type" class="col-sm-2 col-form-label">Voucher Type:</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="voucher_type" name="typing_invoice" required>
                                <option selected value="Typing Invoice">Typing Tax Invoice</option>
                            </select>
                        </div>
                    </div>

                           <!-- Connection Dropdown -->
                    <div class="form-group row mb-2">
                        <label for="connectionSelect" class="col-sm-2 col-form-label">Select Services:</label>
                        <div class="col-sm-10">
                            <select id="connectionSelect" class="form-control" name="connectionSelect"></select>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="form-group row mb-2">
                        <label for="selected_customer" class="col-sm-2 col-form-label">Select Customer:</label>
                        <div class="col-sm-10">
                            <select id="selected_customer" class="form-control" name="selected_customer"></select>
                        </div>
                    </div>

             

                    <!-- Reference Number and Date (Hidden) -->
                    <div class="form-group row mb-3">
                        <div class="col">
                            <input type="text" value="" id="refNumber" name="refNumber" class="form-control" hidden>
                        </div>
                        <div class="col">
                            <input type="date" id="date" value="{{$date}}" name="date_jv" class="form-control" hidden>
                        </div>
                    </div>

                    <!-- Entry Container -->
                    <div id="entryContainer" class="mb-3">
                        <!-- Initial account entry will be added here -->
                    </div>

                    <!-- Total Credit -->
                    <div class="form-group mb-1">
                        <label class="fw-bold">Total Credit:</label>
                        <input type="number" id="totalCredit" name="total_invoice" class="text-success form-control" readonly>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="add-transaction" class="btn btn-success">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>



    @component('ERP.components.modal',[ 'modal_id' => 'typing-payment-modal', 'dataBackDrop'=>'true', 'title'=>'Add payment', ] )
        <form id="paymentForm" class="px-3">
            @csrf

            <div class="form-group mb-3">
                <label for="dateInput">Date</label>
                <input type="date" id="dateInput" class="form-control" value='{{$date}}' name='date'>
            </div>
            <input type='hidden' name="transactionID" id='idInput'>


            <div class="form-group mb-3">
                <label for="customerNameInput">Invoice Referance</label>
                <input readOnly type="text" id="invRef" class="form-control" name='invRef'>
            </div>

            <div class="form-group mb-3">
                <label for="customerNameInput">Customer Name</label>
                <input readOnly type="text" id="customerNameInput" class="form-control" name='customerName'>
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
                <input type="text" id="amountReceivedInput" class="form-control" name='amountReceived'>
            </div>



            <div class="form-group mb-3">
                <label for="noteInput">Note</label>
                <input type="text" id="noteInput" class="form-control" name='note'>
            </div>

            <button type='submit' class='btn btn-primary'>Add Payment</button>
        </form>
    @endcomponent

    @component('ERP.components.modal',[ 'modal_id' => 'typing-credit-note-modal', 'dataBackDrop'=>'true', 'title'=>'Add Credit Transactions', ] )

        <form id="creditNoteForm" class="px-3">
            @csrf
            <div id="creditNoteModalContent">

            </div>

            <button type="submit" class="btn btn-primary">Submit Credit Note</button>
        </form>

    @endcomponent


@endsection

@push('scripts')
    @vite(['resources/js/typing/typing.js'])
@endpush

