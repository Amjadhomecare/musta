@extends('keen')
@section('content')


<div class="card card-flush">
    <!-- Card Header -->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <h2>Credit Memos</h2>
        </div>

        <div class="card-toolbar">
            <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#credit-memo-form-modal">
                <i class="ki-duotone ki-plus fs-2"></i> Add New Credit Memo
            </button>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table id="credit-memo-datatable"
                class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Date</th>
                        <th>Memo Ref</th>
                        <th>Contract</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Maid</th>
                        <th>Note</th>
                        <th>Started</th>
                        <th>Returned Date</th>
                        <th>Amount to Refund</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic rows go here -->
                </tbody>
            </table>
        </div>
    </div>
</div>


  <!-- Modal  -->
  <div id="credit-memo-form-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Credit Memo</h5>
          
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="creditMemoForm" enctype="multipart/form-data" class="px-3">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Left Column -->
                            <div class="form-group">
                            <label for="contract_ref">Package one return ref</label>
                             <input class="form-control " id="contract_ref"  name="contract_ref_p1">
                            
                         </div>
 
                         <div class="form-group">
                            <label for="contract_ref">Package 4  return ref</label>
                             <input class="form-control " id="contract_ref4"  name="contract_ref_p4">
                             </div>

  
                            <div class="form-group">
                                <label for="note">Note</label>
                                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Enter your note here" required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="amount_received">Amount Received</label>
                                <input type="number" class="form-control" id="amount_received" name="amount_received" required>
                            </div>

                            <div class="form-group">
                                <label for="amount_deduction">Deducton for company</label>
                                <input type="number" class="form-control" id="amount_deduction" name="amount_deduction" required>
                            </div>

                            <div class="form-group">
                                <label for="amount_salary">Maid salary</label>
                                <input type="number" class="form-control" id="amount_salary" name="amount_salary" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Right Column -->
                            <div class="form-group">
                                <label for="thecategory">Category Type</label>
                                <input readOnly type="text" class="form-control" id="thecategory" name="category" required>
                            </div>

                            <div class="form-group">
                                <label for="thecustomer">Customer</label>
                                <input readOnly type="text" class="form-control" id="thecustomer" name="customer" required>
                            </div>

                            <div class="form-group">
                                <label for="maid">Maid</label>
                                <input readOnly type="text" class="form-control" id="themaid" name="maid" required>
                            </div>

                            <div class="form-group">
                                <label for="started_date">Started Date</label>
                                <input readOnly type="date" class="form-control" id="thestarted_date" name="started_date" required>
                            </div>

                        
                            <div class="form-group">
                                <label for="returned_date">Returned Date</label>
                                <input readOnly type="date" class="form-control" id="thereturned_date" name="returned_date" required>
                            </div>

                            <div class="form-group">
                                <label for="refunded_amount">Refunded Amount</label>
                                <input  readOnly type="number" class="form-control" id="refunded_amount" name="refunded_amount" required>
                            </div>
                        </div>
                    </div>
                    <br><br>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </div>

                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->




 

@endsection

@push('script')

@push('scripts')
    @vite(['resources/js/complain/refund.js'])
@endpush

@endpush