<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contract P4 – Signature</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  {{-- Bootstrap & Signature Pad --}}
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>

  <style>
    body { background-color: #f8f9fa; }
    .page-container { max-width: 960px; margin: 24px auto; }
    .card-shadow { background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,.08); }
    .logo { height: 70px; }
    .muted { color: #6c757d; }
    .signature-canvas { width: 100% !important; height: 260px; min-height: 200px; border: 1px solid #ced4da; border-radius: 5px; background: #fff; }
    @media (max-width: 576px){
      .signature-canvas { height: 350px !important; min-height: 250px; }
      .page-container { margin: 10px; }
    }
    .table td, .table th { vertical-align: middle; }
    .label-sm { font-size: .9rem; font-weight: 600; }
  </style>
</head>
<body>

<div class="page-container">

  {{-- Header / Brand --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
      <img class="logo mr-3" src="{{ env('logo') }}" alt="Logo">
      <div>
        <h4 class="mb-0">Contract Package Four – Signature</h4>
        <small class="muted">Contract ID: <strong>{{ $p4Data->id }}</strong></small>
      </div>
    </div>
    <div class="text-right">
      <small class="muted d-block">Created at</small>
      <strong>{{ optional($conDetails->created_at)->format('Y-m-d') }}</strong>
    </div>
  </div>

  {{-- Details Card (mirrors your Transfer Letter information) --}}
  <div class="card-shadow p-3 mb-4">
    <h5 class="mb-3">Contract Details</h5>

    <div class="row">
      {{-- Left: Narrative like the transfer letter --}}
      <div class="col-md-7">
        <p class="mb-2">
          To whom it may concern,
        </p>
        <p class="mb-2">
          This is to certify that <strong>MR/Ms: {{ $conDetails->customerInfo->name ?? '' }}</strong>,
          a {{ $conDetails->customerInfo->nationality ?? '' }} national with Emirates ID
          <strong>{{ $conDetails->customerInfo->idNumber ?? '' }}</strong>, is taking the
          {{ $conDetails->maidInfo->nationality ?? '' }} housemaid
          <strong>MS. {{ $conDetails->maidInfo->name ?? '' }}</strong> for housemaid service starting from
          <strong>{{ optional($conDetails->created_at)->format('Y-m-d') }}</strong>.
        </p>
        <p class="mb-2">
          I am aware that in the event of terminating my contract before the end of a month, the company shall return/refund the amount with an AED 150 deduction for every day the housemaid has worked with me during that month.


        </p>
        <p class="mb-2">
          I am also aware that if payment is late by 10 days after the due date without any update from me, the company reserves the right to request the worker to return to the office.
        </p>
        <p class="mb-0">
          Monthly payments will be automatically deducted from the provided bank account using credit card/direct debit.
          Additionally, I acknowledge that the maid has recently arrived in the UAE and {{env('company_name')}} is handling the visa process.
        </p>
      </div>

      {{-- Right: Client Details table --}}
      <div class="col-md-5">
        <h6 class="mb-2">Client Details</h6>
        <table class="table table-sm table-bordered mb-0">
          <tbody>
            <tr>
              <td class="w-40"><span class="label-sm">MR/Ms</span></td>
              <td>{{ $conDetails->customerInfo->name ?? '' }}</td>
            </tr>
            <tr>
              <td><span class="label-sm">Emirates ID</span></td>
              <td>{{ $conDetails->customerInfo->idNumber ?? '' }}</td>
            </tr>
            <tr>
              <td><span class="label-sm">Mobile</span></td>
              <td>
                {{ $conDetails->customerInfo->phone ?? '' }}
                @if(!empty($conDetails->customerInfo->secondaryPhone))
                  / {{ $conDetails->customerInfo->secondaryPhone }}
                @endif
              </td>
            </tr>
            <tr>
              <td><span class="label-sm">Maid</span></td>
              <td>{{ $conDetails->maidInfo->name ?? '' }}</td>
            </tr>
            <tr>
              <td><span class="label-sm">Maid Nationality</span></td>
              <td>{{ $conDetails->maidInfo->nationality ?? '' }}</td>
            </tr>
            <tr>
              <td><span class="label-sm">Start Date</span></td>
              <td>{{ optional($conDetails->created_at)->format('Y-m-d') }}</td>
            </tr>
          </tbody>
        </table>

        @if(!empty($conDetails->signature))
          <div class="mt-3">
            <small class="muted d-block mb-1">Existing Signature</small>
            <img src="{{ $conDetails->signature }}" style="max-width: 220px; width: 100%; border:1px solid #eee; border-radius:6px;">
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Signature Pad Card --}}
  <div class="card-shadow p-3">
    <form id="signatureForm" enctype="multipart/form-data" method="POST" action="{{ route('saveSignatureP4') }}">
      @csrf

      <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
          <h5 class="mb-0">Customer Signature</h5>
          <small class="muted">
            Customer: <strong>{{ $p4Data->customerInfo->name ?? $p4Data->customer ?? '—' }}</strong>
            &nbsp;|&nbsp;
            Maid: <strong>{{ $p4Data->maidInfo->name ?? $p4Data->maid ?? '—' }}</strong>
          </small>
        </div>
        <div>
          {{-- Optional: who captured signature / today --}}
          <small class="muted">Date: {{ now()->format('Y-m-d') }}</small>
        </div>
      </div>

      <div class="form-group">
        <canvas id="signature-pad" class="signature-canvas" height="200"></canvas>
      </div>

      {{-- Hidden payload --}}
      <input type="hidden" name="signature" id="signature">
      <input type="hidden" name="id" value="{{ $p4Data->id }}">
      {{-- Helpful context fields (optional on backend) --}}
      <input type="hidden" name="customer_name" value="{{ $conDetails->customerInfo->name ?? '' }}">
      <input type="hidden" name="customer_eid" value="{{ $conDetails->customerInfo->idNumber ?? '' }}">
      <input type="hidden" name="customer_phone" value="{{ $conDetails->customerInfo->phone ?? '' }}">
      <input type="hidden" name="maid_name" value="{{ $conDetails->maidInfo->name ?? '' }}">
      <input type="hidden" name="maid_nationality" value="{{ $conDetails->maidInfo->nationality ?? '' }}">

      <div class="d-flex justify-content-between">
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

  document.getElementById('signatureForm').addEventListener('submit', function (event) {
    if (signaturePad.isEmpty()) {
      event.preventDefault();
      alert("Please provide a signature.");
    } else {
      signatureInput.value = signaturePad.toDataURL('image/png');
    }
  });

  document.getElementById('clear').addEventListener('click', function () {
    signaturePad.clear();
  });

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width  = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
  }
  window.addEventListener("resize", resizeCanvas);
  resizeCanvas();
</script>

</body>
</html>
