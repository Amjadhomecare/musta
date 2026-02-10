@extends('keen')
@section('content')

<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                     
                    
         <table id="basic-datatable" class="table dt-responsive nowrap w-100">
             <thead>
            <tr>
            <th>Action</th>   
                <th>Contract Ref</th>
                <th>Customer</th>
                <th>Accrued Date</th>
                <th>Current Maid</th> 
                <th>Salary</th>
                <th>Amount</th>
                <th>Note</th>
                <th>Cheque</th>
           
            </tr>
        </thead>
        <tbody>
            @foreach($cat4Ref as $item =>$value)
            <td>
              
              <a href="{{ route('storeGeneraterInvoice', $value->id) }}" 
                  class="btn btn-blue rounded-pill waves-effect waves-light generate-invoice-btn">
                      <i class="fa fa-pencil" aria-hidden="true"></i>
            </a>

              <button
                              type="button"
                              class="btn btn-primary btn-sm open-modal-btn"
                              data-bs-toggle="modal"
                              data-bs-target="#customized-inv-model"
                              data-item-id="{{ $value->id }}"
                              data-date="{{ $value->accrued_date}}"
                              data-contract-ref="{{ $value->contract }}"
                              data-customer="{{$value->customer}}"
                              data-maid="{{$value['contractRef']['maid']}}"
                              maid-salary= "{{ $value['contractRef']['maidInfo']['salary'] }}"
                              total_amount = "{{ $value->amount }}"
                              data-note = "{{ $value->note }}"
                              data-cheque = "{{ $value->cheque }}"
                          
                             >
                               customize
              </button>

    
              </td>
            <tr>

                <td>{{ $value->contract }}</td>
                <td>{{ $value->customer }}</td>  
                <td>{{ $value->accrued_date }}</td>
                <td>{{ $value['contractRef']['maid']}}</td>
                <td>{{ $value['contractRef']['maidInfo']['salary'] }}</td>
                <td>{{ $value->amount }}</td>
                <td>{{ $value->note }}</td>
                <td>{{ $value->cheque }}</td>
              
        

               


            </tr>
            @endforeach
            </tbody>
                    </table>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->                
                        
 </div> <!-- container -->




 <!--Modal -->
<div id="customized-inv-model" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title">Invoice Creation</h5>
              
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form class="px-3" method="post" action="{{route('storeustomizedInvoiceCntl')}}">
                    @csrf

                    <!-- ID Section -->
                    <div class="form-group row">
                        <label for="value-item-id" class="col-sm-4 col-form-label">ID:</label>
                        <div class="col-sm-8">
                            <span id="modal-item-id"></span>
                            <input hidden type="text" name="id" id="value-item-id" class="form-control">
                        </div>
                    </div>

                    <!-- Invoice Accrued Date Section -->
                    <div class="form-group row">
                        <label for="value-item-date" class="col-sm-4 col-form-label">Invoice Accrued Date:</label>
                        <div class="col-sm-8">
                            <span id="modal-item-date"></span>
                            <input hidden type="text" name="date" id="value-item-date" class="form-control">
                        </div>
                    </div>

                    <!-- Contract Reference Section -->
                    <div class="form-group row">
                        <label for="value-item-contract-ref" class="col-sm-4 col-form-label">Contract Reference:</label>
                        <div class="col-sm-8">
                            <span id="modal-item-contract-ref"></span>
                            <input hidden type="text" name="contractRef" id="value-item-contract-ref" class="form-control">
                        </div>
                    </div>

                    <!-- Customer Section -->
                    <div class="form-group row">
                        <label for="value-item-customer" class="col-sm-4 col-form-label">Customer:</label>
                        <div class="col-sm-8">
                            <span id="modal-item-customer"></span>
                            <input hidden type="text" name="customer" id="value-item-customer" class="form-control">
                        </div>
                    </div>

                    <!-- Maid Section -->
                    <div class="form-group row">
                        <label for="value-maid" class="col-sm-4 col-form-label" style="color:red">Maid:</label>
                        <div class="col-sm-8">
                            <span id="modal-maid"></span>
                            <input hidden type="text" name="maidName" id="value-maid" class="form-control">
                        </div>
                    </div>
        
                      <!-- Note Section -->
                    <div class="form-group row">
                        <label for="value-maid" class="col-sm-4 col-form-label" style="color:red">Note:</label>
                        <div class="col-sm-8">
                            <span id="modal-maid"></span>
                            <input type="text" name="invoiceNote" id="value-note" class="form-control">
                        </div>
                    </div>

                         <!-- Cheque number Section -->
                    <div class="form-group row">
                        <label for="value-maid" class="col-sm-4 col-form-label" style="color:red">Cheque number:</label>
                        <div class="col-sm-8">
                            <span id="modal-maid"></span>
                            <input type="text" name="chequeNumber" id="value-cheque" class="form-control">
                        </div>
                    </div>



                    <!-- Salary Section -->
                    <div class="form-group row">
                        <label for="value-maid-salary" class="col-sm-4 col-form-label">Salary:</label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" name="maidSalary" id="value-maid-salary">
                        </div>
                    </div>

                    <!-- Net Gross Invoice Section -->
                    <div class="form-group row">
                        <label for="grossTotalInvoice" class="col-sm-4 col-form-label">Net Gross Invoice:</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="totalNetAmount" type="text" name="grossTotalnvoice">
                        </div>
                    </div>

                    <!-- Total Amount Section -->
                    <div class="form-group row">
                        <label for="value-total-amount" class="col-sm-4 col-form-label">Total Amount:</label>
                        <div class="col-sm-8">
                            <input readOnly class="form-control" type="text" name="totalInvoice" id="value-total-amount">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button id="totalSubmitAmount" class="btn btn-primary" type="submit">Confirm</button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



 </div> <!-- content -->



 <script type="text/javascript">

