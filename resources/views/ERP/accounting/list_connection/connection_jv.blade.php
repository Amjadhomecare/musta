
@extends('keen')
@section('content')



<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <table id="connection-table" class="table dt-responsive nowrap w-100">
                            <thead>
                                <tr>                          
                                    <th>Created at</th> 
                                    <th>Name Of Connection </th>
                                    <th>Ledger </th>
                                    <th>Amount</th>
                                    <th>Notes </th>
                                    <th>Action </th>                        
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
 @vite('resources/js/accounts/pre_connect.js')
@endpush

