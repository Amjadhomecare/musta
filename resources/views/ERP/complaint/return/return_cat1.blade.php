@extends('keen')
@section('content')

<div class="container mt-4">
    <!-- Filters Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body" ">
      
            <div class="row g-3">
                <div class="col-lg-3">
                    <label for="filterApproval" class="form-label">Filter Approval:</label>
                    <select id="filterApproval" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="No">Not</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label for="filterRefund" class="form-label">Filter Refund:</label>
                    <select id="filterRefund" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="No Data">Pending</option>
                        <option value="Old ERP">Old ERP</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label for="min-date" class="form-label">Start Date:</label>
                    <input type="date" id="min-date" class="form-control form-control-sm">
                </div>

                <div class="col-lg-3">
                    <label for="max-date" class="form-label">End Date:</label>
                    <input type="date" id="max-date" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body" ">

            <div class="table-responsive">
                <table id="return-list-cat4-datatable" class="table table-bordered  table-hover">
                    <thead class="table">
                        <tr>
                            <th><input type="checkbox" id="check-all"></th> 
                            <th>Approved</th>
                            <th>Refund</th>
                            <th>Return at</th>
                            <th>Maid</th>
                            <th>Contract</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Invoice Date</th>
                            <th>Invoice Amount</th>
                            <th>Closing</th>
                            <th>Reason</th>
                            <th>Returned by</th>
                            <th>Checked by</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be dynamically populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (auth()->user()->group === 'accounting')
        <div class="text-end mt-3">
            <button id="bulk-update-approval" class="btn btn-success">Update Approvals</button>
        </div>
    @endif
</div>

@endsection

@push('scripts')
    @vite(['resources/js/complain/return_p1.js'])
@endpush
