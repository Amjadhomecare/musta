@extends('keen')
@section('content')

@include('partials.nav_maid')

<div id="kt_app_content" class="app-content flex-column-fluid mt-1">
  <div id="kt_app_content_container" class="app-container">

    {{-- ===== Title ===== --}}
    <div class="card card-flush shadow-sm mb-6">
      <div class="card-header">
        <h4 class="card-title mb-0 text-center" id="maid-name" data-name="{{ $name }}">
          P4 Progress for: <span class="fw-bold">{{ $name }}</span>
        </h4>
      </div>
    </div>

    {{-- ===== If no visa yet ===== --}}
    @if(empty($visa))
      <div class="alert alert-warning d-flex align-items-start" role="alert">
        <i class="ki-outline ki-information-2 fs-2 me-3"></i>
        <div>
          <div class="fw-semibold">No applications found</div>
          <div class="small text-muted">This maid has no ApplyVisa record yet. Create one to see progress steps.</div>
        </div>
      </div>
    @else

      {{-- ===== Latest ApplyVisa summary (service/note/date/current step) ===== --}}
      <div class="card card-flush shadow-sm mb-6">
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
          <div>
            <div class="text-muted small">Service</div>
            <div class="badge badge-light-primary fs-7">
              {{ $serviceLabel }}
            </div>
          </div>

             <div>
            <div class="text-muted small">Current Step</div>
            <div class="badge badge-light-info fs-7">
              {{ $latestStatusLabel ?? '—' }}
            </div>
          </div>


                  <div>
                    <div class="text-muted small">Apply Date</div>
                    <div class="fw-semibold">
                      {{ $visaDate ?? '—' }}
                    </div>
                  </div>

                  <div class="flex-grow-1">
                    <div class="text-muted small">Note</div>
                    <div class="fw-semibold">
                      {{ \Illuminate\Support\Str::limit($visaNote ?? '—', 220) }}
                    </div>
                  </div>

                  <div>
              <div>
              <div class="text-muted small">Application ID</div>
              <div class="fw-semibold">
                <a href="{{ url('/vue/apply-visa?search='.$name) }}" class="text-primary text-decoration-underline">
                  #{{ $visa->id }}
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- ===== Fancy Stepper (scoped to latest ApplyVisa logs) ===== --}}
      <div class="card card-flush shadow-sm mb-10">
        <div class="card-body py-6">
          <div class="overflow-auto">
            <ul class="steps d-flex align-items-center gap-6 list-unstyled mb-0 pb-2 min-w-100">
              @foreach($steps as $step)
                @php
                  $state = $step['rejected'] && $step['reached'] ? 'danger'
                         : ($step['reached'] ? 'success'
                         : ($step['current'] ? 'primary' : 'secondary'));
                @endphp

                <li class="d-flex flex-column align-items-center text-center">
                  <div class="step-circle rounded-circle border
                    @if($state==='success') bg-success-subtle border-success text-success
                    @elseif($state==='primary') bg-primary-subtle border-primary text-primary
                    @elseif($state==='danger') bg-danger-subtle border-danger text-danger
                    @else bg-secondary-subtle border-secondary text-secondary
                    @endif
                    fw-bold"
                    style="width:52px;height:52px; display:grid; place-items:center;">
                    {{ $step['value'] }}
                  </div>
                  <div class="mt-2 fw-semibold small" title="{{ $step['label'] }}">
                    {{ \Illuminate\Support\Str::limit($step['label'], 22) }}
                  </div>
                  @if($step['timestamp'])
                    <div class="text-muted xsmall">
                      {{ $step['timestamp'] }}
                    </div>
                  @endif
                </li>

                @if(!$loop->last)
                  <li class="flex-fill">
                    <div class="progress" style="height:6px;">
                      <div class="progress-bar
                        @if($state==='success' || $state==='primary') bg-primary @else bg-secondary @endif"
                        role="progressbar" style="width:100%"></div>
                    </div>
                  </li>
                @endif
              @endforeach
            </ul>
          </div>

          {{-- Legend --}}
          <div class="d-flex flex-wrap gap-3 mt-4">
            <span class="badge badge-light-success">Completed</span>
            <span class="badge badge-light-primary">Current</span>
            <span class="badge badge-light-secondary">Upcoming</span>
            <span class="badge badge-light-danger">Rejected</span>
          </div>
        </div>
      </div>

      {{-- ===== Timeline (all logs for latest ApplyVisa) ===== --}}
      <div class="card card-flush shadow-sm">
        <div class="card-header">
          <h5 class="card-title mb-0">Status Timeline</h5>
        </div>
        <div class="card-body">
          @forelse($logs as $log)
            <div class="d-flex gap-3 py-3 border-bottom">
              <div class="symbol symbol-35px symbol-circle bg-light">
                <span class="symbol-label fw-bold">{{ $log->status }}</span>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold">
                  {{ $statusMap[$log->status] ?? ('Status '.$log->status) }}
                  <span class="text-muted small">• {{ $log->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="small text-muted">
                  By: <span class="fw-semibold">{{ $log->created_by ?? 'system' }}</span>
                </div>
                @if($log->comment)
                  <div class="mt-1">{{ $log->comment }}</div>
                @endif
              </div>
            </div>
          @empty
            <div class="text-muted">No status updates yet for this application.</div>
          @endforelse
        </div>
      </div>

    @endif {{-- /has visa --}}
  </div>
</div>
@endsection

