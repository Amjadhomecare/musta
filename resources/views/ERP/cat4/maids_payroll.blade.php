@extends('keen')
@section('content')

<div class="container">

<label class="block text-gray-700 text-sm font-bold mb-2" for="paymentWay">
            <select id="maidStatus" class="block appearance-none  bg-white border border-gray-200 text-gray-700 py-2 px-2 pr-4 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">Select Naid Status</option>
                <option value="approved">approved</option>
                <option value="hired">hired</option>
            </select>
        </label>



        <label class="block text-gray-700 text-sm font-bold mb-2" for="paymentWay">
            <select id="maidType" class="block appearance-none  bg-white border border-gray-200 text-gray-700 py-2 px-2 pr-4 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">Maid Type</option>
                <option value="HC">Home care</option>
                <option value="direct hire">direct hire</option>
            </select>
        </label>

        <label class="block text-gray-700 text-sm font-bold mb-2" for="paymentWay">
            <select id="paymentWay" class="block appearance-none  bg-white border border-gray-200 text-gray-700 py-2 px-2 pr-4 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">Select Payment Way</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
            </select>
        </label>

        <select id="days" class="block appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-2 pr-4 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
            <option value="">Select days</option>
            <option value="lessThan28">Less than 28</option>
            <option value="moreThanEqual28">28 days and more</option>
        </select>

        <label class="block text-gray-700 text-sm font-bold mb-2" for="paidStatus">
            <select id="paidStatus" class="block appearance-none bg-white border border-gray-200 text-gray-700 py-2 px-2 pr-4 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
                <option value="">Select Paid Status</option>
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
            </select>
        </label>


        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="filterCheckboxNoRemark">
            <label class="form-check-label" for="filterCheckboxNoRemark">
                Filter Empty Notes & Books
            </label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="filterCheckboxRemark">
            <label class="form-check-label" for="filterCheckboxRemark">
                Filter With Remarks
            </label>
        </div>

        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="filterCheckboxBooked">
            <label class="form-check-label" for="filterCheckboxBooked">
                Filter With Booked
            </label>
        </div>



    
        <div class="container-fluid py-4">

        
        <button id="bulkSaveButton" class="btn btn-success">Bulk generate payroll</button>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Maid Payrolls</h5>
            <div>
                <input type="checkbox" id="selectAllMaids" class="form-check-input me-2">
                <label for="selectAllMaids" class="form-check-label">Select All</label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="maidsDataTable">
                <thead class="thead-light">
                    <tr>
                        <th></th>
                        <th>Maid Name</th>
                        <th>Basic Salary</th>
                        <th>Maid Type</th>
                        <th>Total Days</th>
                        <th>Status</th>
                        <th>Paid</th>
                        <th>Payment Method</th>
                        <th>Latest Contract</th>
                        <th>Status note</th>
                        <th>Customer</th>
                        <th>Deduction</th>
                        <th>Allowance</th>
                        <th>Note</th>
                        <th>Net</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic data rows go here -->
                </tbody>
            </table>
        </div>
    </div>
</div>


</div>


<!-- Modal -->
<div id="maid-dedction" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
            <form id="maidDeductionForm" class="px-3">
        @csrf

            <div class="form-group">
                <input type="hidden" id="idForDeduction" name="advanceDataId">
            </div>

            <div class="form-group">
                <label for="deductionInput">Deduction Amount</label>
                <input type="number" class="form-control" id="deductionInput" name="deductionMaid" placeholder="Enter deduction amount">
            </div>

            <div class="form-group">
                <label for="allowanceInput">Allowance Amount</label>
                <input type="number" class="form-control" id="allowanceInput" name="allowanceMaid" placeholder="Enter allowance amount">
            </div>

            <div class="form-group">
                <label for="noteInput">Note</label>
                <input type="text" class="form-control" id="noteInput" name="noteMaid" placeholder="Enter note">
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
         
        </form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->




@endsection



@push('scripts')
    @vite('resources/js/maid_payroll/maid_payroll.js')
@endpush
