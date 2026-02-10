@component('mail::message')
@once
@php
    // Helpers (guarded to avoid redeclare)
    if (!function_exists('mailr_fmt_num')) {
        function mailr_fmt_num($v) {
            $v = (float) $v;
            $abs = number_format(abs($v), 0);
            return $v < 0 ? "({$abs})" : $abs;
        }
    }
    if (!function_exists('mailr_badge')) {
        function mailr_badge($text, $bg = '#0ea5e9') {
            return "<span style=\"display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;color:#fff;background:{$bg};\">{$text}</span>";
        }
    }
@endphp
@endonce

@php
    /** =======================
     *  Setup & Data
     *  ======================= */
    $senderName = config('mail.from.name', config('app.name'));

    /** @var array $payload */
    $p = $payload ?? [];

    $safe = function($key, $default = 0) use ($p) {
        return data_get($p, $key, $default);
    };

    /* ---------- HIGHLIGHTS (new metrics) ---------- */
    $p4AsNewContracts = (int)$safe('p4AsNewContracts'); // P4_* HC contracts in period
    $p5Count          = (int)$safe('p5Count');          // Direct hire contracts in period
    $p4AllActive      = (int)$safe('p4AllActive');      // Active HC contracts now
    $p5AllActive      = (int)$safe('p5AllActive');      // Active Direct hire contracts now
    $totalActive      = $p4AllActive + $p5AllActive;

    /* ---------- PACKAGE SUMMARY ---------- */
    $p1Contracts = (int)$safe('p1Count');
    $p4Contracts = (int)$safe('p4Count');

    $p1Returns   = (int)$safe('maidReturnCat1_count'); // P1 returns
    $p4Returns   = (int)$safe('returnedMaid_count');   // P4 returns

    $p1Net = $p1Contracts - $p1Returns;
    $p4Net = $p4Contracts - $p4Returns;

    $netBadge = function($n) {
        $bg = $n > 0 ? '#16a34a' : ($n < 0 ? '#dc2626' : '#6b7280');
        return mailr_badge(mailr_fmt_num($n), $bg);
    };

    /* ---------- Visa Applications ---------- */
    $visaRows = collect($p['visa_applications'] ?? [])->map(function ($row) {
        return [
            'label' => $row['label'] ?? (string)($row['service'] ?? 'n/a'),
            'total' => (int)($row['total'] ?? 0),
        ];
    });
    $visaTotal = $p['visa_total'] ?? $visaRows->sum('total');

    /* ---------- Closing Balances ---------- */
    $closing  = collect($p['closing_balance'] ?? [])->map(fn($r) => [
        'ledger' => $r['ledger'] ?? '',
        'closing_balance' => (float)($r['closing_balance'] ?? 0),
    ]);

    /* ---------- Leaderboards ---------- */
    $cat1 = collect($p['categoryOne_counts'] ?? [])->sortByDesc('total')->values();
    $cat4 = collect($p['category4Model_counts'] ?? [])->sortByDesc('total')->values();
@endphp

# Monthly Report — {{ $periodLabel }} — {{ $senderName }}

<small style="color:#6b7280;">Generated on {{ now('Asia/Dubai')->format('Y-m-d H:i') }} (Asia/Dubai)</small>

{{-- ===========================
    HIGHLIGHTS (Top 4 metrics + Total Active)
=========================== --}}
@component('mail::panel')
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th align="left"  style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Highlights Package Four and five</th>
      <th align="right" style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="padding:8px 0;">P4 (HC) — <em>New contracts this period</em></td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p4AsNewContracts) }}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;">P5 (Direct Hire) — <em>New contracts this period</em></td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p5Count) }}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;">P4 (HC) — <em>Active contracts now</em></td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p4AllActive) }}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;">P5 (Direct Hire) — <em>Active contracts now</em></td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p5AllActive) }}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;border-top:1px solid #e5e7eb;"><strong>Total Active (P4 + P5)</strong></td>
      <td align="right" style="padding:8px 0;border-top:1px solid #e5e7eb;"><strong>{{ mailr_fmt_num($totalActive) }}</strong></td>
    </tr>
  </tbody>
