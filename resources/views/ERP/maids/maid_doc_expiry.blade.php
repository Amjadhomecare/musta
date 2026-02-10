@extends('keen')
@section('content')

@include('partials.nav_maid')

<!--begin::Content wrapper-->
<div id="kt_app_content" class="app-content flex-column-fluid mt-2">
  <div id="kt_app_content_container" class="app-container">

    {{-- Doc-expiry card --}}
    @php
      $doc = $maid->maidDocExpiry; // may be default with nulls
      $fmt = fn($v, $f='F d, Y') => $v ? \Carbon\Carbon::parse($v)->format($f) : '—';
    @endphp

    <div class="card card-flush shadow-sm mb-8">
      <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800 fs-3">Document Expiry Details</span>
          <span class="text-muted mt-1 fw-semibold fs-7">For: {{ $name }}</span>
        </h3>
      </div>
      <div class="card-body py-5">
        @if($doc)
          @php
            $getStatus = function($date) {
              if (!$date) return 'secondary';
              $days = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($date), false);
              if ($days < 0) return 'danger';
              if ($days <= 30) return 'warning';
              return 'success';
            };
          @endphp
          <div class="row g-5">
            {{-- Passport --}}
            <div class="col-sm-6 col-xl-3">
              <div class="bg-light-{{ $getStatus($doc->passport_expiry) }} rounded border-{{ $getStatus($doc->passport_expiry) }} border border-dashed p-4">
                <div class="d-flex align-items-center">
                  <i class="ki-duotone ki-user-square fs-2x text-{{ $getStatus($doc->passport_expiry) }} me-3">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                  </i>
                  <div>
                    <div class="fs-7 fw-bold text-gray-700">Passport Expiry</div>
                    <div class="fs-6 fw-bold text-{{ $getStatus($doc->passport_expiry) }}">{{ $fmt($doc->passport_expiry) }}</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- EID --}}
            <div class="col-sm-6 col-xl-3">
              <div class="bg-light-{{ $getStatus($doc->eid_expiry) }} rounded border-{{ $getStatus($doc->eid_expiry) }} border border-dashed p-4">
                <div class="d-flex align-items-center">
                  <i class="ki-duotone ki-file-up fs-2x text-{{ $getStatus($doc->eid_expiry) }} me-3">
                    <span class="path1"></span><span class="path2"></span>
                  </i>
                  <div>
                    <div class="fs-7 fw-bold text-gray-700">EID Expiry</div>
                    <div class="fs-6 fw-bold text-{{ $getStatus($doc->eid_expiry) }}">{{ $fmt($doc->eid_expiry) }}</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Visa --}}
            <div class="col-sm-6 col-xl-3">
              <div class="bg-light-{{ $getStatus($doc->visa_expiry) }} rounded border-{{ $getStatus($doc->visa_expiry) }} border border-dashed p-4">
                <div class="d-flex align-items-center">
                  <i class="ki-duotone ki-calendar-tick fs-2x text-{{ $getStatus($doc->visa_expiry) }} me-3">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                  </i>
                  <div>
                    <div class="fs-7 fw-bold text-gray-700">Visa Expiry</div>
                    <div class="fs-6 fw-bold text-{{ $getStatus($doc->visa_expiry) }}">{{ $fmt($doc->visa_expiry) }}</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Labor Card --}}
            <div class="col-sm-6 col-xl-3">
              <div class="bg-light-{{ $getStatus($doc->labor_card_expiry) }} rounded border-{{ $getStatus($doc->labor_card_expiry) }} border border-dashed p-4">
                <div class="d-flex align-items-center">
                  <i class="ki-duotone ki-briefcase fs-2x text-{{ $getStatus($doc->labor_card_expiry) }} me-3">
                    <span class="path1"></span><span class="path2"></span>
                  </i>
                  <div>
                    <div class="fs-7 fw-bold text-gray-700">Labor Card Expiry</div>
                    <div class="fs-6 fw-bold text-{{ $getStatus($doc->labor_card_expiry) }}">{{ $fmt($doc->labor_card_expiry) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @else
          <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
            <i class="ki-duotone ki-information fs-2tx text-warning me-4">
              <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="d-flex flex-stack flex-grow-1">
              <div class="fw-semibold">
                <h4 class="text-gray-900 fw-bold">No documentation found</h4>
                <div class="fs-6 text-gray-700">There is no document expiry information available for this maid at the moment.</div>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- Visa Applications (from ApplyVisa) --}}
    @php
      // Service labels (0,2,3,4,5)
      $serviceLabel = function ($v) {
        return [
          0=>'Visa renewal',
          2=>'New visa',
          3=>'Cancellation',
          4=>'Absconding',
          5=>'Other'
        ][$v] ?? '—';
      };

      // UPDATED statuses (0 → 11)
      $statusMap = [
        0  => 'Created',
        1  => 'Pending',
        2  => 'Missing document',
        3  => 'Contract done',
        4  => 'Labor insurance done',
        5  => 'Work permit done',
        6  => 'Entry permit done',
        7  => 'Change status done',
        8  => 'Medical done',
        9  => 'Eid done',
        10 => 'Visa stamp done',
        11 => 'Rejected',
      ];
      $statusLabel = fn($v) => $statusMap[$v] ?? '—';
      $statusClass = function ($v) {
        if ($v === 11) return 'badge-light-danger';            // Rejected
        if (in_array($v, [9,10], true)) return 'badge-light-success'; // final
        if ($v === 0) return 'badge-light';                     // Created
        return 'badge-light-warning';                           // in-progress / missing
      };

      $mgtLabel = fn($v) => $v == 1 ? 'Approved' : 'Pending';

      /**
       * Robust JSON→array decoder:
       * - if attribute is already an array (cast), use it
       * - else try raw original value (string JSON) and decode
       */
      $jsonArr = function ($model, string $attr) {
        // 1) Use casted value if already array
        $val = $model->{$attr};
        if (is_array($val)) return $val;

        // 2) Fallback to raw original (bypasses casts/select issues)
        if (method_exists($model, 'getRawOriginal')) {
          $raw = $model->getRawOriginal($attr);
          if (is_array($raw)) return $raw;
          if (is_string($raw) && strlen($raw)) {
            $tmp = json_decode($raw, true);
            return is_array($tmp) ? $tmp : [];
          }
        }

        // 3) Try normal decode on the non-raw value if it's a string
        if (is_string($val) && strlen($val)) {
          $tmp = json_decode($val, true);
          return is_array($tmp) ? $tmp : [];
        }

        return [];
      };
    @endphp

    <div class="card card-flush shadow-sm">
      <div class="card-header border-0">
        <h4 class="card-title mb-0">Visa&nbsp;Applications</h4>
      </div>
      <div class="card-body pt-0">
        @if(isset($applyVisas) && $applyVisas->count())
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                  <th>Date</th>
                  <th>Service</th>
                  <th>Status</th>
                  <th>Mgmt</th>
                  <th>Note</th>
                  <th>Documents</th>
                  <th>Comments</th>
                  <th>Created By</th>
                  <th>Updated By</th>
                  <th>Created At</th>
                  <th>Updated At</th>
                </tr>
              </thead>
              <tbody class="fw-semibold text-gray-700">
                @foreach($applyVisas as $v)
                  <tr>
                    <td>{{ $fmt($v->date) }}</td>

                    <td>
                   <a href="{{ url('/vue/apply-visa?search='.$name) }}" class="text-primary text-decoration-underline">
               
              
                      <span class="badge badge-light">{{ $serviceLabel($v->service) }}</span>

                        </a>
                    </td>

                    <td>
                      @php $sl = $statusLabel($v->status); @endphp
                      <span class="badge {{ $statusClass($v->status) }}">
                        {{ $sl }}
                      </span>
                    </td>

                    <td>
                      <span class="badge {{ $v->managment_approval==1 ? 'badge-light-success' : 'badge-light-warning' }}">
                        {{ $mgtLabel($v->managment_approval) }}
                      </span>
                    </td>

                    <td>{{ $v->note ?? '—' }}</td>

                    {{-- Documents --}}
                    <td>
                      @php $docs = $jsonArr($v, 'document'); @endphp
                      @if(count($docs))
                        <div class="d-flex flex-column gap-1">
                          @foreach($docs as $i => $url)
                            <div class="d-flex align-items-center gap-2">
                              <a href="{{ $url }}" target="_blank" class="text-primary text-decoration-underline">
                                {{ basename(parse_url($url, PHP_URL_PATH) ?? '') ?: 'Open' }}
                              </a>
                              <form action="{{ route('applyVisas.deleteDoc', ['id' => $v->id, 'index' => $i]) }}"
                                    method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light-danger btn-icon"
                                        onclick="return confirm('Delete this document?')">
                                  <i class="ki-duotone ki-trash">
                                    <span class="path1"></span><span class="path2"></span>
                                    <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                  </i>
                                </button>
                              </form>
                            </div>
                          @endforeach
                        </div>
                      @else
                        —
                      @endif
                    </td>

                    {{-- Comments --}}
                    <td>
                      @php $comments = $jsonArr($v, 'comments'); @endphp
                      @if(count($comments))
                        <ul class="list-unstyled mb-0 small">
                          @foreach($comments as $c)
                            <li>
                              <strong>{{ $c['by'] ?? '—' }}</strong>:
                              {{ $c['text'] ?? '' }}
                              <em class="text-muted">
                                ({{ isset($c['at']) ? \Carbon\Carbon::parse($c['at'])->format('M d, Y H:i') : '' }})
                              </em>
                            </li>
                          @endforeach
                        </ul>
                      @else
                        —
                      @endif
                    </td>

                    <td>{{ $v->created_by ?? '—' }}</td>
                    <td>{{ $v->updated_by ?? '—' }}</td>
                    <td>{{ $fmt($v->created_at, 'F d, Y H:i') }}</td>
                    <td>{{ $fmt($v->updated_at, 'F d, Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="alert alert-secondary mb-0">No visa applications found for this maid.</div>
        @endif
      </div>
    </div>

  </div>
</div>

@endsection
