@extends('keen')
@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Filter card-->
        <div class="card shadow-sm mb-8">
            <div class="card-body">

                <!--begin::Filter row-->
                <div class="row gx-5 gy-4 align-items-end">
                    <!-- From date -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="min-date" class="form-label fw-semibold mb-1">
                            From&nbsp;Date
                        </label>
                        <input type="date"
                               id="min-date"
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    <!-- To date -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="max-date" class="form-label fw-semibold mb-1">
                            To&nbsp;Date
                        </label>
                        <input type="date"
                               id="max-date"
                               class="form-control form-control-sm form-control-solid">
                    </div>

                    <!-- Contract status -->
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="filterContracts" class="form-label fw-semibold mb-1">
                            Contract&nbsp;Status
                        </label>
                        <select id="filterContracts"
                                class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Returned</option>
                        </select>
                    </div>

                    <!-- Toggle -->
                    <div class="col-12 col-lg-3 d-flex align-items-center pt-3">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="no_image">
                            <label class="form-check-label ms-2 fw-semibold"
                                   for="no_image">
                                Customers&nbsp;w/o&nbsp;ID
                            </label>
                        </div>
                    </div>
                </div>
                <!--end::Filter row-->

            </div>
        </div>
        <!--end::Filter card-->

        <!--begin::Contracts table card-->
        <div class="card card-flush shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="p4dataTable"
                           class="table table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                <th>Date</th>
                                <th>Contract&nbsp;Ref</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Maid</th>
                                <th>Status</th>
                                <th>Created&nbsp;by</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data rows rendered by DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Contracts table card-->

    </div><!--end::Content container-->
</div>
<!--end::Content wrapper-->

<!-- Modal -->
<div id="return_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="maidReturnForm" class="px-3">
                    @csrf
                    <input readOnly type="text" id="maidNameInput" name="maidName" placeholder="Maid Name">
                    <input readOnly  type="text" id="contractRefInput" name="contractRef" placeholder="Contract Ref">
                    <input readOnly  type="text" id="customerInput" name="customer" placeholder="Customer">
                    <input type="text" id="reasonInput" name="reason" placeholder="Reason" required>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Modal for Complaint -->
<div id="comp_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="compModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="compModalLabel">Customer Complaint</h5>
                     
                </button>
            </div>
            <div class="modal-body">
                <form id="customerCompForm">
                    @csrf
                    <div class="form-group">
                        <label for="maidName">Maid Name</label>
                        <input readonly type="text" id="maidName" name="maidName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contractRef">Contract Ref</label>
                        <input readonly type="text" id="contractRef" name="contractRef" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="customerComp">Customer</label>
                        <input readonly type="text" id="customerComp" name="customer" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="reasonInput">Reason</label>
                        <textarea id="reasonInput" name="reason" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="statusSelect">Status</label>
                        <select id="statusSelect" name="status" class="form-control" required>
                            <option value="pending">Pending</option>
    
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="typeSelect">Type</label>
                        <select id="typeSelect" name="type" class="form-control" required>
                            <option value="general">General</option>
                            <option value="ranaway">Ranaway</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignedToSelect">Assigned To</label>
                        <select style="width:100%" id="assignedToSelect" name="assignedTo" class="form-control" required>
                
                        </select>
                    </div>
                     <br>
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div id="start_date_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form id="startDateForm" class="px-3">
                    @csrf
                    <input readOnly type="text" id="idContract" name="started_date_id" >
                    <input  type="date" id="startDatenput" name="started_date">
                    <input  type="date" id="returnDatenput" name="returned_date">
      
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



@endsection


@push('scripts')
    @vite('resources/js/p4/contractp4.js')
@endpush