</table>
@endcomponent

{{-- ===========================
    PACKAGE SUMMARY (Contracts • Returns • Net)
=========================== --}}
@component('mail::panel')
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th align="left"  style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Package</th>
      <th align="right" style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Contracts</th>
      <th align="right" style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Returns</th>
      <th align="right" style="padding:8px 0;border-bottom:2px solid #e5e7eb;">Net</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style="padding:8px 0;">Package One (P1)</td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p1Contracts) }}</td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p1Returns) }}</td>
      <td align="right" style="padding:8px 0;">{!! $netBadge($p1Net) !!}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;">Flexible / Package Four (P4)</td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p4Contracts) }}</td>
      <td align="right" style="padding:8px 0;">{{ mailr_fmt_num($p4Returns) }}</td>
      <td align="right" style="padding:8px 0;">{!! $netBadge($p4Net) !!}</td>
    </tr>
  </tbody>
</table>
@endcomponent

{{-- ===========================
    OPERATIONAL COUNTERS
=========================== --}}
@component('mail::panel')
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
  <tr>
    <td style="padding:6px 0;">Arrivals</td>
    <td style="text-align:right;padding:6px 0;">{{ mailr_fmt_num($safe('arrival_count')) }}</td>
  </tr>
  <tr>
    <td style="padding:6px 0;">Typing Invoices</td>
    <td style="text-align:right;padding:6px 0;">{{ mailr_fmt_num($safe('typing_count')) }}</td>
  </tr>
  <tr>
    <td style="padding:6px 0;">Releases</td>
    <td style="text-align:right;padding:6px 0;">{{ mailr_fmt_num($safe('release_count')) }}</td>
  </tr>
</table>
@endcomponent

{{-- ===========================
    Releases Breakdown
=========================== --}}
@if(!empty($p['relase']))
### {!! mailr_badge('Releases Breakdown', '#6366f1') !!}

@component('mail::table')
| Status | Total |
|:------ | ----: |
@foreach($p['relase'] as $r)
| {{ $r['new_status'] ?? '' }} | {{ mailr_fmt_num($r['total'] ?? 0) }} |
@endforeach
@endcomponent
@endif

{{-- ===========================
    Created-By Leaderboards
=========================== --}}
@if($cat1->count())
### {!! mailr_badge('Package One — Created By', '#0ea5e9') !!}
@component('mail::table')
| Created By | Total |
|:---------- | ----: |
@foreach($cat1 as $row)
| {{ $row['created_by'] ?? '' }} | {{ mailr_fmt_num($row['total'] ?? 0) }} |
@endforeach
@endcomponent
@endif

@if($cat4->count())
### {!! mailr_badge('Package Four (Flexible) — Created By', '#0ea5e9') !!}
@component('mail::table')
| Created By | Total |
|:---------- | ----: |
@foreach($cat4 as $row)
| {{ $row['created_by'] ?? '' }} | {{ mailr_fmt_num($row['total'] ?? 0) }} |
@endforeach
@endcomponent
@endif

{{-- ===========================
    Visa Applications
=========================== --}}
@if($visaRows->count())
### {!! mailr_badge('Visa Applications (by Service)', '#10b981') !!}

@component('mail::table')
| Service | Total |
|:------- | ----: |
@foreach($visaRows as $vr)
| {{ $vr['label'] }} | {{ mailr_fmt_num($vr['total']) }} |
@endforeach
| **All Visa Applications** | **{{ mailr_fmt_num($visaTotal) }}** |
@endcomponent
@endif

{{-- ===========================
    Closing Balances (ONLY)
=========================== --}}
@if($closing->count())
### {!! mailr_badge('Closing Balances', '#14b8a6') !!}

@component('mail::table')
| Ledger | Closing Balance (AED) |
|:------ | ---------------------:|
@foreach($closing as $r)
| {{ $r['ledger'] }} | {{ number_format((float)$r['closing_balance'], 2) }} |
@endforeach
@endcomponent
@endif

Thanks,  
{{ $senderName }}
@endcomponent
