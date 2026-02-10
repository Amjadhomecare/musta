{{--TODO: Delete This page not use--}}

@extends('keen')
@section('content')


<div class="container mt-10 d-flex justify-content-center">
    <div class="card card-flush shadow-sm col-md-8">
        <!-- Card Header -->
        <div class="card-header py-5">
            <h3 class="card-title">
                <i class="ki-duotone ki-file-text fs-2 me-2"></i> Typing
            </h3>
        </div>

        <!-- Card Body -->
        <div class="card-body py-5">
            <form method="get" action="{{ route('showAccountLedgerStatmentCtrl') }}" class="custom-form">
                @csrf

                <!-- Date Range -->
                <div class="mb-5">
                    <label for="range-datepicker" class="form-label required">Select Date Range</label>
                    <input type="text" id="range-datepicker" name="date_range"
                        class="form-control form-control-solid" placeholder="YYYY-MM-DD to YYYY-MM-DD">
                </div>

                <!-- Ledger Name -->
                <div class="mb-5">
                    <label for="selected_ledger" class="form-label required">Ledger Name</label>
                    <select id="selected_ledger" name="selected_ledger"
                        class="form-select form-select-solid">
                        <!-- Populate options dynamically -->
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-check fs-2 me-2"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@push('scripts')
    @vite('resources/js/accounts/account.js')
@endpush
