
<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-0"> <!-- slight offset from top -->
  <div id="kt_app_content_container" class="app-container" style="max-width:100%;"> <!-- narrower card -->

    {{-- ───────── Maid profile card ───────── --}}
    <div class="card card-flush shadow-sm mb-8">
      @php
        use App\Models\categoryOne;
        use App\Models\Category4Model;
        use App\Models\registerComplaint;
        use App\Models\AsyncSubStripe;
        use App\Models\MaidsDB;

        $latest_customer_p1 = categoryOne::where('maid_id', $maid?->id)->latest()->first();
        $latest_customer_p4 = Category4Model::where('maid_id', $maid?->id)->latest()->first();
      @endphp

@php
use App\Models\CustomerSurviMaid;

$ratingStats = CustomerSurviMaid::where('maid_id', $maid?->id)
    ->selectRaw('
        COUNT(*) as votes,
        AVG(satisfied) as avg_satisfied,
        AVG(perf_cleaning) as avg_cleaning,
        AVG(perf_cooking) as avg_cooking,
        AVG(perf_childcare) as avg_childcare,
        AVG(perf_communication) as avg_communication
    ')
    ->first();

$votes = (int) ($ratingStats->votes ?? 0);

function makeStars($avg) {
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

function renderStars($avg) {
    [$full, $half, $empty] = makeStars($avg);
    $html = '';
    for ($i = 0; $i < $full; $i++) $html .= '<i class="fa fa-star text-warning"></i>';
    if ($half) $html .= '<i class="fa fa-star-half-o text-warning"></i>';
    for ($i = 0; $i < $empty; $i++) $html .= '<i class="fa fa-star-o text-warning"></i>';
    return $html;
}

@endphp


      <div class="card-header py-3">
        <h4 class="card-title mb-0 text-center">{{ $maid->name }}'s&nbsp;Profile</h4>
      </div>

      <div class="card-body">
        <div class="row g-4 align-items-start">

          {{-- Maid image --}}
          <div class="col-12 col-md-auto text-center">
            <img src="{{ $maid->img != 'No data' ? $maid->img : 'https://via.placeholder.com/200' }}"
                 alt="Maid Image" class="img-thumbnail rounded-circle" style="max-width:200px">
          </div>

          {{-- Details --}}
          <div class="col">
            <div class="row g-4">
              <div class="col-md-6">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item bg-transparent"><strong>Status:</strong> {{ $maid->maid_status }}</li>
                  <li class="list-group-item bg-transparent"><strong>Nationality:</strong> {{ $maid->nationality }}</li>
                  <li class="list-group-item bg-transparent"><strong>Salary:</strong> {{ $maid->salary }}</li>
                  <li class="list-group-item bg-transparent"><strong>Age:</strong> {{ $maid->age }}</li>
                  <li class="list-group-item bg-transparent"><strong>Created At:</strong> {{ $maid->created_at }}</li>
                  <li class="list-group-item bg-transparent"><strong>Created By:</strong> {{ $maid->created_by }}</li>
                  <li class="list-group-item bg-transparent"><strong>Last Update:</strong> {{ $maid->updated_at }}</li>

                  @if($votes > 0)
                  <li class="list-group-item bg-transparent">
                <strong>
                    <a href="{{ route('maid.survey.reviews', $maid->id) }}" class="text-primary text-decoration-underline">
                        Customer Ratings ({{ $votes }} {{ $votes === 1 ? 'vote' : 'votes' }})
                    </a>
                </strong>

                    <ul class="list-unstyled mt-2 mb-0 ms-1">
                      <li><strong>Satisfaction:</strong> {!! renderStars($ratingStats->avg_satisfied) !!} <small class="text-muted">({{ round($ratingStats->avg_satisfied,1) }}/5)</small></li>
                      <li><strong>Cleaning:</strong> {!! renderStars($ratingStats->avg_cleaning) !!} <small class="text-muted">({{ round($ratingStats->avg_cleaning,1) }}/5)</small></li>
                      <li><strong>Cooking:</strong> {!! renderStars($ratingStats->avg_cooking) !!} <small class="text-muted">({{ round($ratingStats->avg_cooking,1) }}/5)</small></li>
                      <li><strong>Childcare:</strong> {!! renderStars($ratingStats->avg_childcare) !!} <small class="text-muted">({{ round($ratingStats->avg_childcare,1) }}/5)</small></li>
                      <li><strong>Communication:</strong> {!! renderStars($ratingStats->avg_communication) !!} <small class="text-muted">({{ round($ratingStats->avg_communication,1) }}/5)</small></li>
                    </ul>
                  </li>
                  @endif

                </ul>
              </div>

              <div class="col-md-6">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item bg-transparent"><strong>Agent:</strong> {{ $maid->agency }}</li>
                  <li class="list-group-item bg-transparent"><strong>Booking:</strong> {{ $maid->maid_booked }}</li>
                  @if($latest_customer_p1)
                    <li class="list-group-item bg-transparent"><strong>Latest Package&nbsp;1 Customer:</strong> {{ $latest_customer_p1->customer }}</li>
                  @endif
                  @if($latest_customer_p4)
                    <li class="list-group-item bg-transparent"><strong>Latest Package&nbsp;4 Customer:</strong> {{ $latest_customer_p4->customer }}</li>
                  @endif
                  <li class="list-group-item bg-transparent"><strong>Type:</strong> {{ $maid->maid_type }}</li>
                  <li class="list-group-item bg-transparent"><strong>Payment:</strong> {{ $maid->payment }}</li>
                  <li class="list-group-item bg-transparent">
                    <strong>Date of birth:</strong>
                    {{ $maid->dob ? \Carbon\Carbon::parse($maid->dob)->format('d/m/Y') : '—' }}
                  </li>

                  <li class="list-group-item bg-transparent"><strong>Passport number:</strong> {{ $maid->passport_number }}</li>

                  <li class="list-group-item bg-transparent">
                    <button class="btn btn-outline-primary btn-sm" onclick="copyCurrentUrl()">
                      <i class="fa fa-copy me-1"></i>Copy URL
                    </button>
                  </li>

 

                </ul>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /card-body -->

      {{-- Navigation --}}
      <div class="card-body border-top">
        <ul class="nav nav-pills justify-content-center flex-wrap gap-2">
          @foreach ([
            'doc/maid/' . $name                    => 'Maid Document',
            'maid-report/' . $name                 => 'Package 1',
            'maid-report/p4/' . $name              => 'Package 4',
            'maid-doc-expiry/' . $maid->id         => 'Doc package 4',
            'pro/p4/' . $maid->name                => 'PRO Process',
            'vue/pay-order?search=' . $name        => 'PRO payments',
            'payroll/history/' . $name             => 'Payroll History',
            'page/maid-finance/' . $name           => 'Finance',
            'get-maids-salary-p1-by-name/' . $name => 'P1 Salary',
            'page/maid/invoices/' . $name          => 'Invoices',
            'payroll-note/' . $name                => 'Payroll Deduction',
            'vue/leave-salary?search=' . $name     => 'Leave',
            'vue/ticket-maid?search=' . $name      => 'Ticket',
            'pl/' . $name                          => 'P&L',
          
          ] as $url => $label)
            <li class="nav-item">
              <a href="/{{ $url }}" class="btn btn-light-primary btn-sm {{ request()->is($url) ? 'active' : '' }}">{{ $label }}</a>
            </li>
          @endforeach
        </ul>
      </div>

    </div><!-- /profile card -->

  </div><!-- /container -->
</div><!-- /wrapper -->

<script>


function copyCurrentUrl() {
    const currentUrl = window.location.href; 

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(currentUrl)
            .then(() => {
                alert("URL copied!");
            })
            .catch(err => {
                console.error("Failed to copy: ", err);
                alert("Failed to copy the URL.");
            });
    } else {
      
        const tempInput = document.createElement('input');
        tempInput.value = currentUrl;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            alert("URL copied (fallback method)!");
        } catch (err) {
            console.error("Failed to copy (fallback): ", err);
            alert("Failed to copy the URL.");
        }
        document.body.removeChild(tempInput);
    }
}
</script>

<style>
  .hc-stars i { font-size: 1.05rem; vertical-align: -1px; }
</style>
