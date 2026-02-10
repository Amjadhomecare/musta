@extends('keen')
@section('content')

<!--begin::Documents card-->
<div class="card shadow-sm border-0">
    <div class="card-body">

        <!--begin::Toolbar-->
        <div class="d-flex flex-wrap align-items-center gap-4 mb-6">
            <h5 class="mb-0">Documents</h5>

            <button type="button"
                    class="btn btn-primary d-flex align-items-center ms-auto"
                    data-bs-toggle="modal"
                    data-bs-target="#documentModal">
                <i class="ki-duotone ki-plus fs-2 me-2"></i>New&nbsp;Document
            </button>
        </div>
        <!--end::Toolbar-->

        <!--begin::Table-->
          <div class="table-responsive">
                    <table id="doc_datatable" class="table w-100 align-middle table-row-dashed fs-6 gy-5 gs-5">
                        <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                    <tr>
                        <th style="width:3rem;"></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!--end::Table-->

    </div>
</div>
<!--end::Documents card-->



{{-- Modal --}}
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="documentForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="documentModalLabel">Add Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Person <span class="text-danger">*</span></label>
                    <select id="personSelect" name="person" class="form-select" required></select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Expire&nbsp;Date</label>
                    <input name="expire_date" type="date" class="form-control">
                </div>

                <div class="col-12">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input name="file" type="file"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx ,.csv" 
                           class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Note</label>
                    <textarea name="note" class="form-control" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>




@endsection

@push('scripts')
  
@vite(['resources/js/report/document.js'])

@endpush