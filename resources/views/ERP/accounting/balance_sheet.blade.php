@extends('keen')
@section('content')


<div class="container mt-4">
    <h2 class="mb-4 text-center">Balance Sheet</h2>

    {{-- Choose end-date --}}
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="input-group">
                    <span class="input-group-text">End Date</span>
                    <input  type="date"
                            name="end_date"
                            class="form-control"
                            value="{{ request('end_date', '2023-12-31') }}">
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        {{-- ───────────────────────────── ASSETS ───────────────────────────── --}}
        <div class="col-md-6">
            <div class="card border-secondary mb-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Assets</h4>
                </div>
                <div class="card-body">
                    @php
                        $assetsTotal   = 0;
                        $assetClasses  = ['Assets', 'Account Receivable' ,'Fixed Assets'];   // NEW: show both classes
                    @endphp

                    @foreach($assetClasses as $assetClass)
                        @if(isset($structuredBalances[$assetClass]))
                            @foreach($structuredBalances[$assetClass] as $subClass => $groups)
                                <h3 class="text-muted">{{ $subClass }}</h3>
                                @foreach($groups as $group => $data)
                                    {{-- Direct AR/AP totals --}}
                                    @if(in_array($group, ['Account Receivable']))
                                        @php $assetsTotal += $data; @endphp
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <strong>{{ $group }}</strong>
                                            <span class="badge bg-success">{{ number_format($data, 2) }}</span>
                                        </div>
                                    {{-- Normal grouped ledgers --}}
                                    @elseif(is_array($data) && isset($data[0]['ledger']))
                                        @php
                                            $entries    = collect($data)->filter(fn($e) => $e['balance'] != 0);
                                            $groupTotal = $entries->sum('balance');
                                            $assetsTotal += $groupTotal;
                                        @endphp

                                        @if($entries->isNotEmpty())
                                            <h6 class="mt-3">
                                                {{ $group }}
                                                <span class="badge bg-secondary">{{ number_format($groupTotal, 2) }}</span>
                                            </h6>

                                            <table class="table table-sm table-bordered mt-2">
                                                <thead>
                                                    <tr>
                                                        <th>Ledger</th>
                                                        <th class="text-end">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($entries as $entry)
                                                        <tr>
                                                            <td>{{ $entry['ledger'] }}</td>
                                                            <td class="text-end">{{ number_format($entry['balance'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    @endif
                                @endforeach
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ──────────────────────── LIABILITIES (and equity) ──────────────────────── --}}
        <div class="col-md-6">
            <div class="card border-danger mb-4">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Liabilities</h4>
                </div>
                <div class="card-body">
                    @php
                        $liabilitiesTotal = 0;
                        $liabilityClasses = ['Liabilities', 'Liability'];     // existing keys
                    @endphp

                    @foreach($liabilityClasses as $liabilityClass)
                        @if(isset($structuredBalances[$liabilityClass]))
                            @foreach($structuredBalances[$liabilityClass] as $subClass => $groups)
                                <h3 class="text-muted">{{ $subClass }}</h3>
                                @foreach($groups as $group => $data)
                                    @if(in_array($group, ['Account Receivable', 'Account Payable']))
                                        @php $liabilitiesTotal += $data; @endphp
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <strong>{{ $group }}</strong>
                                            <span class="badge bg-success">{{ number_format($data, 2) }}</span>
                                        </div>
                                    @elseif(is_array($data) && isset($data[0]['ledger']))
                                        @php
                                            $entries    = collect($data)->filter(fn($e) => $e['balance'] != 0);
                                            $groupTotal = $entries->sum('balance');
                                            $liabilitiesTotal += $groupTotal;
                                        @endphp

                                        @if($entries->isNotEmpty())
                                            <h6 class="mt-3">
                                                {{ $group }}
                                                <span class="badge bg-secondary">{{ number_format($groupTotal, 2) }}</span>
                                            </h6>

                                            <table class="table table-sm table-bordered mt-2">
                                                <thead>
                                                    <tr>
                                                        <th>Ledger</th>
                                                        <th class="text-end">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($entries as $entry)
                                                        <tr>
                                                            <td>{{ $entry['ledger'] }}</td>
                                                            <td class="text-end">{{ number_format($entry['balance'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    @endif
                                @endforeach
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Profit / Loss inside Liabilities --}}
                    @if(isset($structuredBalances['Profit/Loss']) && $structuredBalances['Profit/Loss'] != 0)
                        <div class="d-flex justify-content-between border-top pt-3 mt-4 fw-bold text-primary">
                            <span>Profit / Loss</span>
                            <span>{{ number_format($structuredBalances['Profit/Loss'], 2) }}</span>
                        </div>
                        @php $liabilitiesTotal += $structuredBalances['Profit/Loss']; @endphp
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────────────── SUMMARY TOTALS ───────────────────────────── --}}
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="d-flex justify-content-between border-top pt-3 fw-bold">
                <span>Total Assets</span>
                <span>{{ number_format($assetsTotal, 2) }}</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-between border-top pt-3 fw-bold">
                <span>Total Liabilities</span>
                <span>{{ number_format($liabilitiesTotal, 2) }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
