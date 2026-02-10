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

<style>
    .card-header {
        background-color: #333;
        color: black;
        font-size: 1.25rem;
        font-weight: 600;
    }


    .btn-danger {
        background-color: #c9302c;
        border-color: #ac2925;
    }

    .btn-danger:hover {
        background-color: #ac2925;
        border-color: #761c19;
    }

    .btn-success, .btn-primary {
        border-radius: 5px;
    }


    .installment label {
        font-weight: 500;
    }

    .installment .col-md-3,
    .installment .col-md-2,
    .installment .col-md-1 {
        padding-right: 5px;
        padding-left: 5px;
    }

    /* Container styling */
    .container {
        max-width: 800px;
    }

</style>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            Contract Details for Customer: {{ $contract->customerInfo->name  ?? 'N/A' }} | 
            Contract Ref: {{ $randomRefNumber ?? 'N/A' }}
        </div>
        
        <div class="card-body">
            <form action="{{ route('joinNewMaidCategory4ContractCntl') }}" method="POST" id="installmentsForm">
                @csrf
                @method('POST')

                <input name="selected_customer" type="hidden" value="{{ $contract->customerInfo->name }}">
                <input name="new_contract_ref" type="hidden" value="{{ $randomRefNumber }}">
                <input name="old_contract_ref" readOnly type="input" value="{{ $contract['Contract_ref'] }}">

                <div class="mb-4">
                    <label for="date" class="form-label">New maid Start Date:</label>
                    <input readOnly type="date" value="{{ $today }}" id="date" name="contract_date" class="form-control">
                </div>
                
                <div class="mb-4">
                    <label for="selected_maid" class="form-label">Select a New Maid:</label>
                    <select name="selected_maid" class="form-control" id="selected_maid" data-toggle="select2">
                        <option disabled selected value="">Select the New Maid</option>
                        @foreach ($maids as $maid)
                            <option value="{{ $maid->name }}">{{ $maid->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="installmentsContainer" data-customer="{{ $copyUpcomingInstallment[0]['customer'] ?? '' }}" data-contract="{{ $copyUpcomingInstallment[0]['contract'] ?? '' }}" data-maid="{{ $copyUpcomingInstallment[0]['countractRef']['maid'] ?? '' }}">
                    <p><strong>Remaining Installments from Previous Contract:</strong></p>

                    @foreach($copyUpcomingInstallment as $installment)
                        <div class="row installment align-items-center">
                            <input type="hidden" name="installments[{{ $installment['id'] }}][newContractRef]" value="{{ $randomRefNumber }}">
                            <input type="hidden" name="installments[{{ $installment['id'] }}][customer]" value="{{ $copyUpcomingInstallment[0]['customer'] }}">

                            <div class="col-md-3 mb-2">
                                <label>Accrued Date:</label>
                                <input type="date" class="form-control" name="installments[{{ $installment['id'] }}][accrued_date]" value="{{ $installment['accrued_date'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Note:</label>
                                <input type="text" class="form-control" name="installments[{{ $installment['id'] }}][note]" value="{{ $installment['note'] }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>Cheque:</label>
                                <input type="number" class="form-control" name="installments[{{ $installment['id'] }}][cheque]" value="{{ $installment['cheque'] }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>Amount:</label>
                                <input type="number" class="form-control" name="installments[{{ $installment['id'] }}][amount]" value="{{ $installment['amount'] }}">
                            </div>
                            <div class="col-md-2 text-center">
                                <a href="{{ route('deleteUpcomingInstallment', $installment['id']) }}" class="btn btn-danger btn-sm mt-3">Delete</a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="addNewInstallment" class="btn btn-success mt-3">Add New Installment</button>
                <button type="submit" class="btn btn-primary mt-3">Join New Contract</button>
            </form>
        </div>
    </div>
</div>

<script>
    
document.getElementById('addNewInstallment').addEventListener('click', function() {
    let container = document.getElementById('installmentsContainer');
    let newIndex = container.querySelectorAll('.installment').length;

    let newInstallment = document.createElement('div');
    newInstallment.classList.add('row', 'installment', 'align-items-center');

    newInstallment.innerHTML = `
        <input type="hidden" name="installments[${newIndex}][newContractRef]" value="{{ $randomRefNumber }}">
        <input type="hidden" name="installments[${newIndex}][customer]" value="{{ $contract['customer'] }}">
        
        <div class="col-md-3 mb-2">
            <label>Accrued Date:</label>
            <input type="date" class="form-control" name="installments[${newIndex}][accrued_date]">
        </div>
        <div class="col-md-3 mb-2">
            <label>Note:</label>
            <input type="text" class="form-control" name="installments[${newIndex}][note]">
        </div>
        <div class="col-md-2 mb-2">
            <label>Cheque:</label>
            <input type="number" class="form-control" name="installments[${newIndex}][cheque]">
        </div>
        <div class="col-md-2 mb-2">
            <label>Amount:</label>
            <input type="number" class="form-control" name="installments[${newIndex}][amount]">
        </div>
        <div class="col-md-2 text-center">
            <button type="button" class="btn btn-danger btn-sm mt-3" onclick="removeAccountEntry(this)">Remove</button>
        </div>
    `;
    container.appendChild(newInstallment);
});

function removeAccountEntry(button) {
    button.closest('.installment').remove();
}





</script>

@endsection

@push('scripts')
<script>
        $(document).ready(function() {
            // Initialize Select2 on the maid select element
            $('select[data-toggle="select2"]').select2();
        });
    </script>
@endpush
