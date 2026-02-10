@extends('keen')
@section('content')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Filter card-->
        <div class="card shadow-sm mb-8">
            <div class="card-body">
                <form id="payrollForm" class="row gx-5 gy-4 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="month" class="form-label fw-semibold mb-1">Month</label>
                        <input type="month" id="month" name="month" class="form-control form-control-sm form-control-solid" required>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="branch" class="form-label fw-semibold mb-1">Visa Branch</label>
                        <select id="branch" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="h">Homecare</option>
                            <option value="fc">Familycare</option>
                            <option value="kh">Khorfakan</option>
                            <option value="ahl">Ahlia</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="maidStatus" class="form-label fw-semibold mb-1">Maid&nbsp;Status</label>
                        <select id="maidStatus" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="hired">Hired</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="maidType" class="form-label fw-semibold mb-1">Maid&nbsp;Type</label>
                        <select id="maidType" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="HC">HC</option>
                            <option value="Direct Hire">Direct&nbsp;Hire</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="maidPayment" class="form-label fw-semibold mb-1">Payment&nbsp;Type</label>
                        <select id="maidPayment" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="workingDaysFilter" class="form-label fw-semibold mb-1">Working&nbsp;Days</label>
                        <select id="workingDaysFilter" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="more_than_25">&#8805;&nbsp;25&nbsp;days</option>
                            <option value="less_than_25">&lt;&nbsp;25&nbsp;days</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="paymentStatusFilter" class="form-label fw-semibold mb-1">Payment&nbsp;Status</label>
                        <select id="paymentStatusFilter" class="form-select form-select-sm form-select-solid">
                            <option value="">All</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-6 d-flex flex-wrap align-items-center gap-4 pt-3">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input filter-checkbox" type="checkbox" id="filterNoNoteNoBooked">
                            <label class="form-check-label" for="filterNoNoteNoBooked">No&nbsp;Note &amp; No&nbsp;Booked</label>
                        </div>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input filter-checkbox" type="checkbox" id="filterBooked">
                            <label class="form-check-label" for="filterBooked">Only&nbsp;Booked</label>
                        </div>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input filter-checkbox" type="checkbox" id="filterNote">
                            <label class="form-check-label" for="filterNote">Only&nbsp;Note</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end pt-4">
                        <button type="submit" class="btn btn-primary px-10">
                            Get&nbsp;Payroll&nbsp;Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Filter card-->

        <!--begin::Payroll table card-->
        <div class="card card-flush shadow-sm">
            <div class="card-header py-4">
                <h5 class="card-title mb-0">Maid&nbsp;Payrolls</h5>
                <div class="d-flex flex-wrap align-items-center gap-4 ms-auto">
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input type="checkbox" id="selectAllMaids" class="form-check-input">
                        <label for="selectAllMaids" class="form-check-label">Select&nbsp;All</label>
                    </div>
                    <input type="text" id="bulk-note" class="form-control form-control-sm form-control-solid w-200px" placeholder="Enter note" />
                    <button id="bulkSaveButton" class="btn btn-light-primary">
                        Bulk&nbsp;Generate&nbsp;Payroll
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="payrollTable" class="table table-bordered table-hover table-row-dashed fs-6 w-100">
                        <thead class="bg-light text-gray-700 fw-bold text-uppercase">
                            <tr>
                                @for ($i=0; $i<23; $i++)
                                    <th></th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end::Payroll table card-->

    </div>
    <!--end::Content container-->
</div>
<!--end::Content wrapper-->
<!-- Edit Maid Deduction Modal -->
<div id="maid-dedction" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form id="maidDeductionForm" class="px-3">
          @csrf

          <!-- Hidden: primary key of advance record -->
          <input type="hidden" id="idForDeduction" name="advanceDataId">

          <!-- Display maid (readonly, not submitted) -->
          <div class="form-group mb-2">
            <label for="maidNameForDeduction">Maid</label>
            <input type="text" class="form-control" id="maidNameForDeduction" readonly>
          </div>

          <!-- (Optional) Hidden maid_id if you ever need it on edit -->
          <!-- <input type="hidden" id="maidIdForDeduction" name="maid_id"> -->

          <div class="form-group mb-2">
            <label for="deductionInput">Deduction Amount</label>
            <input type="number" class="form-control" id="deductionInput" name="deductionMaid" placeholder="Enter deduction amount">
          </div>

          <div class="form-group mb-2">
            <label for="allowanceInput">Allowance Amount</label>
            <input type="number" class="form-control" id="allowanceInput" name="allowanceMaid" placeholder="Enter allowance amount">
          </div>

          <div class="form-group mb-3">
            <label for="noteInput">Note</label>
            <input type="text" class="form-control" id="noteInput" name="noteMaid" placeholder="Enter note">
          </div>

          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Add Maid Deduction Modal -->
<div id="add-maid-deduction-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <form id="addMaidDeductionForm" class="px-3">
          @csrf

          <!-- Display maid (readonly, not used by backend) -->
          <div class="form-group mb-2">
            <label for="maidNameAdd">Maid</label>
            <input type="text" class="form-control" id="maidNameAdd" placeholder="Maid name" readonly>
          </div>

          <!-- Required by backend: maid_id -->
          <input type="hidden" id="maidIdAdd" name="maid_id">

          <div class="form-group mb-2">
            <label for="deductionInputAdd">Deduction Amount</label>
            <input type="number" class="form-control" id="deductionInputAdd" name="deductionMaid" placeholder="Enter deduction amount">
          </div>

          <div class="form-group mb-2">
            <label for="allowanceInputAdd">Allowance Amount</label>
            <input type="number" class="form-control" id="allowanceInputAdd" name="allowanceMaid" placeholder="Enter allowance amount">
          </div>

          <div class="form-group mb-2">
            <label for="noteInputAdd">Note</label>
            <input type="text" class="form-control" id="noteInputAdd" name="noteMaid" placeholder="Enter note">
          </div>

          <div class="form-group mb-3">
            <label for="modalMonth">Select Month</label>
            <input type="month" id="modalMonth" name="month" class="form-control" readonly required>
          </div>

          <button type="submit" class="btn btn-success mt-2">Add Record</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- /Add Maid Deduction Modal -->




@endsection


@push('scripts')
    @vite('resources/js/maid_payroll/new_payroll.js')
@endpush

