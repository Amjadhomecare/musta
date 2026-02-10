@extends('keen')
@section('content')

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h3 class="card-title"> Package one </h3>
        </div>
        <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

         <form method="post"  action="{{route('storeCateOneContract')}}">
            @csrf
            

                  <!--Customers-->
               <div class="mb-4">
                    <label for="connection"  class="form-label">Select Customer</label>
                    <select name="selected_customer" id="customer_select"  class="form-control" data-toggle="select2" require>
                        <option disabled selected value="">Customer Name</option>
                  
                    </select>
                </div>

                     <!--Maid-->
               <div class="mb-4">
                    <label for="connection"  class="form-label">Select Maid</label>

                    <select name="maid"  class="form-control" data-toggle="select2" require>
                        <option disabled selected value="">Maid Name</option>
                        @foreach ($maids as $maid)
                            <option value="{{ $maid->name}}">{{$maid->name}}</option>
                        @endforeach
                    </select>
                </div>

             <!-- Connection Dropdown -->
               <div class="mb-4">
                    <label for="connection"  class="form-label">Select service</label>
                    <select  name='connaction' id="connectionSelect" class="form-control" data-toggle="select2" onchange="handleConnectionChange(this.value)">
                        <option disabled selected value="">Select the Connection</option>
                        @foreach ($selectConnection as $connection)
                            <option value="{{$connection->invoice_connection_name}}">{{$connection->invoice_connection_name}}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Reference Number and Date -->
                <div class="row mb-3">
              
                    <div class="col">
                        <label for="date" class="form-label">Date Started:</label>
                        <input type="date" id="date" value="{{$today}}" name="date_start" class="form-control">
                    </div>

                    <div class="col">
                        <label for="date" class="form-label">Date Ended:</label>
                        <input type="date" id="date" value="{{$twoYearsLater}}"  name="date_ended" class="form-control">
                    </div>
                </div>

                <!-- Entry Container -->
                <div id="entryContainer" class="mb-3">
                    <!-- Initial account entry will be added here -->
                </div>

                <div class="col">
                    <div name="total_of_invoice" class="fw-bold">Total Credit: 
                        <input type="text" id="totalCredit" name="total_invoice" class="text-success form-control"  readonly>
                    </div>
                </div>
                

                <!-- Submit Button -->
                <input type="submit" value="Submit" class="btn btn-success">
            </form>
          
          
      
        </div>
    </div>
</div>




@endsection

@push('scripts')
    @vite('resources/js/p1/add_contractp1.js')
    <script>
        $(document).ready(function() {
           
            $('select[data-toggle="select2"]').select2();
        });
    </script>
@endpush





