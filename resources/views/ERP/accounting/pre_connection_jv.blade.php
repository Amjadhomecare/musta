@extends('keen')
@section('content')

<div class="container mt-5">
    <div class="card shadow-sm"> <!-- Added shadow -->
        <div class="card-header  text-white">
            <h3 class="card-title">Pre-connection  Accounts</h3>

            <a href="{{route('listConnectionJv')}}">connection list</a>
        </div>
        <div class="card-body">
            <form method="post" action="{{route('addNewPreConnectionGeneralJVCntl')}}" >
            @csrf

                <!-- Reference Number and Date -->
                <div class="row mb-3">
                    <div hidden class="col">
                        <label for="refNumber" class="form-label">Reference Number:</label>
                        <input type="text" value="" id="refNumber" name="refNumber" class="form-control" readonly>
                    </div>

                    <div class="col">
                        <label for="name_of_connection" class="form-label">Name of connection:</label>
                        <input  pattern="[A-Za-z\s.,!?]*" title="Only English letters, spaces, and basic punctuation are allowed." type="text" value="" id="name_of_connection" name="name_of_connection" class="form-control">
                    </div>
                    <div class="col">
                        <label for="group" class="form-label">Group:</label>
                        <select id="group" name="group" class="form-control">
                           <option value='jv' > jv </option>
                        </select>
                    </div>
                </div>

                <!-- Entry Container -->
                <div id="entryContainer" class="mb-3">
                    <!-- Initial account entry will be added here -->
                </div>

                <!-- Add Entry Button -->
                <div class="mb-3 d-flex justify-content-end">
                    <button type="button" onclick="RecurringJV()" class="btn btn-outline-secondary">
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
                        <div class="fw-bold">Total Debit: <span id="totalDebit" class="text-danger">0.00</span></div>
                    </div>
                    <div class="col">
                        <div class="fw-bold">Total Credit: <span id="totalCredit" class="text-success">0.00</span></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <input  type="submit" value="Submit" class="btn btn-success">
            </form>
        </div>
    </div>
</div>





@endsection

@push('scripts')
 @vite('resources/js/accounts/pre_connect.js')
@endpush

