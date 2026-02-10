@extends('keen')
@section('content')


<style>


</style>
<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">
                        

                <table id="jv_bulk" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ref</th>
                            <th>voucher</th>
                            <th>Account</th>
                            <th>post_type</th>
                            <th>note</th>
                            <th>amount</th>
                            <th>Action</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="transactionForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Total Summary Section -->
                <div class="total-summary mt-3 d-flex justify-content-between align-items-center px-4 py-3 bg-light border-bottom">
                    <div class="total-item">
                        <strong>Total Debit:</strong> <span id="totalDebit">0</span>
                    </div>
                    <div class="total-item">
                        <strong>Total Credit:</strong> <span id="totalCredit">0</span>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" id="modalContent">
                    <!-- Content will be populated dynamically -->
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add transaction</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
    .total-summary {
        font-size: 1.2rem; 
    }
    .total-item {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        border-right: 1px solid #ccc; 
    }
    .total-item:last-child {
        border-right: none; 
    }
</style>


<div class="modal fade" id="deleteBulkModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="deleteRef" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center mb-3">Are you sure you want to delete this reference?</p>
                    <div class="mb-3">
                        <label for="refInput" class="form-label">Reference ID</label>
                        <input type="text" class="form-control" id="refInput" name="ref_delete" readonly>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection


@push('scripts')
    @vite('resources/js/accounts/jv_bulk.js')
@endpush

