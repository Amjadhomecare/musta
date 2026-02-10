@extends('keen')
@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Customers card-->
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0">Customer&nbsp;Details</h5>

                <!-- Add Customer button (right) -->
                <button
                    id="add-new-customer"
                    type="button"
                    class="btn btn-primary ms-auto"
                    data-bs-toggle="modal"
                    data-bs-target="#customer-form-modal">
                    <i class="ki-duotone ki-plus fs-2 me-2"></i>Add&nbsp;New&nbsp;Customer
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="customers-datatable"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Secondary&nbsp;Phone</th>
                                <th>ID&nbsp;Number</th>
                                <th>ID&nbsp;Type</th>
                                <th>Customer&nbsp;Type</th>
                                <th>Related</th>
                                <th>Email</th>
                                <th>Nationality</th>
                                <th>Address</th>
                                <th>ID&nbsp;Image</th>
                                <th>Note</th>
                                <th>Created&nbsp;By</th>
                                <th>Created&nbsp;At</th>
                                <th class="text-end">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables rows here --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Customers card-->

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->


     @include('ERP.customers.customer_model_js')

    @endsection

@push('scripts')
    @vite('resources/js/customers/customer.js') 
@endpush

