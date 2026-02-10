@extends('keen')
@section('content')

<div class="container my-10" style="max-width: 900px;">
    <div class="card card-flush shadow-sm border">
        <div class="card-header bg-primary text-white text-center">
            <h3 class="card-title fs-3 mb-0">
                {{ $details_jv[0]->voucher_type }} #{{ $details_jv[0]->refCode }} #PV 00{{ $details_jv[0]->refNumber }}
            </h3>
            <small class="d-block mt-1 fs-6">Date: {{ $details_jv[0]->date }}</small>
        </div>

        <div class="card-body p-5">
            <div class="table-responsive">
                <table class="table table-bordered border-primary">
                    <thead class="fw-bold">
                        <tr>
                            <th class="px-3">Type</th>
                            <th class="px-3">Account</th>
                            <th class="text-end px-3">Debit</th>
                            <th class="text-end px-3">Credit</th>
                            <th class="px-3">Maid</th>
                            <th class="px-3">Notes</th>
                            <th class="px-3">Created At</th>
                            <th class="px-3">Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details_jv as $transaction)

                        <tr>
                            <td class="px-3">{{ $transaction->type }}</td>
                            <td class="px-3">{{ $transaction->accountLedger?->ledger }}</td>
                            <td class="text-end px-3">{{ $transaction->type == 'debit' ? number_format($transaction->amount, 2) : '0.00' }}</td>
                            <td class="text-end px-3">{{ $transaction->type == 'credit' ? number_format($transaction->amount, 2) : '0.00' }}</td>
                            <td class="px-3">{{ $transaction?->maidRelation?->name }}</td>
                            <td class="px-3">{{ $transaction->notes }}</td>
                            <td class="px-3">{{ $transaction->created_at }}</td>
                            <td class="px-3">{{ $transaction->updated_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="fw-semibold mt-4">Created by: <span class="">{{ $details_jv[0]->created_by }}</span></p>

            <div class="row mt-8">
                <div class="col-6 text-center">
                    <p class="fw-semibold mb-2">Authorized Signature</p>
                    <div class="border-bottom border-dark mx-auto" style="width: 80%; height: 2rem;"></div>
                    <p class="mt-1">Mr.</p>
                </div>
                <div class="col-6 text-center">
                    <p class="fw-semibold mb-2">Receiver Signature</p>
                    <div class="border-bottom border-dark mx-auto" style="width: 80%; height: 2rem;"></div>
                    <p class="mt-1">Mr/Mrs.</p>
                </div>
            </div>

            @if(!empty($details_jv[0]->extra))
            <div class="text-center my-4">
                <a href="{{ $details_jv[0]->extra }}" target="_blank" class="btn btn-outline-success btn-sm">
                    <i class="ki-outline ki-eye"></i> View Attachment
                </a>
            </div>
            @endif
        </div>

        <div class="card-footer text-center">
            <p class="text-muted small mb-2">&copy; {{ date('Y') }} {{ env('company_name') }}</p>
            <button class="btn btn-primary btn-sm d-print-none" onclick="window.print()">
                <i class="ki-outline ki-printer fs-4 me-1"></i> Print Voucher
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            box-shadow: none;
        }
        .d-print-none {
            display: none !important;
        }
    }
</style>

@endsection
