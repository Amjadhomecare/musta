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

<form action="{{ route('updateConnection') }}" method="POST" class="form-horizontal">
    @csrf
 
    <div class="col-md-3">
                <label for="name-connection" class="form-label">Name</label>
                <input type="text"  class="form-control form-control-sm" id="name-connection" name="name_of_connection" value="{{ $connectionName->name_of_connection ?? '' }}">
    </div>

    @foreach($connection as $conn)
        <div class="row mb-3">

            <div class="col-md-3">
                <label for="type-{{ $conn->id }}" class="form-label">Type</label>
                <select class="form-select form-select-sm" id="type-{{ $conn->id }}" name="type[]">
                    <option value="{{ $conn->type }}" > {{ $conn->type }} </option>
                    <option value='debit'>Debit</option>
                    <option value='credit'>Credit</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="account-{{ $conn->id }}" class="form-label">Account</label>
                <select type="text"  class="form-control form-control-sm" id="account-{{ $conn->id }}" data-toggle="select2" name="account[]" >
                            <option value="{{ $conn->account }}">{{ $conn->account }} </option>

                             @foreach ($ledgers as $ledger )
                               <option value="{{ $ledger->ledger }}">{{$ledger->ledger}}</option>
                             @endforeach
                <select>
            </div>
            <div class="col-md-3">
                <label for="amount-{{ $conn->id }}" class="form-label">Amount</label>
                <input type="number" class="form-control form-control-sm" id="amount-{{ $conn->id }}" name="amount[]" value="{{ $conn->amount }}">
            </div>

          

    <div class="col-md-3 d-flex align-items-center">
        <button type="button" class="btn btn-danger btn-sm remove-row" data-id="{{ $conn->id }}">Remove</button>
    </div>



            <input type="hidden" name="connection_id[]" value="{{ $conn->id }}">
        </div>
    @endforeach
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">Update</button>
    </div>
</form>



@endsection


@push('scripts')


<script>

document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector('form');
    const updateButton = document.querySelector('button[type="submit"]');

    const calculateTotals = () => {
        const types = form.querySelectorAll('select[name="type[]"]');
        const amounts = form.querySelectorAll('input[name="amount[]"]');

        return Array.from(types).reduce((totals, type, index) => {
            const amount = parseFloat(amounts[index].value) || 0;
            if (type.value === 'credit') {
                totals.credit += amount;
            } else if (type.value === 'debit') {
                totals.debit += amount;
            }
            return totals;
        }, { credit: 0, debit: 0 });
    };

    const updateButtonState = () => {
        const totals = calculateTotals();
        updateButton.disabled = totals.credit !== totals.debit;
    };

    const removeRow = (button) => {
        const row = button.closest('.row');
        if (row) {
            const connectionId = row.querySelector('input[name="connection_id[]"]').value;
            if (connectionId) {
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'deleted_connection_ids[]';
                deleteInput.value = connectionId;
                form.appendChild(deleteInput);
            }
            row.remove();
            updateButtonState();
        }
    };

    form.querySelectorAll('.remove-row').forEach((button) => {
        button.addEventListener('click', function () {
            removeRow(button);
        });
    });

    form.addEventListener('change', updateButtonState);

    updateButtonState();
});


</script>   

  <script>
        $(document).ready(function() {
        
            $('select[data-toggle="select2"]').select2();
        });
    </script>
 
@endpush
