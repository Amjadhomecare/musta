@extends('keen')
@section('content')


<div class="container">
    <h2>Receipt Voucher</h2>
    <form action="{{route('storeCashierRV')}}" method="post" class="form-horizontal">
        @csrf <!-- CSRF token for security -->

        <div class="row">
            <!-- Column 1 -->
            <div class="col-md-6 col-sm-12">
                <div class="form-group">
                    <label for="contractRef">Contract Reference For cat1:</label>
                    <input type="text" class="form-control" id="contractRef" name="contract_ref" >
                </div>

                <div class="form-group">
                    <label for="invoiceRef">Invoice Reference For cat4:</label>
                    <input type="text" class="form-control" id="invoiceRef" name="inv4_ref" >
                </div>

                <div class="form-group">
                    <label for="customerName">Customer Name:</label>
                    <select  class="form-control" id="customerName" name="customer_name" required>
                        @foreach($customer as $customer)
                        <option value="{{$customer->ledger}}">{{$customer->ledger}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Column 2 -->
            <div class="col-md-6 col-sm-12">
                <div class="form-group mb-3">
                    <label for="receivedPaymentSelect">Received Payment</label>
                    <select name="receivedFromLedger" id="receivedPaymentSelect" class="form-select">
                        @foreach($cashAndBank as $payment)
                        <option value="{{$payment->ledger}}">{{$payment->ledger}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="closingBalance">Closing Balance:</label>
                    <input type="number" readOnly class="form-control" id="closingBalance" name="closing_balance" >
                </div>

                <div class="form-group">
                    <label for="maidName">Maid Name:</label>
                    <select  class="form-control" id="maidName" name="maid_name" required>
                    <option value="No maid">No maid</option>
                        @foreach($maids as $maid)
                        <option value="{{$maid->name}}">{{$maid->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        
     
    <div class="form-group">
            <label for="transactionDate">Transaction Date:</label>
            <input type="date" value="{{$date}}" class="form-control" id="transactionDate" name="transaction_date" required>
        </div>

        <div class="form-group">
            <label for="amountToReceive">Amount to Receive:</label>
            <input type="number" class="form-control" id="amountToReceive" name="amount_to_receive" required>
        </div>

        <div class="form-group">
                    <label for="note">Add note</label>
                    <input type="text" class="form-control" id="note" name="note" >
                </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>


<br>    <br>  
<div class="form-group row">
            <div class="col-md-3">
                <label for="filterFromDate">From Date:</label>
                <input type="date" id="filterFromDate" class="form-control" />
            </div>
            <div class="col-md-3">
                <label for="filterToDate">To Date:</label>
                <input type="date" id="filterToDate" class="form-control" />
            </div>

       
        
 </div>
        <br>      

<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <table id="receipt-voucher-table" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>                          
                                    <th>Date</th> 
                                    <th>Ref#</th> 
                                    <th>Customer</th> 
                                    <th>Maid</th> 
                                    <th>Amount Received</th> 
                                    <th>Note</th>
                                    <th>Closing balance</th> 
                                        
                                  
                                </tr>
                            </thead>
                        </table>

                    </div> {{-- end card body --}}
                </div> {{-- end card --}}
            </div>{{-- end col --}}
        </div>
        {{-- End DataTable --}}

    </div> {{-- container --}}
</div> {{-- content --}}



@section('scriptForCategory4contracts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.10/js/jquery.dataTables.min.js"></script>


<!-- DataTables Buttons -->
   <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <!-- JSZip (for Excel export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- PDFMake (for PDF export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <!-- DataTables Button - Export Plugins -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

  

    <script>
$(document).ready(function() {
    let dataTable = $('#receipt-voucher-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("getAllRV") }}',
            data: function (d) {
                
                let  fromDate = $('#filterFromDate').val();
                let  toDate = $('#filterToDate').val();
                if (fromDate) {
                    d.fromDate = fromDate;
                }
                if (toDate) {
                    d.toDate = toDate;
                }
                
            }
        },
        columns: [
            { data: 'created_at', name: 'created_at' },                        
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<a target="__blank" href="/receipt/voucher/cashier/${row.refCode}" >${row.refCode}</i></a>
                        
                      
                    `;
                }
            },
            
            { data: 'account', name: 'account' },                                                   
            { data: 'maid_name', name: 'maid_name' },                                                  
            { data: 'amount', name: 'amount' },                                                 
            { data: 'notes', name: 'notes' },  
            { data: 'closing_balance', name: 'closing_balance' },                                                    
        ],
        order: [[0, 'desc']],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        dom: 'Blfrtip'
    });

    $('#filterFromDate, #filterToDate').on('change', function() {
        dataTable.ajax.reload();
    });

    });



document.addEventListener('DOMContentLoaded', function () {
 
 const contractRefSelect = document.getElementById('contractRef');

 contractRefSelect.addEventListener('change', function () {
     const contractRef = this.value;

     fetch('/ajax-cashier/'+contractRef)
         .then(response => {
             if (response.ok) {
                 return response.json();
             } else {
               
                console.log(response)
                 throw new Error('Server responded with status ' + response.status);
             }
         })
         .then(data => {
            const customerNameSelect = document.getElementById('customerName');
            customerNameSelect.innerHTML = '';
            const option = new Option(data.customer, data.customer); 
            customerNameSelect.appendChild(option);

            const maidNameSelect = document.getElementById('maidName');
            maidNameSelect.innerHTML = '';
            const maidOption = new Option(data.maid, data.maid); 
            maidNameSelect.appendChild(maidOption);

            const closing_balance = document.getElementById('closingBalance');
            closing_balance.value = data.closing_balance

         
         })
         .catch(error => {
             console.error('Error:', error);
             alert('An error occurred: ' + error.message);
         });
 });


});



document.addEventListener('DOMContentLoaded', function () {

const contractRefSelect = document.getElementById('invoiceRef');

contractRefSelect.addEventListener('change', function () {
    const invRef4 = this.value;

    fetch('/ajax-inv4/'+invRef4)
        .then(response => {
            if (response.ok) {
                return response.json();
            } else {
              
               console.log(response)
                throw new Error('Server responded with status ' + response.status);
            }
        })
        .then(data => {
           const customerNameSelect = document.getElementById('customerName');
           customerNameSelect.innerHTML = '';
           const option = new Option(data.customer, data.customer); 
           customerNameSelect.appendChild(option);

           const maidNameSelect = document.getElementById('maidName');
           maidNameSelect.innerHTML = '';
           const maidOption = new Option(data.maid, data.maid); 
           maidNameSelect.appendChild(maidOption);
  
           const closing_balance = document.getElementById('closingBalance');

           closing_balance.value = data.closing_balance
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
});

});


</script>

@endsection
@endsection