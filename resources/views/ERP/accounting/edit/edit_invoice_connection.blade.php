@extends('keen')
@section('content')


@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<!-- Begin Form -->
<form action="{{ route('updateConnectionForInvoice') }}" method="post" class="mt-3">
    
    @csrf 

    <div class="col-12 col-md-4">
                    <label class="form-label">Connection Name</label>
                    <input type="text" class="form-control form-control-sm" name="invoice_connection_name" value="{{$connectionName->invoice_connection_name }}" >
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label">Group</label>
                    <select type="text" class="form-control form-control-sm" name="group"  >
                             <option value="{{$connectionName->group }}"> {{$connectionName->group }} </option>
                             <option value="typing">typing</option>
                                  <option value="category4">category4</option>
                                  <option value="category1">category1</option>
                    </select>
    </div>
    @foreach($connection as $item)
        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item['id'] }}">
        <div class="connection-header mb-2 p-2 border rounded bg-light">
            <div class="row g-2">
        
                <div class="col-6 col-md-4">
                    <label class="form-label">Total Credit</label>
                    <input type="amount" class="form-control form-control-sm" name="items[{{ $loop->index }}][total_credit]" value="{{ $item['total_credit'] }}" readonly>
                </div>
            </div>
        </div>

        <div class="connection-detail mb-2 p-2 border rounded bg-secondary">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <label class="form-label">Ledger</label>
                    <select type="text" class="form-control form-control-sm" data-toggle="select2" name="items[{{ $loop->index }}][ledger]">

                      <option value="{{ $item['ledger'] }}">{{ $item['ledger'] }} </option>

                      @foreach ($ledgers as $ledger )
                               <option value="{{ $ledger->ledger }}">{{$ledger->ledger}}</option>
                    @endforeach

                      
                   </select> 

                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Amount</label>
                    <input type="amount" class="form-control form-control-sm" name="items[{{ $loop->index }}][amount]" value="{{ $item['amount'] }}">
                </div>
            </div>
        </div>
    @endforeach
        <div class="mt-3">
            <input type="submit" value="Submit" class="btn btn-primary btn-sm">
        </div>
</form>

<!-- End Form -->




@endsection

@push('scripts')


<script>
    $(document).ready(function() {

        function updateTotalCredit() {
            var totalAmount = 0;
            $('input[name^="items"][name$="[amount]"]').each(function() {
                totalAmount += parseFloat($(this).val()) || 0;
            });
            $('input[name^="items"][name$="[total_credit]"]').val(totalAmount.toFixed(2));
        }

        $(document).on('input', 'input[name^="items"][name$="[amount]"]', function() {
            updateTotalCredit();
        });
    });
</script>

  <script>
        $(document).ready(function() {
        
            $('select[data-toggle="select2"]').select2();
        });
    </script>

@endpush
