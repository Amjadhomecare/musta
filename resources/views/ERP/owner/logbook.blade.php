@extends('keen')
@section('content')

<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                        


                <table id="book_datatable" class="table  table-hover" style="width:100%">
                    <thead>
                        <tr>
               
                            <th>Name</th>
                            <th>User</th>
                            <th>details</th>
                            <th>date</th>
           
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
  
@vite(['resources/js/report/book_log.js'])

@endpush
