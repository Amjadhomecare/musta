@extends('keen')
@section('content')


<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <form id="gg" method="get" action="/search/ledger" class="d-flex flex-column">
            <div class="mb-3">
    <label for="date_start" class="form-label">Start Date</label>
    <input type="date" id="date_start" name="date_start" class="form-control" 
           value="{{ old('date_start', $dateFrom ?? '') }}" required>
</div>

                <div class="mb-3">
    <label for="date_end" class="form-label">End Date</label>
    <input type="date" id="date_end" name="date_end" class="form-control" 
           value="{{ old('date_end', $dateTo ?? '') }}" required>
</div>

                <div class="mb-3">
                    <label for="selected_ledger" class="form-label">Ledger Name</label>
                    <select id="selected_ledger" name="selected_ledger" class="form-control" required>
      
                    </select>
                </div>
                <button type="submit" class="btn btn-blue mt-3 align-self-end">Search</button>
            </form>
        </div>
    </div>
</div>

@component('ERP.components.modal',[ 'modal_id' => 'statement_of_account', 'dataBackDrop'=>'true', 'title'=>'Account Statement Request' ] )
<div>
    <form method="get" action="{{ route('viewSearchStatmentAccountCntl') }}" class="px-3">
        <div class="container">
            <div class="row">
                @csrf
                <div class="form-group mb-3">
                    <label for="range-datepicker" class="form-label">Select Date Range</label>
                    <input type="text" id="range-datepicker" class="form-control" name="date_range" placeholder="YYYY-MM-DD to YYYY-MM-DD">
                </div>

                <div class="form-group mb-3">
                    <label for="selected_ledger" class="form-label">Ledger Name:</label>
                    <select id="selected_ledger" class="form-control " name="selected_ledger" style="z-index: 1100 !important; width: 100%!important"></select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endcomponent

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card custom-table">
            <div class="card-header custom-card-header">
                Date : {{$dateFrom}}  To {{$dateTo}} ** Statement of : <span style="color:orange; font-weight: bold;"> {{$nameOfLedger}} </span>  <br><br> 
                Opening Balance: {{ number_format($openingBalance, 2) }} <br>
                Closing Balance: {{ number_format($finalClosingBalance, 2) }} <br>
                Total Debit: {{ number_format($totalDebit, 2) }} <br>
                Total Credit: {{ number_format($totalCredit, 2) }}
            </div>

            <button id="toggleCurrency" type="button" class="btn btn-sm btn-secondary">
                <span id="toggleCurrencyText"> Convert to USD</span>
    </button>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="account_statement" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Reference Number</th>
                                <th>Voucher Type</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Running Balance</th>
                                <th>Maid</th>
                                <th>Notes</th>
                                <th>Created By</th>
                                <th>Updated By</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                            </thead>
                            <tbody>

                            @php
                                $runningBalance = $openingBalance; 
                            @endphp

                            @foreach ($accountName as $item)
                                @php
                                    if ($item->type == 'debit') {
                                        $runningBalance += $item->amount;
                                    } else if ($item->type == 'credit') {
                                        $runningBalance -= $item->amount;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->date }}</td>
                                    <td> <a href='/view/jv/selected/{{ $item->refCode }}' target='_blank'> {{ $item->refCode }} </a></td>
                                    <td>{{ $item->voucher_type }}</td>
                                    <td class="convertible" data-aed="{{ $item->type == 'debit'  ? $item->amount : 0 }}">
                                        {{ number_format($item->type == 'debit'  ? $item->amount : 0, 2) }}
                                    </td>

                                    <td class="convertible" data-aed="{{ $item->type == 'credit' ? $item->amount : 0 }}">
                                        {{ number_format($item->type == 'credit' ? $item->amount : 0, 2) }}
                                    </td>

                                    <td class="convertible" data-aed="{{ $runningBalance }}">
                                        {{ number_format($runningBalance, 2) }}
                                    </td>

                                    <td>{{ $item->maidRelation->name ?? '-' }}</td>
                                    <td>{{ $item->notes }}</td>
                                    <td>{{ $item->created_by }}</td>
                                    <td>{{ $item->updated_by }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td>{{ $item->updated_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite('resources/js/accounts/account.js')
@endpush
