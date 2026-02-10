@extends('keen')
@section('content')


@if ($errors->any())
    <div class="alert alert-danger">
        Please fix the following errors:
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
    color: white;
}

.form-control {
    background-color: #f5f5f5;
    color: #333;
}

.btn-danger {
    background-color: #c9302c;
    border-color: #ac2925;
}

.btn-danger:hover {
    background-color: #ac2925;
    border-color: #761c19;
}

/* Additional custom styles as needed */




</style>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-dark text-white">
            Customer: {{ $customer_name->customer ?? 'N/A' }} |
            Contract: {{ $customer_name->Contract_ref ?? 'N/A' }} |
            <label>Remaining Accrued Date</label>
        </div>

        <div class="card-body">
            <form action="{{ route('update-installments') }}" method="POST" id="installmentsForm">
                @csrf
                @method('POST')

                {{-- Carry IDs & context for JS --}}
                <div id="installmentsContainer"
                     data-customer-id="{{ $customer_name->customer_id ?? $customer_name->id ?? '' }}"
                     data-customer-name="{{ $customer_name->customer ?? '' }}"
                     data-contract="{{ $customer_name->Contract_ref ?? '' }}"
                     data-maid="{{ $customer_name->maid ?? '' }}">

                    @foreach($editUpcomingInstallment as $installment)
                        @if($installment->invoice_status == 1)
                            <div class="row mb-3 installment align-items-center">
                                <div class="col-md-3 mb-2">
                                    <label>Accrued:</label>
                                    <input type="date" readonly class="form-control"
                                           name="installments[{{ $installment['id'] }}][accrued_date]"
                                           value="{{ $installment['accrued_date'] }}">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label>Note:</label>
                                    <input type="text" readonly class="form-control"
                                           name="installments[{{ $installment['id'] }}][note]"
                                           value="{{ $installment['note'] }}">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label>Cheque:</label>
                                    <input type="number" readonly class="form-control"
                                           name="installments[{{ $installment['id'] }}][cheque]"
                                           value="{{ $installment['cheque'] }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label>Amount:</label>
                                    <input type="number" readonly class="form-control"
                                           name="installments[{{ $installment['id'] }}][amount]"
                                           value="{{ $installment['amount'] }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <p style="color:green;">Generated</p>
                                </div>

                                <div class="col-md-1 mb-2">
                                    {{-- empty cell to align grid --}}
                                </div>
                            </div>
                        @endif

                        @if($installment->invoice_status == 0)
                            <div class="row mb-3 installment align-items-center">
                                <div class="col-md-3 mb-2">
                                    <label>Accrued:</label>
                                    <input type="date" class="form-control"
                                           name="installments[{{ $installment['id'] }}][accrued_date]"
                                           value="{{ $installment['accrued_date'] }}">
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label>Amount:</label>
                                    <input type="number" class="form-control"
                                           name="installments[{{ $installment['id'] }}][amount]"
                                           value="{{ $installment['amount'] }}">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label>Note:</label>
                                    <input type="text" class="form-control"
                                           name="installments[{{ $installment['id'] }}][note]"
                                           value="{{ $installment['note'] }}">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label>Cheque:</label>
                                    <input type="number" class="form-control"
                                           name="installments[{{ $installment['id'] }}][cheque]"
                                           value="{{ $installment['cheque'] }}">
                                </div>

                                <div class="col-md-1 mb-2">
                                    <a href="{{ route('deleteUpcomingInstallment', $installment['id']) }}"
                                       class="btn btn-danger btn-sm mt-4">Delete</a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <button type="button" id="addNewInstallment" class="btn btn-success mt-2">Add New</button>
                <button type="submit" class="btn btn-primary mt-2">Update All</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addNewInstallment').addEventListener('click', function () {
    const container = document.getElementById('installmentsContainer');
    const customerId = container.getAttribute('data-customer-id');
    const contract = container.getAttribute('data-contract');
    const newIndex = container.querySelectorAll('.installment').length; // unique index

    const newInstallment = document.createElement('div');
    newInstallment.classList.add('row', 'mb-3', 'installment', 'align-items-center');

    newInstallment.innerHTML = `
        <input type="hidden" class="form-control" name="installments[new][${newIndex}][customer_id]" value="${customerId}">
        <input type="hidden" class="form-control" name="installments[new][${newIndex}][contract]" value="${contract}">

        <div class="col-md-3 mb-2">
            <label>Accrued:</label>
            <input type="date" class="form-control" name="installments[new][${newIndex}][accrued_date]">
        </div>

        <div class="col-md-3 mb-2">
            <label>Amount:</label>
            <input type="number" class="form-control" name="installments[new][${newIndex}][amount]">
        </div>

        <div class="col-md-3 mb-2">
            <label>Note:</label>
            <input type="text" class="form-control" name="installments[new][${newIndex}][note]">
        </div>

        <div class="col-md-3 mb-2">
            <label>Cheque:</label>
            <input type="number" class="form-control" name="installments[new][${newIndex}][cheque]">
        </div>

        <div class="col-md-1 mb-2">
            <button onclick="removeAccountEntry(this)" type="button" class="btn btn-danger remove-btn mt-4">Remove</button>
        </div>
    `;

    container.appendChild(newInstallment);
});

function removeAccountEntry(button) {
    const entryRow = button.closest('.installment');
    if (entryRow) entryRow.remove();
}
</script>

@endsection
