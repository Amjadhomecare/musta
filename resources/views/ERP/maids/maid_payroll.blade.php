@extends('keen')

@section('content')
@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container">

    {{-- ───────── Title card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header">
        <h4 class="card-title mb-0 text-center" id="maid-data" data-name="{{ $name }}">
          Payroll&nbsp;History&nbsp;for&nbsp;{{ $name }}
        </h4>
      </div>
    </div>

    {{-- ───────── Payroll table ───────── --}}
    <div class="card card-flush shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-row-dashed fs-6 w-100">
            <thead class="bg-light text-gray-700 fw-bold text-uppercase">
              <tr>
                <th>#</th>
                <th>Accrued&nbsp;Month</th>
                <th>Status</th>
                <th class="text-end">Basic&nbsp;(AED)</th>
                <th>Maid&nbsp;Type</th>
                <th>Payment&nbsp;Method</th>
                <th class="text-end">Working&nbsp;Days</th>
                <th class="text-end">Deduction</th>
                <th class="text-end">Allowance</th>
                <th>Note</th>
                <th>Created&nbsp;By</th>
                <th>Updated&nbsp;At</th>
                <th class="text-end">Net&nbsp;Salary&nbsp;(AED)</th>
              </tr>
            </thead>

            <tbody>
              @forelse($maidPayRoll as $index => $payroll)
                <tr>
                  <td>{{ $index + 1 }}</td>

                  {{-- accrued_month may be DATE; show as Y-m --}}
                  <td>
                    @php
                      $acc = $payroll->accrued_month ?? '';
                      // handle both Carbon string or date string
                      try {
                          $accFmt = $acc ? \Carbon\Carbon::parse($acc)->format('Y-m') : '';
                      } catch (\Exception $e) {
                          $accFmt = $acc;
                      }
                    @endphp
                    {{ $accFmt }}
                  </td>

                  <td>{{ $payroll->status ? ucfirst($payroll->status) : '' }}</td>
                  <td class="text-end">{{ number_format((float)($payroll->basic ?? 0), 2) }}</td>
                  <td>{{ $payroll->maid_type ?? '' }}</td>
                  <td>{{ $payroll->method ? ucfirst($payroll->method) : '' }}</td>
                  <td class="text-end">{{ (int)($payroll->working_dayes ?? 0) }}</td>
                  <td class="text-end">{{ number_format((float)($payroll->deduction ?? 0), 2) }}</td>
                  <td class="text-end">{{ number_format((float)($payroll->allowance ?? 0), 2) }}</td>
                  <td>{{ $payroll->note ?? '' }}</td>
                  <td>{{ $payroll->created_by ?? '' }}</td>

                  <td>
                    @php
                      $upd = $payroll->updated_at ?? '';
                      try {
                          $updFmt = $upd ? \Carbon\Carbon::parse($upd)->format('Y-m-d H:i') : '';
                      } catch (\Exception $e) {
                          $updFmt = $upd;
                      }
                    @endphp
                    {{ $updFmt }}
                  </td>

                  <td class="text-end">{{ number_format((float)($payroll->net_salary ?? 0), 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="13" class="text-center text-muted py-8">
                    No payroll entries found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->
@endsection
