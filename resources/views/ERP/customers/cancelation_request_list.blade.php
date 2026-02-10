@extends('keen')
@section('content')

@include('partials.nav_customer')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid">
  <!--begin::Content container-->
  <div id="kt_app_content_container" class="app-container container-xxl">

    {{-- ───────── Complaints card ───────── --}}
    <div class="card card-flush shadow-sm">

      <!-- Header -->
      <div class="card-header">
        <h4 class="card-title mb-0 flex-grow-1 text-center"
            id="customer-name"
            data-name="{{ $name }}">
          Customer&nbsp;All Subscriptions: {{ $name }}
        </h4>
      </div>

      <!-- Body -->
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ki-outline ki-check-circle fs-2 text-success me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if($directDebit->isEmpty())
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="ki-outline ki-information-5 fs-2 me-3"></i>
            <div>No direct debit subscriptions found for this customer.</div>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
              <thead>
                <tr class="fw-bold text-muted bg-light">
                  <th class="min-w-100px">Reference</th>
                  <th class="min-w-100px">Frequency</th>
                  <th class="min-w-120px">Fixed Amount</th>
                  <th class="min-w-120px">Commences On</th>
                  <th class="min-w-120px">Expires On</th>
                  <th class="min-w-150px">Account Title</th>
                  <th class="min-w-100px">IBAN</th>
                  <th class="min-w-150px">Bank</th>
                  <th class="min-w-100px">Status</th>
                  <th class="min-w-100px">Active</th>
                  <th class="min-w-300px text-center">Cancellation Request</th>
                </tr>
              </thead>
              <tbody>
                @foreach($directDebit as $dd)
                  <tr>
                    <td>
                      <span class="text-gray-800 fw-bold">{{ $dd->ref }}</span>
                    </td>
                    <td>
                      @php
                        $frequencies = [
                          'M' => 'Monthly',
                          'Q' => 'Quarterly',
                          'Y' => 'Yearly',
                          'W' => 'Weekly',
                        ];
                      @endphp
                      <span class="badge badge-light-primary">
                        {{ $frequencies[$dd->payment_frequency] ?? $dd->payment_frequency }}
                      </span>
                    </td>
                    <td>
                      <span class="text-gray-800 fw-bold">AED {{ number_format($dd->fixed_amount, 2) }}</span>
                    </td>
                    <td>
                      <span class="text-gray-600">{{ $dd->commences_on->format('Y-m-d') }}</span>
                    </td>
                    <td>
                      <span class="text-gray-600">{{ $dd->expires_on->format('Y-m-d') }}</span>
                    </td>
                    <td>
                      <span class="text-gray-800">{{ $dd->account_title }}</span>
                    </td>
                    <td>
                      <span class="text-gray-600 font-monospace small">{{ $dd->iban }}</span>
                    </td>
                    <td>
                      <span class="text-gray-700">{{ $dd->paying_bank_name }}</span>
                    </td>
                    <td>
                      @php
                        $statusBadges = [
                          0 => 'badge-light-primary',   // Created
                          1 => 'badge-light-success',   // Accepted
                          2 => 'badge-light-warning',   // Pending
                          3 => 'badge-light-danger',    // Rejected
                          4 => 'badge-light-info',      // Resign Requested
                        ];
                        $badgeClass = $statusBadges[$dd->status] ?? 'badge-light-secondary';
                      @endphp
                      <span class="badge {{ $badgeClass }}">{{ $dd->status_label }}</span>
                    </td>
                    <td>
                      @if($dd->active == 0)
                        <span class="badge badge-light-success">
                          <i class="ki-outline ki-check-circle fs-7 me-1"></i>
                          Active
                        </span>
                      @else
                        <span class="badge badge-light-danger">
                          <i class="ki-outline ki-cross-circle fs-7 me-1"></i>
                          Cancelled
                        </span>
                      @endif
                    </td>
                    <td>
                      <!-- Separate forms for Cancellation and Refund - Independent of each other -->
                      <div class="row g-4">
                        <!-- Cancellation Request Section -->
                        <div class="col-md-6">
                          @if($dd->cancelationDd && in_array($dd->cancelationDd->task, [\App\Models\CancelationDd::TASK_CANCELATION_ONLY, \App\Models\CancelationDd::TASK_CANCELATION_AND_REFUND]))
                            <!-- Already has a cancellation request -->
                            <div class="card border border-info shadow-sm h-100">
                              <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                  <i class="ki-outline ki-information-5 fs-2 text-info me-2"></i>
                                  <h6 class="fw-bold text-gray-800 mb-0">Cancellation Request</h6>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-2 mb-4">
                                  <span class="badge badge-info">{{ $dd->cancelationDd->status_label }}</span>
                               
                                </div>

                                @if($dd->cancelationDd->note)
                                  <div class="mb-4">
                                    <div class="bg-light-info p-3 rounded">
                                      <small class="text-gray-700">{{ $dd->cancelationDd->note }}</small>
                                    </div>
                                  </div>
                                @endif

                                <div class="text-muted">
                                  <small>
                                    <i class="ki-outline ki-calendar fs-7 me-1"></i>
                                    {{ $dd->cancelationDd->created_at->format('d M Y, h:i A') }}
                                  </small>
                                </div>
                              </div>
                            </div>
                          @else
                            <!-- Cancellation Request Form -->
                            <div class="card border border-danger shadow-sm h-100">
                              <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                  <i class="ki-outline ki-cross-circle fs-2 text-danger me-2"></i>
                                  <h6 class="fw-bold text-gray-800 mb-0">Cancellation Request</h6>
                                </div>
                                
                                <form action="{{ route('cancelation.request.store') }}" method="POST">
                                  @csrf
                                  <input type="hidden" name="dd_id" value="{{ $dd->id }}">
                                  <input type="hidden" name="request_type" value="cancellation">

                                  <div class="mb-4">
                                    <textarea 
                                      name="note" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Enter reason for cancellation..."
                                      required></textarea>
                                  </div>

                                  <button type="submit" class="btn btn-danger w-100">
                                    <i class="ki-outline ki-cross-circle me-1"></i>
                                    Submit Request
                                  </button>
                                </form>
                              </div>
                            </div>
                          @endif
                        </div>

                        <!-- Refund Request Section -->
                        <div class="col-md-6">
                          @if($dd->refundDd)
                            <!-- Already has a refund request -->
                            <div class="card border border-success shadow-sm h-100">
                              <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                  <i class="ki-outline ki-dollar fs-2 text-success me-2"></i>
                                  <h6 class="fw-bold text-gray-800 mb-0">Refund Request</h6>
                                </div>
                                
                                <div class="mb-4">
                                  <span class="badge badge-success">{{ $dd->refundDd->status_label }}</span>
                                </div>

                                <div class="mb-4">
                                  <div class="bg-light-success p-3 rounded">
                                    <div class="fw-bold fs-4 text-gray-800">AED {{ number_format($dd->refundDd->amount, 2) }}</div>
                                  </div>
                                </div>

                                @if($dd->refundDd->note)
                                  <div class="mb-4">
                                    <div class="bg-light p-3 rounded">
                                      <small class="text-gray-700">{{ $dd->refundDd->note }}</small>
                                    </div>
                                  </div>
                                @endif

                                <div class="text-muted">
                                  <small>
                                    <i class="ki-outline ki-calendar fs-7 me-1"></i>
                                    {{ $dd->refundDd->created_at->format('d M Y, h:i A') }}
                                  </small>
                                </div>
                              </div>
                            </div>
                          @else
                            <!-- Refund Request Form -->
                            <div class="card border border-warning shadow-sm h-100">
                              <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-4">
                                  <i class="ki-outline ki-dollar fs-2 text-warning me-2"></i>
                                  <h6 class="fw-bold text-gray-800 mb-0">Refund Request</h6>
                                </div>
                                
                                <form action="{{ route('cancelation.request.store') }}" method="POST">
                                  @csrf
                                  <input type="hidden" name="dd_id" value="{{ $dd->id }}">
                                  <input type="hidden" name="request_type" value="refund">

                                  <div class="mb-3">
                                    <label class="form-label text-gray-700 fw-semibold">Amount (AED)</label>
                                    <input 
                                      type="number" 
                                      name="amount" 
                                      class="form-control" 
                                      placeholder="0.00" 
                                      step="0.01"
                                      min="0"
                                      required>
                                  </div>

                                  <div class="mb-4">
                                    <label class="form-label text-gray-700 fw-semibold">Reason</label>
                                    <textarea 
                                      name="note" 
                                      class="form-control" 
                                      rows="2" 
                                      placeholder="Enter reason for refund..."
                                      required></textarea>
                                  </div>

                                  <button type="submit" class="btn btn-warning w-100">
                                    <i class="ki-outline ki-dollar me-1"></i>
                                    Submit Request
                                  </button>
                                </form>
                              </div>
                            </div>
                          @endif
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

    </div>

     </div>




@endsection

