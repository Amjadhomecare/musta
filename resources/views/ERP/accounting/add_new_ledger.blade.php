@extends('keen')
@section('content')

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                    @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

                <div class="card-header">
                    Add New Ledger Account
                </div>
                <div class="card-body">
                    <form method="POST" action="{{route('storeRegisterNewLedgerCntl')}}"> 
                        @csrf
                        <div class="form-group mb-3">
                            <label for="ledgerName">Ledger Name</label>
                            <input
                                type="text"
                                 pattern="^[A-Za-z0-9_'']+( [A-Za-z0-9_'']+)*$"
"


                                title="Only English letters with single spaces between words and optional basic punctuation at the end."
                                class="form-control"
                                id="ledgerName"
                                name="ledger_name"
                                placeholder="Enter Ledger Name"
                                required
                                >
                        </div>

                        <div class="form-group mb-3">
                            <label for="note">note</label>
                            <input type="text" class="form-control" id="note" name="note" placeholder="note">
                        </div>

                        <div class="form-group mb-3">
                            <label value ="0" for="amount">amount</label>
                            <input type="number" value ="0"  class="form-control" id="amount" name="amount" placeholder="amount related to ledger">
                        </div>


                        <div class="form-group mb-3">
                            <label for="selectClass">Select Class</label>
                            <select class="form-control" id="selectClass" name="class_name">
                               
                                <option disabled>Choose Class</option>
                                <option value="Assets">Assets</option>
                                <option value="Liability">liability</option>
                                <option value="Owner's Equity">Owner's Equity</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Expenses">Expenses</option>
                               
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="selectClass">Select Sub Class</label>
                            <select class="form-control" id="selectClass" name="sub_class_name">
                                <option disabled>Choose SubClass</option>
                                <option value="Long Term Liabilites">Long Term Liabilites</option>
                                <option value="Current assets">Current assets</option>
                                <option value="Other current libilities">Other current libilities</option>
                                <option value="Fixed Assets">Fixed Assets</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Expenses">Expenses</option>
                                <option value="operating activities">operating activities</option>
                                <option value="investing activities">investing activities</option>
                                <option value="financing activities">financing activities</option>
                            </select>
                        </div>




                        <div class="form-group mb-3">
                            <label for="selectGroup">Select Group</label>
                            <select class="form-control" id="selectGroup" name="group_name">
                          
                                <option disabled>Choose Group</option>    
                                <option value="Investors">Investors</option>  
                                <option value="Staff payroll">Staff payroll</option>    
                                <option value="Staff addvance">Staff advance</option>    
                                <option value="online">online </option>
                                <option value="online payroll">online payroll</option>
                                <option value="ads">ads </option>   
                                <option value="Account Payable">Account Payable</option>
                                <option value="Other Current Liabilities">Other Current Liabilities</option>
                                <option value="Current assets">Current Assets</option>
                                <option value="Fixed Assets">Fixed Assets</option>
                                <option value="Capital">Capital</option>
                                <option value="Discount">Discount</option>
                                <option value="Running Exp">Running Exp</option>
                                <option value="Typing Income">Typing income</option>
                                <option value="Package4 Income">Package4 Income</option>
                                <option value="Current liability">Current liability</option>
                                <option value="Package 1 Income">Package 1 Income</option>
                                <option value="maid agent">maids Agents (Also used for maids CV)</option>
                                <option value="cash equivalent">cash equivalent (for add payment)</option>
                                <option value="fine package 4">fine package 4</option>
                                <option value="fine package 1">fine package 1</option>
                                <option value="doctor and medicine">Doctor and medicine</option>
                                <option value="food expenses">food expenses</option>
                                <option value="transportation expenses">transportation expenses</option>
                                <option value="Fuel & salik expenses & maintence">Fuel & salik expenses & maintence</option>
                                <option value="depreciation expenses">depreciation</option>
                                <option value="accumulated depreciation">accumulated depreciation</option>
                                <option value="maid visa expenses p1">maid visa expenses p1</option>
                                <option value="maid visa expenses p4">maid visa expenses p4</option>
                                <option value="fine on company">fine on company</option>
                                <option value="etisalat sim card">etisalat sim card</option>
                                <option value="office rent">office rent</option>
                                <option value="accommodation rent and other expenses">accommodation rent and other expenses</option>




                             
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>





  <!-- Modal  -->
  <div id="ledger-form-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ledger Editing </h5>
          
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="editFormLedger" enctype="multipart/form-data" class="px-3">

                <div class="form-group">
                    <label for="ledger">Ledger ID</label>
                    <input readOnly  type="text"  class="form-control" id="editledgerId" name='id'>
                </div>
         
                <div class="form-group">
                    <label for="ledger">Ledger</label>
                    <input readOnly  type="text"  class="form-control" id="editledgerName">
                </div>
                <div class="form-group">
                    <label for="editNote">note</label>
                    <input type="text" class="form-control" id="editNote" name='note'>
                </div>
                <div class="form-group">
                    <label for="editClass">class</label>
                    <select type="text" class="form-control" id="editClass" name='class'>

                                <option disabled>Choose Class</option>
                                <option value="Assets">Assets</option>
                                <option value="Liability">liability</option>
                                <option value="Owner's Equity">Owner's Equity</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Expenses">Expenses</option>
                                <option value="Liabilities">Liabilities</option>

                   </select> 
                </div>


                
                <div class="form-group">
                            <label for="selectClass">Select Sub Class</label>
                            <select class="form-control" id="selectClass" name="sub_class">
                               
                                <option disabled>Choose SubClass</option>

                                <option value="Long Term Liabilites">Long Term Liabilites</option>
                                <option value="Current assets">Current assets</option>
                                <option value="Other current libilities">Other current libilities</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Expenses">Expenses</option>
                                <option value="operating activities">operating activities</option>
                                <option value="investing activities">investing activities</option>
                                <option value="financing activities">financing activities</option>
                              
                               
                            </select>
                        </div>

                <div class="form-group">
                    <label for="editGroup"> group </label>
                    <select type="text" class="form-control" id="editGroup" name='group' >
                    <option disabled>Choose Group</option>     
                                <option value="Investors">Investors</option>  
                                <option value="Staff payroll">Staff payroll</option>    
                                <option value="Staff addvance">Staff advance</option>    
                                <option value="online">online </option>
                                <option value="online payroll">online payroll</option>
                                <option value="ads">ads </option>   
                                <option value="Account Payable">Account Payable</option>
                                <option value="Other Current Liabilities">Other Current Liabilities</option>
                                <option value="Current assets">Current Assets</option>
                                <option value="Fixed Assets">Fixed Assets</option>
                                <option value="Capital">Capital</option>
                                <option value="Discount">Discount</option>
                                <option value="Running Exp">Running Exp</option>
                                <option value="Typing Income">Typing income</option>
                                <option value="Package4 Income">Package4 Income</option>
                                <option value="Current liability">Current liability</option>
                                <option value="Package 1 Income">Package 1 Income</option>
                                <option value="maid agent">maids Agents (Also used for maids CV)</option>
                                <option value="cash equivalent">cash equivalent (for add payment)</option>
                                <option value="fine package 4">fine package 4</option>
                                <option value="fine package 1">fine package 1</option>
                                <option value="doctor and medicine">Doctor and medicine</option>
                                <option value="staff & management food expenses">staff & management food expenses</option>
                                <option value="transportation expenses">transportation expenses</option>
                                <option value="Fuel & salik expenses & maintence">Fuel & salik expenses & maintence</option>
                                <option value="depreciation expenses">depreciation</option>
                                <option value="accumulated depreciation">accumulated depreciation</option>
                                <option value="maid visa expenses p1">maid visa expenses p1</option>
                                <option value="maid visa expenses p4">maid visa expenses p4</option>
                                <option value="fine on company">fine on company</option>
                                <option value="etisalat sim card">etisalat sim card</option>
                                <option value="office rent">office rent</option>
                                <option value="accommodation rent and other expenses">accommodation rent and other expenses</option>
                                <option value="maid">maid</option>
                                <option value="Telephone & Internet Exp">Telephone & Internet Exp</option>
                        <!-- Add-new trigger -->
    <option value="__new__">+ Add new group…</option>
  </select>
  <small class="form-text text-muted">Choose an existing group or add a new one.</small>
