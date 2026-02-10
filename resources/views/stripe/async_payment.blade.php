@extends('keen')
@section('content')
<!--begin::Content wrapper-->

<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
    <!--begin::Filters card-->
    <div class="card shadow-sm mb-7">
        <div class="card-body">
            <div class="row gx-5 gy-4 align-items-end">
                <!-- From Date -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="min-date" class="form-label fw-semibold mb-1">From</label>
                    <input type="date" id="min-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
                <!-- To Date -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="max-date" class="form-label fw-semibold mb-1">To</label>
                    <input type="date" id="max-date" class="form-control form-control-sm form-control-solid" placeholder="YYYY-MM-DD" />
                </div>
                <!-- Payment refunded -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="statusFilter" class="form-label fw-semibold mb-1">Refund Status</label>
                    <select id="statusFilter" class="form-select form-select-sm form-select-solid">
                        <option value="">All</option>
                        <option value="1">Full Refunded</option>
                        <option value="0">Not Refunded</option>
                    </select>
                </div>
                <!-- Charge status -->
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="status_pay" class="form-label fw-semibold mb-1">Charge Status</label>
                    <select id="status_pay" class="form-select form-select-sm form-select-solid">
                        <option value="">All</option>
                        <option value="succeeded">Succeeded</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <!-- Partial refund toggle -->
                <div class="col-12 col-lg-3 d-flex align-items-center pt-3">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" id="partial_refunded" />
                        <label class="form-check-label ms-2 fw-semibold" for="partial_refunded">Partial Refund only</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Filters card-->

    <!--begin::DataTable card-->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="async_stripe_payment" class="table table-hover table-row-dashed fs-6 gy-5 gs-5 w-100">
                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                        <tr>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Receipt</th>
                            <th>Created At</th>
                            <th>Billing Email</th>
                            <th>Billing Name</th>
                            <th>Customer ERP</th>
                            <th>Maid ERP</th>
                            <th>Sub ID</th>
                            <th>Action</th>
                            <th>Stripe ID</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--end::DataTable card-->

</div>
<!--end::Content container-->
```

</div>
<!--end::Content wrapper-->



<!-- Modal -->
<div class="modal fade" id="pay-stripe-modal" tabindex="-1" role="dialog" aria-labelledby="payStripeModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payStripeModalLabel">Stripe Payment Details</h5>
            </div>
            <div class="modal-body">
                <form id="stripe-details-form">
                    <div class="form-group">
                        <label for="stripe-id">ID</label>
                        <input type="text"  name="stripe_id" id="stripe-id" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="stripe-amount">Amount</label>
                        <input type="text" name="amount" id="stripe-amount" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="stripe-status">Status</label>
                        <input type="text" id="stripe-status" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="billing-name">Billing Name</label>
                        <input type="text" id="billing-name" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="stripe-created-at">Created At</label>
                        <input type="text" name="date" id="stripe-created-at" class="form-control" readonly>
                    </div>

                                   
                <div class="form-group mb-3">
                    <label for="customerSelect">Customer</label>
                    <select id="customerSelect" name="customer" class="form-control" style="width: 100%;">
                    </select>
                </div>

                
                <div class="form-group mb-3">
                    <label for="maidSelect">Maid</label>
                    <select id="maidSelect" name="maid" class="form-control" style="width: 100%;">
                    </select>
                </div>
                <div class="form-group text-right">
                        <button type="submit" class="btn btn-blue"> Pay</button>
            </div>

                </form>
            </div>
       
        </div>
    </div>
</div>

<button id="sync-charges" class="btn btn-blue">Sync Charges</button>



@endsection

@push('scripts')
    @vite(['resources/js/stripe/payment.js'])
@endpush






