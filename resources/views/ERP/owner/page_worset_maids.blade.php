@extends('keen')
@section('content')

<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                        


                <table id="worst_maid_datatable" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
               
                            <th>Maid name</th>
                            <th> nationality </th>
                            <th>Number OF return</th>
           
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
  
@vite(['resources/js/report/worst_maid.js'])

@endpush
