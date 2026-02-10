@extends('keen')
@section('content')
@include('partials.nav_maid')

{{-- Local polish for this page only --}}
@push('styles')
<style>
  .card-kpi {
    border: 0;
    background: linear-gradient(135deg, rgba(246,248,250,.9), rgba(237,241,247,.9));
    backdrop-filter: blur(2px);
  }
  .card-kpi .symbol {
    background: rgba(255,255,255,.7);
  }
  .kpi-amount {
    letter-spacing: .3px;
  }
  .table-sticky thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #fff;
  }
  .table-hover tbody tr:hover {
    background-color: #f9fbff !important;
  }
  .badge-chip {
    border-radius: 999px;
    padding: .4rem .7rem;
    font-weight: 600;
  }
  .subtle {
    color: #7e8299;
    font-size: .85rem;
  }
  .section-title {
    display:flex; align-items:center; gap:.6rem;
  }
  .ribbon {
    position:absolute; right:14px; top:14px;
    padding:.35rem .6rem; border-radius:.5rem;
    font-size:.75rem; font-weight:700;
    background:#eef6ff; color:#0969da;
  }
  .ribbon.negative { background:#fff1f2; color:#e11d48; }
  .table tfoot td { background:#fafbfc; }
  .divider {
    height:1px; background: #eef0f3; margin: 1rem 0;
  }

  /* ───────── Print Styling ───────── */
  @media print {
    @page {
      size: A4;
      margin: 1.5cm;
    }
    body {
      -webkit-print-color-adjust: exact !important;
      color-adjust: exact !important;
      background: #fff;
    }
    .no-print, nav, .app-sidebar, .app-header {
      display: none !important;
    }
    .app-content, .app-container {
      padding: 0 !important;
      margin: 0 !important;
    }
    .card {
      box-shadow: none !important;
      page-break-inside: avoid;
    }
    .ribbon {
      position: static;
      float: right;
      margin-bottom: .5rem;
    }
    .table {
      font-size: 0.9rem;
    }
  }
</style>
@endpush

<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container">

    {{-- ===== Title & Print Button ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-6">
      <h3 class="fw-bold mb-0">Financial Analysis — {{ $name }}</h3>
      <button onclick="window.print()" class="btn btn-sm btn-primary no-print">
        <i class="fa-solid fa-print me-2"></i> Print Report
      </button>
    </div>

    {{-- ===== Title & Context Card ===== --}}
    <div class="card card-flush shadow-sm mb-8 position-relative">
      @php
        $profitNumber = ($profit ?? (($summary->total_revenue ?? 0) + ($summary->total_expenses ?? 0)));
      @endphp
      <div class="ribbon {{ $profitNumber < 0 ? 'negative' : '' }}">
        {{ $profitNumber < 0 ? 'Loss' : 'Profit' }}
      </div>
      <div class="card-body py-6 d-flex flex-wrap align-items-center justify-content-between gap-4">
        <div>
          <div class="section-title">
            <i class="ki-duotone ki-graph-up fs-2 text-primary">
              <span class="path1"></span><span class="path2"></span>
            </i>
            <h4 class="mb-0">Overview</h4>
          </div>
          <div class="subtle mt-2">
            Interactive P&L overview per maid with Package 4 contract counters and per-ledger breakdown.
          </div>
        </div>

        {{-- Quick legend --}}
        <div class="d-flex align-items-center gap-3 no-print">
          <span class="badge badge-light-success badge-chip">Revenue</span>
          <span class="badge badge-light-danger badge-chip">Expenses</span>
          <span class="badge badge-light-secondary badge-chip">Non-P&L</span>
        </div>
      </div>
    </div>

    {{-- ===== KPI Row (with Pkg 4 Counting Days) ===== --}}
    <div class="row g-6 mb-8">
      {{-- Revenue --}}
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi shadow-sm h-100">
          <div class="card-body py-6 px-6">
            <div class="text-success fw-semibold">Total Revenue</div>
            <div class="kpi-amount fs-2 fw-bolder mt-1">
              {{ number_format($summary->total_revenue ?? 0, 2) }}
            </div>
            <div class="subtle mt-1">class = <span class="fw-semibold">Revenue</span></div>
          </div>
        </div>
      </div>

      {{-- Expenses --}}
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi shadow-sm h-100">
          <div class="card-body py-6 px-6">
            <div class="text-danger fw-semibold">Total Expenses</div>
            <div class="kpi-amount fs-2 fw-bolder mt-1">
              {{ number_format($summary->total_expenses ?? 0, 2) }}
            </div>
            <div class="subtle mt-1">class = <span class="fw-semibold">Expenses</span></div>
          </div>
        </div>
      </div>

      {{-- Net Profit --}}
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi shadow-sm h-100">
          <div class="card-body py-6 px-6">
            <div class="text-primary fw-semibold">Net Profit / (Loss)</div>
            <div class="kpi-amount fs-2 fw-bolder mt-1">
              {{ number_format($profitNumber, 2) }}
            </div>
            <div class="subtle mt-1">From <span class="fw-semibold">Revenue + Expenses</span></div>
          </div>
        </div>
      </div>

      {{-- Pkg 4 Counting Days --}}
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-kpi shadow-sm h-100">
          <div class="card-body py-6 px-6">
            <div class="text-info fw-semibold">Pkg 4 Counting Days</div>
            <div class="kpi-amount fs-2 fw-bolder mt-1">
              {{ (int)($pkg4CountingDays ?? 0) }}
            </div>
            
          </div>
        </div>
      </div>
    </div>

    {{-- ===== Package 4 — Contracts Summary & Ledger Breakdown (Side by Side) ===== --}}
    <div class="row g-6 mb-8">
      {{-- ===== Package 4 — Contracts Summary ===== --}}
      <div class="col-12 col-lg-6">
        <div class="card card-flush shadow-sm h-100">
          <div class="card-header border-0 pt-6">
            <div class="section-title">
              <i class="ki-duotone ki-briefcase fs-2 text-gray-700">
                <span class="path1"></span><span class="path2"></span>
              </i>
              <h5 class="card-title fw-bold mb-0">Package 4 — Contracts Summary</h5>
            </div>
          </div>

          <div class="divider"></div>

          <div class="card-body pt-0">
            <div class="table-responsive">
              <table class="table table-sticky align-middle table-row-dashed table-hover gy-4">
                <thead class="text-muted fw-semibold border-bottom">
                  <tr>
                    <th>Contract Date</th>
                    <th>Returned Date</th>
                    <th class="text-end">Counting Days</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pkg4Contracts as $row)
                    <tr>
                      <td>{{ $row->contract_date ? \Carbon\Carbon::parse($row->contract_date)->format('Y-m-d') : '—' }}</td>
                      <td>
                        @if($row->returned_date)
                          {{ \Carbon\Carbon::parse($row->returned_date)->format('Y-m-d') }}
                        @else
                          <span class="badge badge-light-info badge-chip">In Progress</span>
                        @endif
                      </td>
                      <td class="text-end fw-semibold">{{ number_format((int)($row->counting_days ?? 0)) }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted py-10">
                        No Package 4 contracts found for {{ $name }}.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
                @if(($pkg4Contracts ?? collect())->count() > 0)
                  <tfoot>
                    <tr class="fw-bold">
                      <td colspan="2" class="text-end">Total Counting Days</td>
                      <td class="text-end">{{ number_format((int)($pkg4CountingDays ?? 0)) }}</td>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- ===== Ledger Breakdown ===== --}}
      <div class="col-12 col-lg-6">
        <div class="card card-flush shadow-sm h-100">
          <div class="card-header border-0 pt-6">
            <div class="section-title">
              <i class="ki-duotone ki-notepad fs-2 text-primary">
                <span class="path1"></span><span class="path2"></span>
              </i>
              <h5 class="card-title fw-bold mb-0">Ledger Breakdown</h5>
            </div>
          </div>

          <div class="divider"></div>

          <div class="card-body pt-0">
            <div class="table-responsive">
              <table class="table table-sticky align-middle table-row-dashed table-hover gy-4">
                <thead class="text-muted fw-semibold border-bottom">
                  <tr>
                    <th>Ledger</th>
                    <th>Class</th>
                    <th class="text-end">Signed Total (AED)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($perLedger as $row)
                    <tr>
                      <td class="fw-semibold">{{ $row->ledger }}</td>
                      <td>
                        <span class="badge
                          @if($row->class === 'Revenue') badge-light-success
                          @elseif($row->class === 'Expenses') badge-light-danger
                          @elseif($row->class === 'Assets') badge-light-info
                          @elseif($row->class === 'Liabilities') badge-light-warning
                          @elseif($row->class === 'Equity') badge-light-dark
                          @else badge-light-secondary @endif">
                          {{ $row->class ?? '—' }}
                        </span>
                      </td>
                      <td class="text-end fw-semibold">
                        {{ number_format($row->signed_total ?? 0, 2) }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted py-10">
                        No ledger movements found for {{ $name }}.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
