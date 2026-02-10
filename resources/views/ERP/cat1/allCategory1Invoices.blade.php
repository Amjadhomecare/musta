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
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="min-date" class="form-label fw-semibold mb-1">From&nbsp;Date</label>
                        <input type="date"
                               id="min-date"
                               class="form-control form-control-sm form-control-solid"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="max-date" class="form-label fw-semibold mb-1">To&nbsp;Date</label>
                        <input type="date"
                               id="max-date"
                               class="form-control form-control-sm form-control-solid"
                               placeholder="YYYY-MM-DD">
                    </div>

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
             

                      <div class="d-flex justify-content-end mb-3">
                        <button id="apply-credit-bulk" class="btn btn-sm btn-primary">
                            Apply Credit to Selected
                        </button>
                    </div>

            </div>
        </div>
        {{-- ───────── /Filter card ───────── --}}

        {{-- ───────── Invoice table card ───────── --}}
        <div class="card card-flush shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="invoice-table"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                
                                <th>
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Contract</th>
                                <th>Customer</th>
                                <th>Maid</th>
                                <th>Service</th>
                                <th class="text-end">Total&nbsp;Invoice</th>
                                <th class="text-end">Invoice&nbsp;Balance</th>
                                <th>Note</th>
                                <th>Status</th>
                                <th>Latest&nbsp;Receipt</th>
                                <th>Credit&nbsp;Note</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- filled by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- ───────── /Invoice table card ───────── --}}

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->


<!-- Modal -->
<div id="payment-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="paymentForm" class="px-3">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="dateInput">Date</label>
                        <input type="date" id="dateInput" class="form-control" value='{{$date}}' name='date'>
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
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@push('scripts')
   @vite(['resources/js/p1/p1Invoice.js'])

@endpush
