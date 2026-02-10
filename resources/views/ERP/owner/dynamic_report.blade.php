@extends('keen')
@section('content')
<div class="container mt-2">
    <h4 class="bg-success text-white p-3 rounded shadow-sm">Cash & Banks</h4>
    <div id="reportCash" class="d-flex flex-wrap">
        <!-- Report result will be injected here -->         
    </div>

    <h4 class="bg-warning text-white p-3 rounded shadow-sm mt-4">Latest three months typing</h4>
    <div id="lastThreeMonths" class="d-flex flex-wrap">
        <!-- Report result will be injected here -->         
    </div>

    <h4 class="bg-success text-white p-3 rounded shadow-sm mt-4">Latest three months package one</h4>  
    <div id="lastThreeMonthsPackage1" class="d-flex flex-wrap">
        <!-- Report result will be injected here -->         
    </div>

    <h4 class="bg-info text-white p-3 rounded shadow-sm mt-4">Latest three months package Four</h4>  
    <div id="lastThreeMonthsPackage4" class="d-flex flex-wrap">
        <!-- Report result will be injected here -->         
    </div>
</div>


  


@endsection

@push('scripts')
    @vite('resources/js/report/report.js')
@endpush
