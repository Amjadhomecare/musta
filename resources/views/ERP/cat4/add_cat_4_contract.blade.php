@extends('keen')
@section('content')

@if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

<div class="container mt-5">
    <div class="card shadow-sm"> <!-- Added shadow -->
        <div class="card-header  text-white">
            <h3 class="card-title">Package four contract</h3>
        </div>
        <div class="card-body">
            <form method="post" action="{{route('storeCategory4ContractCntl')}}" >
            @csrf
            
                <!-- Reference Number and Date -->
                <div class="row mb-3">
                 
                    <div class="col">
                        <label for="name_of_connection" class="form-label">Contract Ref Nummber:</label>
                        <input readOnly value=P4_{{$randomRefNumber}} type="text" name="contract_ref" id="name_of_connection"  class="form-control">
                    </div>

                    <div class="col">
                        <label for="date" class="form-label">Contract Started Date:</label>
                        <input readOnly type="date" value={{$today}} id="date" name="contract_date" class="form-control">
                    </div>

                             <!--Customers-->
               <div class="mb-4">
                    <label for="connection"  class="form-label">Select Customer</label>

                    <select name="selected_customer" id="customer_select"  class="form-control" data-toggle="select2" require>
                        <option disabled selected value="">Customer Name</option>
                  
                    </select>
                </div>
                                  <!--Maids-->
               <div class="mb-4">
                    <label for="connection"  class="form-label">Select From approved maids</label>

                    <select name="selected_maid"  class="form-control" data-toggle="select2">
                        <option disabled selected value="">select maid</option>
                     @foreach ($maids as $maid )
                            <option value="{{$maid->name}}">{{$maid->name}}</option>
                    @endforeach
                    
                    </select>
                </div>

                  
                </div>
 
                <p>Initial values</p>
                      <!-- initial values -->
                            <div class="col">
                                <label class="form-label">Accured Date MM-DAY-YEAR:</label>
                                <input id="accruedDate" value={{$today}} type="date"  step="0.01" class="form-control">
                            </div>

                            <div class="col">
                                <label class="form-label">Monthly payment(Amount):</label>
                                <input id="accruedAmount" value=0 type="number"  step="0.01"  class="form-control">
                            </div>
                            <div class="col">
                                <label class="form-label">Notes:</label>
                                <input id="note" value="no note" type="text"  class="form-control">
                            </div>

                            <div class="col">
                                <label class="form-label">Cheques:</label>
                                <input id="cheque" value="no cheque"  type="text"  class="form-control">
                            </div>

                            <br> <br> <br>


                <!-- Entry Container -->
                <div id="entryContainer" class="mb-3">
                    <!-- Initial account entry will be added here -->
                </div>

                     <!-- Recurring Number -->
                <div class="mb-3">
                    <label for="RecurringNumber" class="form-label">Recurring Number:</label>
                    <input type="number" value="1" id="RecurringNumber" name="Recurring" class="form-control">
                </div>


                <!-- Add Entry Button -->
                <div class="mb-3 ">
                    <button type="button" onclick="RecurringJV()" id="addMore" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-circle"></i> Add installment Entry
                    </button>
                </div>

           
            
                <!-- Submit Button -->
                <input style="margin-right: 400px;" type="submit" value="Submit Category 4 Contract" class="btn btn-success">
    
            </form>
        </div>
    </div>
</div>

@endsection


@push('scripts')

<script>
        $(document).ready(function() {
            // Initialize Select2 on the maid select element
            $('select[data-toggle="select2"]').select2();
        });
    </script>
    @vite('resources/js/p4/add_contractp4.js')
@endpush

