@extends('keen')
@section('content')

<!--begin::Content wrapper-->

<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">


    <!--begin::Filter card-->
    <div class="card shadow-sm mb-7">
        <div class="card-body">
            <div class="row gx-5 gy-4">
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <label for="min-date" class="form-label fw-semibold mb-1">From</label>
                    <input type="date" id="min-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <label for="max-date" class="form-label fw-semibold mb-1">To</label>
                    <input type="date" id="max-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
            </div>

            <!-- Add button bottom-right -->
            <div class="d-flex justify-content-end mt-5">
                <button type="button"
                        class="btn btn-primary btn-lg px-6 advance-modal-btn"
                        data-bs-toggle="modal" data-bs-target="#add-advance-modal">
                    Add&nbsp;Customer&nbsp;Advance
                </button>
            </div>
        </div>
    </div>
    <!--end::Filter card-->

    <!--begin::DataTable card-->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="advance_datatable" class="table table-hover table-row-dashed fs-6 gy-5 gs-5 w-100">
                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
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
                            <th>Created by</th>
                            <th>Updated by</th>
                            <th>Created at</th>
                            <th>Updated at</th>
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
    @vite('resources/js/customers/advance.js')
@endpush
