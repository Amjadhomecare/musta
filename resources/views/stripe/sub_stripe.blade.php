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
                    <label for="statusFilter" class="form-label fw-semibold mb-1">Subscription Status</label>
                    <select id="statusFilter" class="form-select form-select-sm form-select-solid">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="cancel">Canceled</option>
                        <option value="past_due">Past&nbsp;due</option>
                        <option value="trialing">Trialing</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>
            </div>

            <!-- Sync button bottom-right -->
            <div class="d-flex justify-content-end mt-5">
                <button id="sync-charges"
                        class="btn btn-primary btn-lg px-6">
                    Sync&nbsp;Subscription
                </button>
            </div>
        </div>
    </div>
    <!--end::Filter card-->

    <!--begin::DataTable card-->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="stripe_sub" class="table table-hover table-row-dashed fs-6 gy-5 gs-5 w-100">
                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Created at</th>
                            <th>Name in ERP</th>
                            <th>Status</th>
                            <th>Canceled at</th>
                            <th class="text-end">Amount</th>
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
<div class="modal fade" id="sub-stripe-modal" tabindex="-1" role="dialog" aria-labelledby="payStripeModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ">Stripe Subscription Details</h5>
            </div>
            <div class="modal-body">
                <form id="stripe-details-form">
                    <div class="form-group">
                        <label for="stripe-id">ID</label>
                        <input type="text"  name="stripe_sub_id" id="stripe_sub_id" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="stripe-amount">Amount</label>
                        <input type="text" name="amount" id="stripe-amount" class="form-control" readonly>
                    </div>
            
                    <div class="form-group">
                        <label for="stripe-created-at">Created At</label>
                        <input type="text" name="date" id="stripe-created-at" class="form-control" readonly>
                    </div>

                                   
                <div class="form-group mb-3">
                    <label for="customerSelect">Customer From ERP</label>
                    <select id="customerSelect" name="customer" class="form-control" style="width: 100%;">
                    </select>
                </div>

                
             
                <div class="form-group text-right">
                        <button type="submit" class="btn btn-blue"> Update</button>
            </div>

                </form>
            </div>
       
            </div>
            </div>
            </div>
        </div>
    

@endsection

@push('scripts')
    @vite(['resources/js/stripe/sub.js'])
@endpush