</div>
                                        
                <div class="form-group">
                    <label for="editAmount">amount</label>
                    <input type="text" class="form-control" id="editAmount" name='amount'>
                </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </div>

                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <table id="ledgers-table" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>                          
                                    <th>Created at</th> 
                                    <th>Account Name</th>
                                    <th>Class</th> 
                                    <th>Sub Class</th>
                                    <th>Group</th>
                                    <th>Note</th>
                                    <th>Amount related </th>
                              
                                    <th>Action</th> 
                        
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


@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let dataTable = $('#ledgers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("AjaxlistAccountLedgers") }}',
      
        },
        columns: [
            { data: 'created_at', name: 'created_at' }, 
            { data: 'ledger', name: 'ledger' },   
            { data: 'class', name: 'class' }, 
            { data: 'sub_class', name: 'sub_class' },  
            { data: 'group', name: 'group' },  
            { data: 'note', name: 'note' }, 
            { data: 'amount', name: 'amount' }, 
         
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<button data-id="${row.id}" class="btn open-modal-btn btn-block mb-3" data-bs-toggle="modal" data-bs-target="#ledger-form-modal">Edit</button>`;
                }

            }    
             ],
                order: [[0, 'desc']],
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                dom: 'Blfrtip'
            });

      
        const form = document.getElementById('editFormLedger');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');
            try {
                const response = await fetch("{{ route('updateLedger') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP error, status = ' + response.status);
                    }

                    const result = await response.json();

                    $('#ledger-form-modal').modal('hide');
                    if (result.status === 'success') {

                        dataTable.ajax.reload(null, false);
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                }

            });
});


$(document).on('click', '.open-modal-btn', function() {
    let ledgerId = $(this).data('id');
    $.ajax({
        url: '/ledger/' + ledgerId, 
        type: 'GET',
        success: function(response) {
            $('#editledgerId').val(response.id);
            $('#editledgerName').val(response.ledger);
            $('#editNote').val(response.note);
            $('#editClass').val(response.class);
            $('#editGroup').val(response.group);
            $('#editAmount').val(response.amount);
            $('#ledger-form-modal').modal('show');
        }
    });
});



document.getElementById('editGroup').addEventListener('change', function () {
    const customInput = document.getElementById('customGroupInput');

    if (this.value === '__custom__') {
        customInput.style.display = 'block';
        customInput.required = true;
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
});




</script>


<script>
  (function () {
    const select = document.getElementById('editGroup');

    select.addEventListener('change', function () {
      if (this.value === '__new__') {
        const entered = window.prompt('Enter a new group name:') || '';
        const val = entered.trim();

        if (val.length === 0) {
          // Reset to placeholder if nothing entered
          this.value = '';
          return;
        }

        // If this custom value already exists, just select it
        const exists = Array.from(this.options).some(o => o.value.toLowerCase() === val.toLowerCase());
        if (exists) {
          this.value = Array.from(this.options).find(o => o.value.toLowerCase() === val.toLowerCase()).value;
          return;
        }

        // Create and select a new option
        const opt = new Option(val, val, true, true);
        // Insert new option before the "+ Add new" option (last one)
        this.add(opt, this.options[this.options.length - 1]);
      }
    });
  })();
</script>
   
@endpush