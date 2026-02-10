@extends('keen')
@section('content')


@php
use App\Models\General_journal_voucher;
@endphp

     @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
    @endif

<div class="container mt-3">
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h3 class="card-title"> Add Invoice</h3>
        </div>
        <div class="card-body">
         <form method="post" action="{{ route('saveTypingInvoice') }}">
            @csrf
                <!-- Voucher Type -->
                <div class="mb-3">
                    <label for="voucher_type" class="form-label">Voucher type:</label>
                    <select class="form-select" id="voucher_type"   name="typing_invoice" require>
                        <option selected value="Typing Invoice">Typing Tax Invoice</option>
                    </select>
                </div>

              <!--Customers-->
               <div class="mb-3">
                    <label for="selected_customer"  class="form-label">Select Customer</label>
                    <select id="selected_customer" class="form-control" name="selected_customer"></select>
               </div>

                <!-- Connection Dropdown -->
                <div class="mb-3">
                    <label for="connectionSelect"  class="form-label">Select Typing Services</label>
                    <select id="connectionSelect" class="form-control" name="connectionSelect"></select>
               </div>


                <!-- Reference Number and Date -->
                <div class="row mb-3">
                    <div class="col">
                        <label for="refNumber" class="form-label">Reference Number:</label>
                        <input type="text" value="" id="refNumber" name="refNumber" class="form-control" readonly>
                    </div>
                    <div class="col">
                        <label for="date" class="form-label">Date:</label>
                        <input type="date" id="date" value="{{$currentDate}}" name="date_jv" class="form-control">
                    </div>
                </div>

                <!-- Entry Container -->
                <div id="entryContainer" class="mb-3">
                    <!-- Initial account entry will be added here -->
                </div>

                <div class="col mb-1">
                    <div name="total_of_invoice" class="fw-bold">Total Credit:
                        <input type="number" id="totalCredit" name="total_invoice" class="text-success form-control"  readonly>
                    </div>
                </div>


                <!-- Submit Button -->
                <input type="submit" value="Submit" class="btn btn-primary">

            </form>

        </div>
    </div>
</div>


@endsection

@push('scripts')
    @vite('resources/js/typing/typing.js')
@endpush


