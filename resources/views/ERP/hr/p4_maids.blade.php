@extends('keen')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">
        
        <!-- Header & Import Section -->
        <div class="card mb-5 mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Maid Document Expiry Management</span>
     
                </h3>
                <div class="card-toolbar">
                    <div class="d-flex flex-column gap-3">
                        <!-- Import Labor Card Form -->
                        <form action="{{ route('bulk.maid-doc-expiry.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <div class="input-group input-group-sm input-group-solid">
                                <input type="file" name="file" class="form-control" required accept=".xlsx,.xls,.csv">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i> Import Labor Card Expiry
                                </button>
                            </div>
                        </form>
                        
                        <!-- Import Passport & Visa/EID Form -->
                        <form action="{{ route('bulk.passport-doc-expiry.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <div class="input-group input-group-sm input-group-solid">
                                <input type="file" name="file" class="form-control" required accept=".csv">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i> Import Visa/EID Expiry CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card-body py-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ki-duotone ki-check-circle fs-2 text-success me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ki-duotone ki-cross-circle fs-2 text-danger me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <!-- Filters Section -->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6 mb-5">
                    <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                        <div class="mb-3 mb-md-0 fw-semibold">
                            <h4 class="text-gray-900 fw-bold mb-3 d-flex align-items-center">
                                <i class="ki-duotone ki-filter fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                Filters
                            </h4>
                            
                            <div class="row g-5">
                                <!-- Maid Type Filter -->
                                <div class="col-md-3">
                                    <label for="maidTypeFilter" class="form-label fs-6 fw-bold text-gray-700">Maid Type</label>
                                    <select class="form-select form-select-solid" id="maidTypeFilter" data-control="select2" data-placeholder="Select Type">
                                        <option value="">All Types</option>
                                        <option value="HC">HC</option>
                                        <option value="Direct hire">Direct Hire</option>
                                    </select>
                                </div>

                                <!-- Status Filters -->
                                <div class="col-md-9">
                                    <label class="form-label fs-6 fw-bold text-gray-700 mb-4">Quick Filters</label>
                                    <div class="d-flex flex-wrap gap-4">
                                        
                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="passportExpiring"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="passportExpiring">
                                                <i class="ki-duotone ki-calendar-tick text-warning fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                Passport Expiring (≤30 days)
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="eidExpiring"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="eidExpiring">
                                                <i class="ki-duotone ki-calendar-tick text-warning fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                EID Expiring (≤30 days)
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="visaExpiring"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="visaExpiring">
                                                <i class="ki-duotone ki-calendar-tick text-warning fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                Visa Expiring (≤30 days)
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="laborExpiring"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="laborExpiring">
                                                <i class="ki-duotone ki-calendar-tick text-warning fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                Labor Card Expiring (≤30 days)
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="nullPassport"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="nullPassport">
                                                <i class="ki-duotone ki-cross-circle text-danger fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                Missing Passport
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="nullEid"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="nullEid">
                                                <i class="ki-duotone ki-cross-circle text-danger fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                Missing EID
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="nullVisa"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="nullVisa">
                                                <i class="ki-duotone ki-cross-circle text-danger fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                Missing Visa
                                            </label>
                                        </div>

                                        <div class="form-check form-switch form-check-custom form-check-solid border p-2 rounded bg-white">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" id="nullLabor"/>
                                            <label class="form-check-label ps-2 fs-7 fw-semibold text-gray-700" for="nullLabor">
                                                <i class="ki-duotone ki-cross-circle text-danger fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                                Missing Labor Card
                                            </label>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="p4_expira_dt" class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-150px rounded-start">Name</th>
                                <th class="min-w-120px">Passport Number</th>
                                <th class="min-w-100px">Maid Type</th>
                                <th class="min-w-120px">Passport</th>
                                <th class="min-w-120px">Emirate ID</th>
                                <th class="min-w-120px">Visa Expiry</th>
                                <th class="min-w-120px">Labor Card</th>
                                <th>Status</th>
                                <th>Created by</th>
                                <th>Updated by</th>
                                <th>Created at</th>
                                <th>Updated at</th>
                                <th class="pe-4 text-end rounded-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Document Expiry Modal -->
<div class="modal fade" id="editDocExpiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Edit Document Expiry Dates</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="docExpiryForm" class="form">
                    <input type="hidden" id="maid_id" name="maid_id">
                    
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2">
                            <span class="required">Maid Name</span>
                        </label>
                        <input type="text" class="form-control form-control-solid" id="maid_name" readonly>
                    </div>

                    <div class="row g-9 mb-8">
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Passport Expiry</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text"><i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="date" class="form-control form-control-solid" id="passport_expiry" name="passport_expiry">
                            </div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">EID Expiry</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text"><i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="date" class="form-control form-control-solid" id="eid_expiry" name="eid_expiry">
                            </div>
                        </div>
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2">Labor Card Expiry</label>
                            <div class="input-group input-group-solid">
                                <span class="input-group-text"><i class="ki-duotone ki-calendar fs-2"><span class="path1"></span><span class="path2"></span></i></span>
                                <input type="date" class="form-control form-control-solid" id="labor_card_expiry" name="labor_card_expiry">
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-15">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="button" class="btn btn-primary" id="saveDocExpiry">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/hr/p4.js')
@endpush
