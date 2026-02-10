@extends('keen')
@section('content')

@include('partials.nav_customer')


<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          Invoices&nbsp;for:&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Filter card ───────── --}}
    <div class="card shadow-sm mb-5">
      <div class="card-body">
        <div class="row gx-5 gy-4 align-items-end">
          <div class="col-sm-6 col-lg-4">
            <label for="filterV" class="form-label fw-semibold mb-1">Voucher&nbsp;Type</label>
            <select id="filterV" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="Typing Invoice">Typing Invoice</option>
              <option value="Invoice Package4">Invoice Package&nbsp;4</option>
              <option value="Invoice Package1">Invoice Package&nbsp;1</option>
            </select>
          </div>

          <!-- Add‑invoice button (right) -->
          <div class="col-auto ms-auto">
            <button type="button"
                    class="btn btn-primary btn-sm d-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#non_contract_add_transactions">
              <i class="fas fa-plus-circle me-2"></i>New&nbsp;Invoice&nbsp;{{ $name }}
            </button>
          </div>
        </div>
      </div>
    </div>
    {{-- ───────── /Filter card ───────── --}}

    {{-- ───────── Table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="datatable_invoices"
                 class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Ref</th>
                <th>Contract</th>
                <th>Customer</th>
                <th>Maid</th>
                <th>Service</th>
                <th class="text-end">Total&nbsp;Invoice</th>
                <th class="text-end">Invoice&nbsp;Balance</th>
                <th>Payment&nbsp;Status</th>
                <th>Note</th>
                <th>Latest&nbsp;Receipt</th>
                <th>Credit&nbsp;Note</th>
                <th>Created&nbsp;By</th>
                <th>Created&nbsp;At</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows injected here --></tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- ───────── /Table card ───────── --}}

  </div><!-- /container-xxl -->
</div><!-- /content wrapper -->

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

    @component('ERP.components.modal',[
    'modal_id' => 'typing-credit-note-modal', 
    'dataBackDrop' => 'true', 
    'title' => 'Add Credit Transactions',
])

    <form id="creditNoteForm" class="px-3">
        @csrf


                <input 
                    type="text" 
                    class="form-control shadow-sm border border-primary rounded" 
                    id="note" 
                    name="note" 
                    placeholder="Enter your note"
                >

                <br>

        <div id="creditNoteModalContent">
            <!-- Updated input field -->
            <div class="mb-3">
      
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Credit Note</button>
    </form>



    

@endcomponent



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
                              <input type="text" id="selected_customer" class="form-control" name="selected_customer" value="{{$name}}" readonly>
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
                            <input type="date" id="date" value="{{date('Y-m-d') }}" name="date_jv" class="form-control"  readonly >
                        </div>
                      
                    </div>
                    <div id="entryContainer" class="mb-3"></div>
                    
                    <div class="col mb-2">
                        <div name="total_of_invoice" class="fw-bold">Total Credit:
                            <input type="number" id="totalCredit" name="total_invoice" class="text-success form-control" readonly>
                        </div>
                    </div>
                    <button type='submit' class='btn btn-blue'>Add Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>




@endsection

@push('scripts')
    @vite('resources/js/customers/invoices.js')
@endpush
