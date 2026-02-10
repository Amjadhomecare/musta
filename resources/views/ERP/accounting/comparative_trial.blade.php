
@extends('keen')

@section('content')
@php
  // Amount formatter with parentheses for negatives
  function amt($v){
    $v = (float)$v;
    $abs = number_format(abs($v), 2);
    return $v < 0 ? "({$abs})" : $abs;
  }
@endphp

<style>
  .bs-card { max-width: 980px; margin: 0 auto; background: var(--bs-card-bg); }
  .bs-header {
    background:#e98a52; /* brand color */
    color:#fff;
    text-transform:uppercase;
    padding:18px 22px;
    border-radius:.65rem .65rem 0 0;
  }
  .bs-title { font-weight:800; letter-spacing:.5px; }

  .bs-table { width:100%; border-collapse:collapse; }
  .bs-table th, .bs-table td { padding:8px 10px; }
  .bs-table thead th {
    text-align:right;
    color: var(--bs-secondary-color);
    font-weight:700;
    border-bottom:2px solid var(--bs-border-color);
  }
  .bs-table thead th:first-child { text-align:left; }

  .lbl { text-align:left; color: var(--bs-body-color); }
  .num { text-align:right; color: var(--bs-body-color); min-width:140px; font-variant-numeric: tabular-nums; }
  .section { font-weight:800; color: var(--bs-emphasis-color); padding-top:14px; }
  .indent { padding-left:20px; }
  .subtotal { font-weight:700; border-top:1px solid var(--bs-border-color); }
  .total { font-weight:800; border-top:2px solid var(--bs-border-color); }
  .note { color: var(--bs-secondary-color); font-size:.9rem; }

  /* Controls look correct in both themes */
  .form-control,
  input[type="date"] {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
    border-color: var(--bs-border-color);
  }
  .btn-light { color: var(--bs-body-color); background-color: var(--bs-tertiary-bg); border-color: var(--bs-border-color); }

  /* Optional: slightly adjust header tone in dark for contrast */
  [data-bs-theme="dark"] .bs-header { background:#c47742; color:#fff; }

  /* Print */
  @media print {
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
  }

  /* Fallback for OS-level dark if app doesn't set data-bs-theme */
  @media (prefers-color-scheme: dark) {
    :root:not([data-bs-theme]) .bs-card { background: var(--bs-card-bg); }
    :root:not([data-bs-theme]) .bs-table thead th {
      color: var(--bs-secondary-color);
      border-bottom: 1px solid var(--bs-border-color);
    }
    :root:not([data-bs-theme]) .lbl,
    :root:not([data-bs-theme]) .num { color: var(--bs-body-color); }
    :root:not([data-bs-theme]) .section { color: var(--bs-emphasis-color); }
    :root:not([data-bs-theme]) .note { color: var(--bs-secondary-color); }
  }
</style>

<div class="container-xxl py-6">
  <div class="card shadow-sm bs-card" id="print-area">
    <div class="bs-header">
      <div class="bs-title">COMPARATIVE TRIAL BALANCE</div>
      <div>As of {{ $end }}</div>
    </div>

    <div class="card-body p-6">
      {{-- Filter --}}
      <form method="get" class="row g-3 mb-5 no-print" action="{{ route('erp.comparative-trial') }}">
        <div class="col-auto">
          <label class="form-label fw-semibold mb-1">Opening (left)</label>
          <input type="date" name="start" class="form-control" value="{{ $start }}">
        </div>
        <div class="col-auto">
          <label class="form-label fw-semibold mb-1">Ending (right)</label>
          <input type="date" name="end" class="form-control" value="{{ $end }}">
        </div>
        <div class="col-auto d-flex align-items-end">
          <button class="btn btn-primary fw-bold">
            <i class="ki-duotone ki-filter fs-2 me-1"></i> Apply
          </button>
        </div>
        <div class="col-auto d-flex align-items-end">
          <a href="{{ route('erp.comparative-trial') }}" class="btn btn-light">Reset</a>
        </div>
        <div class="col-auto d-flex align-items-end">
          <button type="button" onclick="printTrial()" class="btn btn-success">
            <i class="ki-duotone ki-printer fs-2 me-1"></i> Print
          </button>
        </div>
      </form>

      <table class="bs-table mb-4">
        <thead>
          <tr>
            <th class="lbl"></th>
            <th class="num">{{ $start }} <br><span class="text-muted small">(Opening)</span></th>
            <th class="num">{{ $end }} <br><span class="text-muted small">(Ending)</span></th>
            <th class="num">Change increase/<br/>(decrease)</th>
          </tr>
        </thead>
        <tbody>
          {{-- Loop through each CLASS and list its GROUPS --}}
          @forelse($classes as $className => $c)
            <tr><td class="section" colspan="4">{{ $className }}</td></tr>

            @foreach($c['rows'] as $r)
              <tr>
                <td class="lbl indent">{{ $r->ledger_group }}</td>
                {{-- Opening first, then ending --}}
                <td class="num">{{ amt($r->opening_balance) }}</td>
                <td class="num">{{ amt($r->ending_balance) }}</td>
                <td class="num">{{ amt($r->change_amount) }}</td>
              </tr>
            @endforeach

            {{-- Subtotal per class --}}
            <tr>
              <td class="lbl subtotal">Total {{ $className }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_opening']) }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_ending']) }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_change']) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="lbl text-muted">No data for selected dates.</td>
            </tr>
          @endforelse

          {{-- Grand total --}}
          <tr>
            <td class="lbl total">Total</td>
            <td class="num total">{{ amt($totals['opening']) }}</td>
            <td class="num total">{{ amt($totals['ending']) }}</td>
            <td class="num total">{{ amt($totals['change']) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="note">All amounts in AED • Debits(+) Credits(−). Change = {{ $end }} − {{ $start }}.</div>
    </div>
  </div>
</div>

<script>
function printTrial() {
  window.print();
}
</script>
@endsection
