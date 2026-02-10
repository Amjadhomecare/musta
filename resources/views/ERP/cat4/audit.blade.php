@extends('keen')
@section('content')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <!--begin::Content container-->
  <div id="kt_app_content_container" class="app-container container-fluid">

    {{-- ───────── Toolbar ───────── --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-6">
      <div>
        <h3 class="mb-0">Upcoming Installments</h3>
        <div class="text-gray-600 fs-7">
          Manage pending installments & month rollover
          <span class="badge badge-light-primary ms-2">Branch: {{ config('app.branch') }}</span>
        </div>
      </div>

      {{-- @if(Auth::user()?->group === 'admin') --}}
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light btn-sm" id="refreshBtn">
          <i class="ki-duotone ki-reload fs-3 me-2"></i>
          Refresh
        </button>

        <button id="rolloverOpenModal"
                class="btn btn-primary btn-sm"
                data-bs-toggle="tooltip"
                data-bs-placement="left"
                title="Clone latest installment +1 month for eligible contracts">
          <i class="ki-duotone ki-plus-circle fs-3 me-2"></i>
          Rollover Installments
        </button>
      </div>
      {{-- @endif --}}
    </div>
    {{-- ───────── /Toolbar ───────── --}}

    {{-- ───────── Filters ───────── --}}
    <div class="card shadow-sm mb-7">
      <div class="card-header align-items-center border-0 py-4">
        <h4 class="card-title m-0">Filters</h4>
        <div class="card-toolbar">
          <button class="btn btn-light btn-sm" id="clearFiltersBtn">
            <i class="ki-duotone ki-arrows-circle fs-3 me-2"></i>Clear
          </button>
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="row g-4">
          {{-- From / To --}}
          <div class="col-12 col-md-6 col-lg-3">
            <label for="min-date" class="form-label fw-semibold mb-1">From Date</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="ki-duotone ki-calendar"></i></span>
              <input type="date" id="min-date" class="form-control form-control-sm form-control-solid">
            </div>
          </div>

          <div class="col-12 col-md-6 col-lg-3">
            <label for="max-date" class="form-label fw-semibold mb-1">To Date</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="ki-duotone ki-calendar"></i></span>
              <input type="date" id="max-date" class="form-control form-control-sm form-control-solid">
            </div>
          </div>

          {{-- Contract type --}}
          <div class="col-12 col-md-6 col-lg-2">
            <label for="filterContracts" class="form-label fw-semibold mb-1">Contract Type</label>
            <select id="filterContracts" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="direct hire">Package 5</option>
              <option value="HC">HC</option>
            </select>
          </div>

          {{-- Department --}}
          <div class="col-12 col-md-6 col-lg-2">
            <label for="filterDep" class="form-label fw-semibold mb-1">Department</label>
            <select id="filterDep" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="online">Online</option>
              <option value="sales">From Office</option>
            </select>
          </div>

          {{-- New / Replacement --}}
          <div class="col-12 col-md-6 col-lg-2">
            <label for="filterNew" class="form-label fw-semibold mb-1">New Contract</label>
            <select id="filterNew" class="form-select form-select-sm form-select-solid">
              <option value="">All</option>
              <option value="Join">As Replacement</option>
              <option value="P4">As New Contract</option>
            </select>
          </div>

          {{-- Switch --}}
          <div class="col-12 d-flex align-items-center">
            <div class="form-check form-switch form-check-custom form-check-solid">
              <input class="form-check-input" type="checkbox" id="installmentZero" value="1">
              <label class="form-check-label ms-2 fw-semibold" for="installmentZero">
                Zero Upcoming Installment
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- ───────── /Filters ───────── --}}

    {{-- ───────── Table ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="p4dataTable" class="table table-hover table-row-dashed fs-6 w-100 align-middle">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>Date</th>
                <th>Contract Ref</th>
                <th>Phone</th>
                <th>Customer</th>
                <th>Maid</th>
                <th>Maid Type</th>
                <th>Maid Payment</th>
                <th class="text-end">Maid Salary</th>
                <th>Latest Invoice</th>
                <th class="text-end">Latest Amount</th>
                <th class="text-end">Installment</th>
                <th>Dep</th>
                <th>Created By</th>
              </tr>
            </thead>
            <tbody><!-- DataTables rows --></tbody>
          </table>
        </div>
      </div>
    </div>
    {{-- ───────── /Table ───────── --}}

  </div>
  <!--end::Content container-->
</div>
<!--end::Content wrapper-->

{{-- Rollover Confirm Modal --}}
<div class="modal fade" id="rolloverModal" tabindex="-1" aria-labelledby="rolloverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rolloverModalLabel">Confirm Rollover</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        This will create a new upcoming installment (Accrued&nbsp;Date + 1 month, <code>invoice_status = 0</code>)
        for all <strong>active contracts without a pending invoice</strong>.<br><br>
        Are you sure you want to continue?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="rolloverConfirmBtn" class="btn btn-primary">
          <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
          Run Rollover
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
  <div id="toastBox" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastMsg">Done.</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  @media (min-width: 992px){
    .sticky-toolbar { position: sticky; top: 0; z-index: 3; background: var(--bs-body-bg); }
  }
</style>
@endpush

@push('scripts')
{{-- Expose only the branch from .env --}}
<script>
  window.APP_BRANCH = "{{ config('app.branch') }}";
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Tooltips
  if (window.bootstrap) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  }

  // Toast helper
  const showToast = (message, variant = 'primary') => {
    const box = document.getElementById('toastBox');
    const msg = document.getElementById('toastMsg');
    msg.textContent = message;
    box.className = `toast align-items-center text-bg-${variant} border-0`;
    new bootstrap.Toast(box, { delay: 3500 }).show();
  };

  // DataTable reload helper
  const reloadTable = () => {
    if (window.LaravelDataTables?.p4dataTable?.ajax?.reload) {
      window.LaravelDataTables.p4dataTable.ajax.reload(null, false);
    } else if (window.jQuery?.fn?.DataTable) {
      const dt = jQuery('#p4dataTable').DataTable?.();
      dt?.ajax?.reload?.(null, false);
    }
  };

  // Clear filters
  const clearBtn = document.getElementById('clearFiltersBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      ['min-date','max-date'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
      ['filterContracts','filterDep','filterNew'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
      const zero = document.getElementById('installmentZero'); if (zero) zero.checked = false;
      reloadTable();
    });
  }

  // Refresh button
  const refreshBtn = document.getElementById('refreshBtn');
  if (refreshBtn) refreshBtn.addEventListener('click', reloadTable);

  // Rollover modal logic
  const openBtn = document.getElementById('rolloverOpenModal');
  const modalEl = document.getElementById('rolloverModal');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

  if (openBtn && modal) openBtn.addEventListener('click', () => modal.show());

  // Confirm run
  const runBtn = document.getElementById('rolloverConfirmBtn');
  const runSpinner = runBtn?.querySelector('.spinner-border');

  // PROD URL (switch to localhost for local testing if needed)
  const apiUrl = `https://api.alahliamaids.com/api/UpcomingInstallments/rollover?db=${encodeURIComponent(window.APP_BRANCH)}&user=system`;
  // const apiUrl = `https://localhost:7113/api/UpcomingInstallments/rollover?db=${encodeURIComponent(window.APP_BRANCH)}&user=system`;

  if (runBtn) {
    runBtn.addEventListener('click', async () => {
      try {
        runBtn.disabled = true;
        runSpinner?.classList.remove('d-none');

        const res = await fetch(apiUrl, {
          method: 'POST',
          headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
          const text = await res.text().catch(() => '');
          throw new Error(`HTTP ${res.status} ${res.statusText} — ${text}`);
        }

        const data = await res.json().catch(() => ({}));
        const inserted = data?.inserted ?? 0;

        modal?.hide();
        showToast(`Rollover complete. Inserted: ${inserted}`, inserted > 0 ? 'success' : 'secondary');
        reloadTable();
      } catch (err) {
        console.error(err);
        showToast(`Failed to rollover installments: ${(err?.message ?? 'Unknown error')}`, 'danger');
      } finally {
        runSpinner?.classList.add('d-none');
        runBtn.disabled = false;
      }
    });
  }
});
</script>

@vite('resources/js/p4/audit.js')
@endpush
