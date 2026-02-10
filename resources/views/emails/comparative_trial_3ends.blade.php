@php
  function amt($v){ $v=(float)$v; $a=number_format(abs($v),2); return $v<0? "({$a})" : $a; }
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    .wrap { max-width: 980px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color:#111; }
    .header { background:#e98a52; color:#fff; text-transform:uppercase; padding:16px 18px; }
    .title { font-weight:800; letter-spacing:.5px; font-size:16px; }
    .sub { font-size:13px; opacity:.95; text-transform:none; margin-top:2px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px 10px; }
    thead th { text-align:right; color:#555; font-weight:700; border-bottom:2px solid #ddd; font-size:13px; }
    thead th:first-child { text-align:left; }
    .lbl { text-align:left; }
    .num { text-align:right; min-width:120px; font-variant-numeric: tabular-nums; }
    .section { font-weight:800; color:#333; padding-top:14px; }
    .indent { padding-left:20px; }
    .subtotal { font-weight:700; border-top:1px solid #bbb; }
    .total { font-weight:800; border-top:2px solid #666; }
    .note { color:#666; font-size:12px; margin-top:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <div class="title">Comparative Financial Report — 3 MONTH ENDS</div>
      <div class="sub">As of {{ $meta['asof'] }}</div>
    </div>

    <div style="padding:16px 18px;">
      <table>
        <thead>
          <tr>
            <th class="lbl"></th>
            <th class="num">{{ $meta['col1']['label'] }}<br><span style="color:#888;font-size:12px;">({{ $meta['col1']['date'] }})</span></th>
            <th class="num">{{ $meta['col2']['label'] }}<br><span style="color:#888;font-size:12px;">({{ $meta['col2']['date'] }})</span></th>
            <th class="num">{{ $meta['col3']['label'] }}<br><span style="color:#888;font-size:12px;">({{ $meta['col3']['date'] }})</span></th>
            <th class="num">{{ $meta['col4']['label'] }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($classes as $className => $c)
            <tr><td class="section" colspan="5">{{ $className }}</td></tr>
            @foreach($c['rows'] as $r)
              <tr>
                <td class="lbl indent">{{ $r['ledger_group'] }}</td>
                <td class="num">{{ amt($r['bal_m1']) }}</td>
                <td class="num">{{ amt($r['bal_m2']) }}</td>
                <td class="num">{{ amt($r['bal_m3']) }}</td>
                <td class="num">{{ amt($r['change_amount']) }}</td>
              </tr>
            @endforeach
            <tr>
              <td class="lbl subtotal">Total {{ $className }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_m1']) }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_m2']) }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_m3']) }}</td>
              <td class="num subtotal">{{ amt($c['subtotal_change']) }}</td>
            </tr>
          @endforeach
          <tr>
            <td class="lbl total">Grand Total</td>
            <td class="num total">{{ amt($totals['m1']) }}</td>
            <td class="num total">{{ amt($totals['m2']) }}</td>
            <td class="num total">{{ amt($totals['m3']) }}</td>
            <td class="num total">{{ amt($totals['change']) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="note">
        All amounts in AED • Debits(+) Credits(−). Change = {{ $meta['col3']['label'] }} − {{ $meta['col1']['label'] }}.
      </div>
    </div>
  </div>
</body>
</html>
