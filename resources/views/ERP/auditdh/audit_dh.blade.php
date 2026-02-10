@extends('keen')
@section('content')


<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">


             <div class="row">
              
             <div class="col-md-3">
                        <label for="filterFromDate">From Date:</label>
                        <input type="date" id="min-date" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <label for="filterToDate">To Date:</label>
                        <input type="date" id="max-date" class="form-control" />
                    </div>
          

            </div>
        </div>
    </div>
</div>


<div class="container mt-4">
    
    
    <div class="card shadow-sm border-0">
        <div class="card-body" >
            <div class="table-responsive">

                <table id="dh_datatable" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
                    
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>




@endsection

@push('scripts')
    @vite('resources/js/maid_payroll/audit_dh.js')
@endpush