document.addEventListener('DOMContentLoaded', function () {
    let modal = document.getElementById('customized-inv-model');
    document.querySelectorAll('.open-modal-btn').forEach(function (button) {
        button.addEventListener('click', function () {
           
            modal.querySelector('#modal-item-id').textContent = button.getAttribute('data-item-id');
            modal.querySelector('#value-item-id').value = button.getAttribute('data-item-id');

            modal.querySelector('#modal-item-date').textContent = button.getAttribute('data-date');
            modal.querySelector('#value-item-date').value = button.getAttribute('data-date');

            modal.querySelector('#modal-item-contract-ref').textContent = button.getAttribute('data-contract-ref');
            modal.querySelector('#value-item-contract-ref').value = button.getAttribute('data-contract-ref');

            modal.querySelector('#modal-item-customer').textContent = button.getAttribute('data-customer');
            modal.querySelector('#value-item-customer').value = button.getAttribute('data-customer');

            modal.querySelector('#modal-maid').textContent = button.getAttribute('data-maid');
            modal.querySelector('#value-maid').value = button.getAttribute('data-maid');

            modal.querySelector('#value-note').value = button.getAttribute('data-note');
            modal.querySelector('#value-cheque').value = button.getAttribute('data-cheque');

         


           
            function updateSubmitButtonState() {
                let maidSalaryValue = parseFloat(document.getElementById('value-maid-salary').value) || 0;
                let netGrossInvoice = parseFloat(document.getElementById('totalNetAmount').value) || 0;
                let totalAmountValue = parseFloat(button.getAttribute('total_amount')) || 0;
                let submitButton = document.getElementById('totalSubmitAmount');

                if (maidSalaryValue + netGrossInvoice !== totalAmountValue) {
                    submitButton.disabled = true;
                } else {
                    submitButton.disabled = false;
                }
            }

           
           
            modal.querySelector('#value-total-amount').value = button.getAttribute('total_amount');

        
            modal.querySelector('#totalNetAmount').addEventListener('change', updateSubmitButtonState);
            modal.querySelector('#value-maid-salary').addEventListener('change', updateSubmitButtonState);
         
            
            updateSubmitButtonState();
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.generate-invoice-btn').forEach( (button) => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); 
            const href = button.getAttribute('href'); 
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to generate the invoice?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, generate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});


</script>



@endsection