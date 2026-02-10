@extends('keen')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm border-0 mb-4">
 
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-2">
                    <label for="filterApproval" class="form-label">Filter status:</label>
                    <select id="filterApproval" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="No">Not</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-4">
                    <label for="min-date" class="form-label">From Date:</label>
                    <input type="date" id="min-date" class="form-control" />
                </div>
                
                <div class="col-lg-3 col-md-4">
                    <label for="max-date" class="form-label">To Date:</label>
                    <input type="date" id="max-date" class="form-control" />
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="return-list-cat4-datatable" class="table table-bordered table-hover text-center" style="width:100%">
                    <thead class="table">
                        <tr>
                            <th><input type="checkbox" id="check-all"></th>
                            <th>Approved</th>
                            <th>Maid Start at</th>
                            <th>returnd at</th>
                            <th>Maid</th>
                            <th>Contract</th>
                            <th>Customer</th>
                            <th>Closing Balance</th>
                            <th>Latest Invoice</th>
                            <th>Reason</th>
                            <th>Old</th>
                            <th>New</th>
                            <th>Returned By</th>
                            <th>Checked By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic rows here -->
                    </tbody>
                </table>
            </div>
            @if (auth()->user()->group === 'accounting') 
            <div class="d-flex justify-content-end mt-3">
                <button id="bulk-update-approval" class="btn btn-primary">Update Approvals</button>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/complain/return_p4.js'])
@endpush
