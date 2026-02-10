@extends('keen')
@section('content')

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h3 class="card-title">Pre-connection For Invoices</h3>
            <a href="{{route('listConnectionInvoice')}}">pre-connection list</a>
        </div>
        <div class="card-body">
            <form method="post" action="{{route('storeInvoicesPreConnectionsCntl')}}">
                @csrf

                <!-- Name + Group -->
                <div class="row mb-3">
                    <div class="col">
                        <label for="name_of_connection" class="form-label">Name of connection:</label>
                        <input 
                            pattern="[A-Za-z\s.,!?]*" 
                            title="Only English letters, spaces, and basic punctuation are allowed." 
                            type="text" 
                            name="name_of_connection" 
                            id="name_of_connection"  
                            class="form-control">
                    </div>
                    <div class="col">
                        <label for="the_group" class="form-label">Group:</label>
                        <select id="the_group" name="the_group" class="form-control">
                            <option value="typing">Typing</option>
                            <option value="category1">Package 1</option>
                            <option value="non_contract_invoice">Invoice on fly</option>
                        </select>
                    </div>
                </div>

                <!-- Entry Container -->
                <div id="entryContainer" class="mb-3"></div>

                <!-- Add Entry Button -->
                <div class="mb-3 d-flex justify-content-end">
                    <button type="button" onclick="RecurringINV()" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-circle"></i> Add Entry
                    </button>
                </div>

                <!-- Recurring Number -->
                <div class="mb-3">
                    <label for="RecurringNumber" class="form-label">Recurring Number:</label>
                    <input type="number" value="1" id="RecurringNumber" name="Recurring" class="form-control">
                </div>

                <!-- Totals -->
                <div class="row mb-3">
                    <div class="col">
                        <div class="fw-bold">Total Credit: 
                            <span id="totalCredit" class="text-success">0.00</span>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="total_credit" id="hiddenTotalCredit" value="0">

                <!-- Submit -->
                <input type="submit" value="Submit" class="btn btn-success">
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/accounts/inv_connection.js')
@endpush
