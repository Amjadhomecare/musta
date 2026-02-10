@extends('keen')

@section('content')
@php
    function starPieces($avg) {
        if (!$avg) return [0,0,5];
        $full = floor($avg);
        $fraction = $avg - $full;
        $half = 0;
        if ($fraction >= 0.75) {
            $full += 1;
        } elseif ($fraction >= 0.25) {
            $half = 1;
        }
        $empty = 5 - $full - $half;
        return [$full, $half, $empty];
    }
    function starHtml($avg) {
        [$f,$h,$e] = starPieces($avg ?? 0);
        $html = '';
        for($i=0;$i<$f;$i++) $html .= '<i class="fa fa-star text-warning"></i>';
        if ($h) $html .= '<i class="fa fa-star-half-o text-warning"></i>';
        for($i=0;$i<$e;$i++) $html .= '<i class="fa fa-star-o text-warning"></i>';
        return $html;
    }

    $votes = (int)($stats->votes ?? 0);
@endphp

<style>
  .hc-stars i { font-size: 1rem; vertical-align: -1px; }
  .review-card { border:1px solid rgba(0,0,0,.08); border-radius:.75rem; padding:1rem; }
  .review-card + .review-card { margin-top: .75rem; }
  .muted { color: #6c757d; }
  .grid-2 { display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.5rem 1.25rem; }
  @media (max-width: 768px){ .grid-2 { grid-template-columns: 1fr; } }
</style>

<div class="app-content flex-column-fluid">
  <div class="app-container">

    <div class="card card-flush shadow-sm">
      <div class="card-header">
        <div class="card-title flex-column">
          <h3 class="mb-1">Customer Reviews — {{ $maid->name }}</h3>
          <div class="muted">
            @if($votes > 0)
              <strong>{{ $votes }}</strong> {{ $votes === 1 ? 'review' : 'reviews' }} total
            @else
              No reviews yet.
            @endif
          </div>
        </div>
        <div class="card-toolbar">
          <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">Back</a>
        </div>
      </div>

      @if($votes > 0)
      <div class="card-body">
        {{-- Averages header --}}
        <div class="review-card mb-4">
          <div class="grid-2">
            <div>
              <div><strong>Satisfaction</strong></div>
              <div class="hc-stars">{!! starHtml($stats->avg_satisfied) !!}</div>
              <small class="muted">{{ number_format($stats->avg_satisfied,1) }}/5</small>
            </div>
            <div>
              <div><strong>Cleaning</strong></div>
              <div class="hc-stars">{!! starHtml($stats->avg_cleaning) !!}</div>
              <small class="muted">{{ number_format($stats->avg_cleaning,1) }}/5</small>
            </div>
            <div>
              <div><strong>Cooking</strong></div>
              <div class="hc-stars">{!! starHtml($stats->avg_cooking) !!}</div>
              <small class="muted">{{ number_format($stats->avg_cooking,1) }}/5</small>
            </div>
            <div>
              <div><strong>Childcare</strong></div>
              <div class="hc-stars">{!! starHtml($stats->avg_childcare) !!}</div>
              <small class="muted">{{ number_format($stats->avg_childcare,1) }}/5</small>
            </div>
            <div>
              <div><strong>Communication</strong></div>
              <div class="hc-stars">{!! starHtml($stats->avg_communication) !!}</div>
              <small class="muted">{{ number_format($stats->avg_communication,1) }}/5</small>
            </div>
          </div>
        </div>

        {{-- Each review --}}
        @foreach($reviews as $r)
          <div class="review-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <strong>
                  {{ optional($r->customer)->name ?? 'Customer #'.$r->customer_id }}
                </strong>
                <span class="muted ms-2">
                  {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, H:i') }}
                </span>
              </div>
              <div class="hc-stars">{!! starHtml($r->satisfied) !!}</div>
            </div>

            <div class="grid-2 mt-3">
              <div>
                <div class="muted">Cleaning</div>
                <div class="hc-stars">{!! starHtml($r->perf_cleaning) !!}</div>
              </div>
              <div>
                <div class="muted">Cooking</div>
                <div class="hc-stars">{!! starHtml($r->perf_cooking) !!}</div>
              </div>
              <div>
                <div class="muted">Childcare</div>
                <div class="hc-stars">{!! starHtml($r->perf_childcare) !!}</div>
              </div>
              <div>
                <div class="muted">Communication</div>
                <div class="hc-stars">{!! starHtml($r->perf_communication) !!}</div>
              </div>
            </div>

            @if(!empty($r->note))
              <div class="mt-3">
                <div class="muted mb-1">Note</div>
                <div>{{ $r->note }}</div>
              </div>
            @endif
          </div>
        @endforeach

        <div class="mt-4">
          {{ $reviews->links() }}
        </div>
      </div>
      @endif

    </div>

  </div>
</div>
@endsection
