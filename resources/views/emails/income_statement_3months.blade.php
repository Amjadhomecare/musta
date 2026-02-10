@php
  // Pull company name from .env MAIL_FROM_NAME
  $senderName = config('mail.from.name', 'Your Company');

  // Format numeric values (accounting style)
  function fmt($v){
      $v = (float)$v;
      $abs = number_format(abs($v), 2);
      return $v < 0 ? "({$abs})" : $abs;
  }

  // Arrow + color styling
  function changeDisplay($v){
      $v = (float)$v;
      $formatted = fmt($v);
      if ($v > 0) return "<span style='color:#16a34a;font-weight:600;'>⬆ {$formatted}</span>";
      if ($v < 0) return "<span style='color:#dc2626;font-weight:600;'>⬇ {$formatted}</span>";
      return "<span style='color:#6b7280;'>{$formatted}</span>";
  }

  $m1Lbl = $meta['col1']['label'];
  $m2Lbl = $meta['col2']['label'];
  $m3Lbl = $meta['col3']['label'];
@endphp

<div style="max-width:980px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto;background:#ffffff;">

  {{-- HEADER --}}
  <div style="background:#0d9488;color:#fff;padding:16px 20px;">
    <div style="font-weight:800;letter-spacing:.3px;text-transform:uppercase;">
      {{ $meta['heading'] }}
    </div>
    <div style="font-weight:500;text-transform:none;opacity:.95;margin-top:4px;">
      As of {{ $meta['asof'] }} — <span style="color:#fff;font-weight:600;">{{ $senderName }}</span>
    </div>
  </div>

  {{-- ================= REVENUE ================= --}}
  <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;background:#fafafa;font-weight:700;">Revenue</div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Group</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m1Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Change ({{ $m3Lbl }} − {{ $m1Lbl }})</th>
      </tr>
    </thead>
    <tbody>
      @foreach(($incomeClasses['Revenue']['rows'] ?? []) as $r)
        <tr>
          <td style="padding:6px 12px;">{{ $r['ledger_group'] }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m1']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m2']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m3']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($r['change_amount']) !!}</td>
        </tr>
      @endforeach
      <tr style="font-weight:700;background:#fcfcfc;">
        <td style="padding:8px 12px;">Subtotal Revenue</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Revenue']['subtotal_m1'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Revenue']['subtotal_m2'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Revenue']['subtotal_m3'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($incomeClasses['Revenue']['subtotal_change'] ?? 0) !!}</td>
      </tr>
    </tbody>
  </table>

  {{-- ================= EXPENSES ================= --}}
  <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;background:#fafafa;font-weight:700;margin-top:10px;">Expenses</div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Group</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m1Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Change ({{ $m3Lbl }} − {{ $m1Lbl }})</th>
      </tr>
    </thead>
    <tbody>
      @foreach(($incomeClasses['Expenses']['rows'] ?? []) as $r)
        <tr>
          <td style="padding:6px 12px;">{{ $r['ledger_group'] }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m1']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m2']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{{ fmt($r['m3']) }}</td>
          <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($r['change_amount']) !!}</td>
        </tr>
      @endforeach
      <tr style="font-weight:700;background:#fcfcfc;">
        <td style="padding:8px 12px;">Subtotal Expenses</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Expenses']['subtotal_m1'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Expenses']['subtotal_m2'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeClasses['Expenses']['subtotal_m3'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($incomeClasses['Expenses']['subtotal_change'] ?? 0) !!}</td>
      </tr>
    </tbody>
  </table>

  {{-- ================= NET INCOME ================= --}}
  <div style="padding:12px 18px;border-top:1px solid #f3f4f6;background:#f6ffed;margin-top:10px;font-weight:800;">Net Income</div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr>
        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">&nbsp;</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m1Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }}</th>
        <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Change ({{ $m3Lbl }} − {{ $m1Lbl }})</th>
      </tr>
    </thead>
    <tbody>
      <tr style="font-weight:800;">
        <td style="padding:8px 12px;">Revenue − Expenses</td>
        <td style="text-align:right;padding:8px 12px;">{{ fmt($incomeTotals['net']['m1'] ?? 0) }}</td>
        <td style="text-align:right;padding:12px 12px;color:{{ ($incomeTotals['net']['m2'] ?? 0) >=0 ? '#16a34a':'#dc2626'}};">{{ fmt($incomeTotals['net']['m2'] ?? 0) }}</td>
        <td style="text-align:right;padding:12px 12px;color:{{ ($incomeTotals['net']['m3'] ?? 0) >=0 ? '#16a34a':'#dc2626'}};">{{ fmt($incomeTotals['net']['m3'] ?? 0) }}</td>
        <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($incomeTotals['net']['change'] ?? 0) !!}</td>
      </tr>
    </tbody>
  </table>

  {{-- ================= SUMMARY OF CHANGES (WITH GROUP DETAILS) ================= --}}
  @php
    $delta = fn($a, $b) => (float)$a - (float)$b;
  @endphp

  <div style="max-width:980px;margin:25px auto;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
    <div style="background:#1f2937;color:#fff;padding:14px 18px;font-weight:800;letter-spacing:.3px;text-transform:uppercase;">
      Summary of Changes — Month-to-Month (By Group)
      <div style="font-weight:500;text-transform:none;opacity:.85;">
        ({{ $m2Lbl }} − {{ $m1Lbl }}), ({{ $m3Lbl }} − {{ $m2Lbl }}), ({{ $m3Lbl }} − {{ $m1Lbl }})
      </div>
    </div>

    {{-- REVENUE CHANGES BY GROUP --}}
    <div style="padding:10px 18px;background:#fafafa;font-weight:700;">Revenue Changes</div>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Group</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }} − {{ $m1Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m2Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m1Lbl }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach(($incomeClasses['Revenue']['rows'] ?? []) as $r)
          @php
            $d21 = $delta($r['m2'], $r['m1']);
            $d32 = $delta($r['m3'], $r['m2']);
            $d31 = $delta($r['m3'], $r['m1']);
          @endphp
          <tr>
            <td style="padding:6px 12px;">{{ $r['ledger_group'] }}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d21) !!}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d32) !!}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d31) !!}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- EXPENSES CHANGES BY GROUP --}}
    <div style="padding:10px 18px;background:#fafafa;font-weight:700;margin-top:10px;">Expenses Changes</div>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">Group</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }} − {{ $m1Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m2Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m1Lbl }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach(($incomeClasses['Expenses']['rows'] ?? []) as $r)
          @php
            $d21 = $delta($r['m2'], $r['m1']);
            $d32 = $delta($r['m3'], $r['m2']);
            $d31 = $delta($r['m3'], $r['m1']);
          @endphp
          <tr>
            <td style="padding:6px 12px;">{{ $r['ledger_group'] }}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d21) !!}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d32) !!}</td>
            <td style="text-align:right;padding:6px 12px;">{!! changeDisplay($d31) !!}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- NET INCOME CHANGES (TOTAL SUMMARY) --}}
    @php
      $net = $incomeTotals['net'];
      $net21 = $delta($net['m2'], $net['m1']);
      $net32 = $delta($net['m3'], $net['m2']);
      $net31 = $delta($net['m3'], $net['m1']);
    @endphp
    <div style="padding:10px 18px;background:#f6ffed;font-weight:800;margin-top:10px;">Net Income Changes</div>
    <table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
      <thead>
        <tr style="background:#f9fafb;">
          <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #e5e7eb;">&nbsp;</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m2Lbl }} − {{ $m1Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m2Lbl }}</th>
          <th style="text-align:right;padding:8px 12px;border-bottom:2px solid #e5e7eb;">{{ $m3Lbl }} − {{ $m1Lbl }}</th>
        </tr>
      </thead>
      <tbody>
        <tr style="font-weight:800;">
          <td style="padding:8px 12px;">Total Δ Net Income</td>
          <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($net21) !!}</td>
          <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($net32) !!}</td>
          <td style="text-align:right;padding:8px 12px;">{!! changeDisplay($net31) !!}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
