@extends('keen')
@section('content')

<div class="container mt-4">
    
    <div class="card shadow-sm border-0">
        <div class="card-body" ">
            <div class="table-responsive">
                        

            <table id="stripe_payment" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Description</th>
                        <th>Status</th>
                   
                        <th>Stripe maid</th>
                        <th>Created At</th>
                        <th>Billing Email</th>
                        <th>Billing Name</th>
                    </tr>
                </thead>
            </table>

            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
    @vite(['resources/js/stripe/payment.js'])
@endpush


