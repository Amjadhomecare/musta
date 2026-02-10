@extends('keen')
@section('content')


@include('partials.nav_customer')

{{-- ───────── Date-range filter card ───────── --}}
<div class="container-xxl mt-5">
    <div class="card shadow-sm mb-8">
        <div class="card-header py-3">
            <h5 class="card-title mb-0">Filter by Date</h5>
        </div>
        <div class="card-body">
            <div class="row gx-5 gy-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <label for="min-date" class="form-label fw-semibold mb-1">From&nbsp;Date</label>
                    <input type="date" id="min-date" class="form-control form-control-sm form-control-solid">
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label for="max-date" class="form-label fw-semibold mb-1">To&nbsp;Date</label>
                    <input type="date" id="max-date" class="form-control form-control-sm form-control-solid">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ───────── Advance table card ───────── --}}
<div class="container-xxl">
    <div class="card card-flush shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h4 class="card-title mb-0 flex-grow-1 text-center" id="customer-name" data-name="{{ $name }}">
                All advance for: {{ $name }}
            </h4>

            <button type="button"
                    class="btn btn-primary btn-sm ms-auto advance-modal-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#advance-modal">
                <i class="bi bi-plus-circle me-1"></i>Add&nbsp;Advance
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="advance_datatable"
                       class="table table-hover table-row-dashed fs-6 w-100">
                    <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Maid</th>
                            <th>Method</th>
                            <th>Note</th>
                            <th class="text-end">Amount</th>
                            <th>Receipt</th>
                            <th>Received</th>
                            <th>Created&nbsp;By</th>
                            <th>Updated&nbsp;By</th>
                            <th>Created&nbsp;At</th>
                            <th>Updated&nbsp;At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div id="advance-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">


            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Add Customer Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="advanceForm" class="px-3">
                    @csrf

                 <!-- date -->
                <div class="form-group mb-3">
                        <label for="noteInput">Received date</label>
                        <input type="date"  name="date" class="form-control">
                 </div>
                        <!-- Select Customer -->
                <div class="form-group mb-3">
                    <label for="customerSelect">Customer</label>
                    <select id="customerSelect" name="customer" class="form-control" style="width: 100%;">
                    </select>
                </div>

                <!-- Select Maid -->
                <div class="form-group mb-3">
                    <label for="maidSelect">Maid</label>
                    <select id="maidSelect" name="maid" class="form-control" style="width: 100%;">
                    </select>
                </div>


                    <!-- Select Post Type -->
                    <div class="form-group mb-3">
                        <label for="postTypeSelect">Post Type</label>
                        <select id="postTypeSelect" name="post_type" class="form-control">
                            <option value="">Select Post Type</option>
                            <option value="FAB">FAB</option>
                            <option value="CBD">CBD</option>
                            <option value="stripe">stripe</option>
                            <option value="cash">cash</option>
                            <option value="visa card">visa card</option>
                        </select>
                    </div>

                    <!-- Note Input -->
                    <div class="form-group mb-3">
                        <label for="noteInput">Note</label>
                        <input type="text" id="noteInput" name="note" class="form-control" placeholder="Enter note">
                    </div>

                    <!-- Amount Input -->
                    <div class="form-group mb-3">
                        <label for="amountInput">Amount</label>
                        <input type="number" id="amountInput" name="amount" class="form-control" placeholder="Enter amount">
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Receive Advance Modal -->
<div id="receive-advance-modal" class="modal fade" tabindex="-1" aria-labelledby="receiveAdvanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="receiveAdvanceModalLabel">Receive Customer Advance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="receiveAdvanceForm">
                    @csrf
      
                    <input type="hidden" id="customerAdvanceId" name="customer_advance_id">
                    <div class="form-group mb-3">
                        <label for="noteInput">Received date</label>
                        <input type="date"  name="date" class="form-control">
                 </div>
           
                    <div class="form-group mb-3">
                        <label for="customerAdvanceId">Customer Name</label>
                        <input type="text" id="customerName" name="credit_ledger" class="form-control" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label for="maidAdvanceId">Maid Name</label>
                        <input type="text" id="maidName" name="maid_name" class="form-control" readonly>
                    </div>

                          
                 <div class="form-group mb-3">
                    <label for="receivedMehtod">method</label>
                    <select id="receivedMehtod" name="debit_ledger" class="form-control" style="width: 100%;">
                    </select>
                </div>

                    <div class="form-group mb-3">
                        <label for="advanceAmount">Advance Amount</label>
                        <input type="number" id="advanceAmount" name="advance_amount" class="form-control" readonly>
                    </div>

             
                    <div class="form-group mb-3">
                        <label for="receiveAmount">Receive Amount</label>
                        <input type="number" id="receiveAmount" name="amount_received" class="form-control" placeholder="Enter amount to receive">
                    </div>

              
                    <div class="form-group mb-3">
                        <label for="receiveNotes">Notes</label>
                        <textarea id="receiveNotes" name="note" class="form-control" rows="3" placeholder="Enter any additional notes"></textarea>
                    </div>
     
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-success">Confirm Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


                
@endsection



@push('scripts')
    @vite('resources/js/customers/adv.js')
@endpush
