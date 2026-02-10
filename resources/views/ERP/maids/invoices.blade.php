@extends('keen')
@section('content')

@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container" style="max-width:100%;">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 text-center" id="maid-name" data-name="{{ $name }}">
          Invoices&nbsp;for&nbsp;Maid:&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Table card ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="datatable_invoices" class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Date</th>
                <th>Voucher&nbsp;Type</th>
                <th>Reference</th>
                <th>Customer</th>
                <th>Service</th>
                <th class="text-end">Total&nbsp;Invoice</th>
                <th class="text-end">Invoice&nbsp;Balance</th>
                <th>Payment&nbsp;Status</th>
                <th>Note</th>
                <th>Latest&nbsp;Receipt</th>
                <th>Credit&nbsp;Note</th>
                <th>Created&nbsp;By</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody><!-- populated via DataTables --></tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->

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
            <input 
                    type="text" 
                    class="form-control shadow-sm border border-primary rounded" 
                    id="note" 
                    name="note" 
                    placeholder="Enter your note"
                >

                <br>
            <div id="creditNoteModalContent">

            </div>

            <button type="submit" class="btn btn-primary">Submit Credit Note</button>
        </form>

    @endcomponent


@endsection

@push('scripts')
    @vite('resources/js/maid/invoices.js')
@endpush
