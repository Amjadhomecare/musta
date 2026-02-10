@extends('keen')
@section('content')
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">

        <!--begin::Page heading-->
        <h2 class="mb-8 fw-bold">Trial Balance</h2>
        <!--end::Page heading-->

        <!--begin::Filter card-->
        <div class="card shadow-sm mb-10">
            <div class="card-body">
                <form action="{{ route('viewTrialBalanceCntl') }}" method="GET" class="row gx-5 gy-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="start_date" class="form-label fw-semibold mb-1">Start&nbsp;Date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm form-control-solid" />
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="end_date" class="form-label fw-semibold mb-1">End&nbsp;Date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm form-control-solid" />
                    </div>
                    <div class="col-12 col-lg-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-light-primary px-10 w-100 w-lg-auto">
                            <i class="ki-duotone ki-filter fs-2 me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Filter card-->

        <!--begin::Trial balance accordion-->
        <div class="accordion accordion-icon-toggle" id="tb_accordion">
            @php $totalDebit = 0; $totalCredit = 0; @endphp

            @foreach ($structuredBalances as $class => $groups)
                @php
                    $classDebit = 0; $classCredit = 0;
                    foreach ($groups as $ledgers) {
                        foreach ($ledgers as $l) {
                            $l['balance'] > 0 ? $classDebit += $l['balance'] : $classCredit += abs($l['balance']);
                        }
                    }
                @endphp

                <!--begin::Class item-->
                <div class="accordion-item mb-4">
                    <!--begin::Header-->
                    <div class="accordion-header py-3 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#tb_{{ Str::slug($class) }}" aria-expanded="false">
                        <span class="accordion-icon"><i class="ki-duotone ki-arrow fs-3"></i></span>
                        <h3 class="fs-5 fw-bold flex-grow-1">{{ $class }}</h3>
                        <div class="d-flex flex-column text-end">
                            <span class="text-gray-600">Debit&nbsp;: <strong class="text-success">{{ number_format($classDebit,2) }}</strong></span>
                            <span class="text-gray-600">Credit: <strong class="text-danger">{{ number_format($classCredit,2) }}</strong></span>
                        </div>
                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div id="tb_{{ Str::slug($class) }}" class="accordion-collapse collapse" data-bs-parent="#tb_accordion">
                        <div class="accordion-body py-4 px-0">
                            <div class="table-responsive">
                                <table class="table table-row-dashed fs-7 gy-2 w-100">
                                    <thead class="text-gray-700 fw-bold text-uppercase bg-light">
                                        <tr>
                                            <th class="min-w-150px">Group</th>
                                            <th class="min-w-250px">Ledger</th>
                                            <th class="text-end min-w-100px">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groups as $group => $ledgers)
                                            @php $groupBalance = 0; @endphp
                                            @foreach ($ledgers as $ledger)
                                                @php
                                                    $groupBalance += $ledger['balance'];
                                                    $ledger['balance'] > 0 ? $totalDebit += $ledger['balance'] : $totalCredit += abs($ledger['balance']);
                                                @endphp
                                                <tr>
                                                    <td>{{ $group }}</td>
                                                    <td>
                                                        <a target="_blank" href="/search/ledger?date_start={{ $startDate }}&date_end={{ $endDate }}&selected_ledger={{ $ledger['ledger'] }}">
                                                            {{ $ledger['ledger'] }}
                                                        </a>
                                                    </td>
                                                    <td class="text-end">{{ number_format($ledger['balance'],2) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fw-bold text-gray-800">
                                                <td colspan="2">Total&nbsp;{{ $group }}</td>
                                                <td class="text-end">{{ number_format($groupBalance,2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Class item-->
            @endforeach
        </div>
        <!--end::Trial balance accordion-->

        <!--begin::Total card-->
        <div class="card card-flush mt-10">
            <div class="card-body">
                <div class="row g-0 text-center">
                    <div class="col-6 border-end">
                        <span class="fs-4 fw-bold">Total&nbsp;Debit</span>
                        <div class="fs-3 text-success fw-bold mt-1">{{ number_format($totalDebit,2) }}</div>
                    </div>
                    <div class="col-6">
                        <span class="fs-4 fw-bold">Total&nbsp;Credit</span>
                        <div class="fs-3 text-danger fw-bold mt-1">{{ number_format($totalCredit,2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Total card-->

    </div>
    <!--end::Content container-->
</div>
<!--end::Content wrapper-->
@endsection