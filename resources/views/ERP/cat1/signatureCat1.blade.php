<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contract Signature – Package One</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
  <style>
    body{background:#f8f9fa}
    .page{max-width:980px;margin:24px auto}
    .cardx{background:#fff;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,.08);padding:20px}
    .logo{height:70px}
    .muted{color:#6c757d}
    .signature-canvas{width:100% !important;height:260px;min-height:200px;border:1px solid #ced4da;border-radius:6px;background:#fff}
    .table td,.table th{vertical-align:middle}
    .label-sm{font-size:.9rem;font-weight:600}
    .rtl{direction:rtl;text-align:right}
    @media(max-width:576px){.page{margin:10px}.signature-canvas{height:340px !important}}
  </style>
</head>
<body>
<div class="page">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
      <img class="logo mr-3" src="{{ env('logo') }}" alt="Logo">
      <div>
        <h4 class="mb-0">Contract Package One – Signature</h4>
        <small class="muted">Contract Ref: <strong>{{ $cat1Data->contract_ref }}</strong></small>
      </div>
    </div>
    <div class="text-right">
      <small class="muted d-block">Created at</small>
      <strong>{{ optional($conDetails->created_at)->format('Y-m-d') }}</strong>
    </div>
  </div>

  {{-- Details (EN + client table) --}}
  <div class="cardx mb-4">
    <div class="row">
      <div class="col-md-7">
        <h5 class="mb-3">Transfer Letter (Summary)</h5>
        <p class="mb-2">
          Subject: Transfer form
        </p>
        <p class="mb-2">
          Based on Circular No. (2) regarding the mechanism of extradition of a worker within the country,
          I, the undersigned <strong>{{ $conDetails->customerInfo->name ?? $conDetails->customer }}</strong>
          with Emirates ID <strong>{{ $conDetails->customerInfo->idNumber ?? '' }}</strong>,
          have no objection to transferring the worker’s file
          <strong>{{ $conDetails->maidInfo->name ?? $cat1Data->maid }}</strong>
          (Nationality: <strong>{{ $conDetails->maidInfo->nationality ?? $cat1Data->nationality }}</strong>)
          to my profile. I will process the entry permit at Home Care Center.
        </p>
        <p class="mb-2">
          From the date: <strong>{{ $conDetails->started_date }}</strong> within the specified trial period until
          <strong>{{ (new DateTime($conDetails->started_date))->add(new DateInterval('P6D'))->format('Y-m-d') }}</strong>.
          I acknowledge late/penalty rules and that monthly payments may be auto-deducted by card/direct debit.
        </p>
        <p class="mb-0">
          The sponsor must complete residency procedures within no more than 55 days from receiving the worker,
          otherwise the provided guarantee is forfeited.
        </p>
      </div>
      <div class="col-md-5">
        <h6 class="mb-2">Client & Contract Details</h6>
        <table class="table table-sm table-bordered mb-2">
          <tbody>
            <tr><td class="w-40"><span class="label-sm">Customer</span></td><td>{{ $cat1Data->customer }}</td></tr>
            <tr><td><span class="label-sm">Emirates ID</span></td><td>{{ $conDetails->customerInfo->idNumber ?? '' }}</td></tr>
            <tr><td><span class="label-sm">Mobile</span></td>
                <td>
                  {{ $conDetails->customerInfo->phone ?? '' }}
                  @if(!empty($conDetails->customerInfo->secondaryPhone)) / {{ $conDetails->customerInfo->secondaryPhone }} @endif
                </td></tr>
            <tr><td><span class="label-sm">Maid</span></td><td>{{ $conDetails->maidInfo->name ?? $cat1Data->maid }}</td></tr>
            <tr><td><span class="label-sm">Nationality</span></td><td>{{ $conDetails->maidInfo->nationality ?? $cat1Data->nationality }}</td></tr>
            <tr><td><span class="label-sm">Start / End</span></td>
                <td>{{ $cat1Data->started_date }} → {{ $cat1Data->ended_date }}</td></tr>
            <tr><td><span class="label-sm">Amount (AED)</span></td><td>{{ number_format((float)$cat1Data->amount, 2) }}</td></tr>
            <tr><td><span class="label-sm">Status</span></td>
                <td>{{ $cat1Data->contract_status == 1 ? 'Active' : 'Inactive' }}</td></tr>
            <tr><td><span class="label-sm">Invoice Ref</span></td><td>{{ $cat1Data->invoice_ref }}</td></tr>
          </tbody>
        </table>

        @if(!empty($conDetails->signature))
          <small class="muted d-block mb-1">Existing Signature</small>
          <img src="{{ $conDetails->signature }}" style="max-width: 220px; width: 100%; border:1px solid #eee; border-radius:6px;">
        @endif

        <div class="mt-3">
          <em class="muted">Created by: {{ $cat1Data->created_by }} on {{ \Carbon\Carbon::parse($cat1Data->created_at)->format('Y-m-d') }} — NextMeta ERP</em>
        </div>
      </div>
    </div>
  </div>

  {{-- Arabic note (compact) --}}
  <div class="cardx mb-4 rtl">
    <h6 class="mb-2">الموضوع: نموذج تعهد</h6>
    <p class="mb-2">
      أتعهد أنا <strong>{{ $conDetails->customerInfo->name ?? $conDetails->customer }}</strong>
      رقم الهوية <strong>{{ $conDetails->customerInfo->idNumber ?? '' }}</strong>
      بعدم الممانعة من نقل ملف العاملة <strong>{{ $conDetails->maidInfo->name ?? $cat1Data->maid }}</strong>
      (الجنسية: <strong>{{ $conDetails->maidInfo->nationality ?? $cat1Data->nationality }}</strong>)
      إلى ملفي الشخصي، مع إنهاء إجراءات إذن الدخول داخل مركزنا، وذلك من تاريخ
      <strong>{{ $conDetails->started_date }}</strong> وخلال فترة التجربة حتى
      <strong>{{ (new DateTime($conDetails->started_date))->add(new DateInterval('P6D'))->format('Y-m-d') }}</strong>.
      يجب استكمال إجراءات الإقامة خلال 55 يومًا وإلا يسقط الضمان.
    </p>
  </div>

  {{-- Signature Pad --}}
  <div class="cardx">
    <form id="signatureForm" method="POST" action="{{ route('saveSignatureCat1') }}" enctype="multipart/form-data">
      @csrf
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <h5 class="mb-0">Customer Signature</h5>
          <small class="muted">
            Customer: <strong>{{ $cat1Data->customer }}</strong>
            &nbsp;|&nbsp; Maid: <strong>{{ $conDetails->maidInfo->name ?? $cat1Data->maid }}</strong>
          </small>
        </div>
        <small class="muted">Date: {{ now()->format('Y-m-d') }}</small>
      </div>

      <canvas id="signature-pad" class="signature-canvas" height="200"></canvas>

      {{-- Hidden payload --}}
      <input type="hidden" name="signature" id="signature">
      <input type="hidden" name="id" value="{{ $cat1Data->id }}">
      {{-- Optional context for logs --}}
      <input type="hidden" name="customer_name" value="{{ $cat1Data->customer }}">
      <input type="hidden" name="maid_name" value="{{ $conDetails->maidInfo->name ?? $cat1Data->maid }}">
      <input type="hidden" name="maid_nationality" value="{{ $conDetails->maidInfo->nationality ?? $cat1Data->nationality }}">

      <div class="d-flex justify-content-between mt-3">
        <button type="button" id="clear" class="btn btn-secondary">Clear</button>
        <button type="submit" id="save" class="btn btn-primary">Save Signature</button>
      </div>
    </form>
  </div>

</div>

<script>
  const canvas = document.getElementById('signature-pad');
  const signaturePad = new SignaturePad(canvas);
  const signatureInput = document.getElementById('signature');

  document.getElementById('signatureForm').addEventListener('submit', function (e) {
    if (signaturePad.isEmpty()) {
      e.preventDefault();
      alert('Please provide a signature.');
    } else {
      signatureInput.value = signaturePad.toDataURL('image/png');
    }
  });

  document.getElementById('clear').addEventListener('click', () => signaturePad.clear());

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    signaturePad.clear();
  }
  window.addEventListener('resize', resizeCanvas);
  resizeCanvas();
</script>
</body>
</html>
