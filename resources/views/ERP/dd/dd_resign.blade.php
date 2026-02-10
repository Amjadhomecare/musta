{{-- resources/views/ERP/dd/dd_resign.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Direct-Debit Re-Sign</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .sigPad {
            display: block;
            width: 100%;
            max-width: 700px;
            height: 320px;
            background: transparent;
            border: 1px solid #ced4da;
            border-radius: .5rem;
            cursor: crosshair;
            margin: 0 auto;
        }
        @media (max-width: 576px) {
            .sigPad { height: 400px; max-width: 100vw; }
        }
        /* Dark mode tweaks */
        body.bg-dark .sigPad, [data-bs-theme="dark"] .sigPad { background: transparent !important; }
        body.bg-dark .sigPad, [data-bs-theme="dark"] .sigPad { border-color: rgba(255,255,255,.25); }
        .step-title { display: flex; align-items: center; gap: .5rem; }
        .step-title .badge { font-size: .75rem; }
    </style>
</head>
<body class="container py-4 {{ (request()->cookie('theme') == 'dark') ? 'bg-dark text-light' : '' }}">

    {{-- Logo --}}
    <div class="text-center mb-4">
        <img src="https://homecaremaids.ae/assets/logo/logo-full.svg" 
             alt="HomeCare Maids Logo" 
             style="max-height: 60px; width: auto;">
    </div>

    <h1 class="mb-3">Re-Sign Direct-Debit Mandate</h1>
    <p class="text-muted mb-4">Reference: <strong>{{ $directDebit->ref }}</strong></p>

    {{-- Information Card --}}
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-pen me-2"></i>Update Signatures
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Please provide your updated signatures below to re-authorize the Direct Debit Mandate.
            </p>

            <div class="alert alert-info mb-3">
                <strong>Note:</strong> Only the signatures will be updated. All other mandate details will remain unchanged.
            </div>

            <h6 class="fw-bold mb-2">Current mandate details:</h6>
            <ul class="list-unstyled mb-0">
                <li class="mb-1"><strong>Amount:</strong> AED {{ number_format($directDebit->fixed_amount ?? 0, 2) }}</li>
                <li class="mb-1"><strong>Commences:</strong> {{ \Carbon\Carbon::parse($directDebit->commences_on)->format('d M Y') }}</li>
                @if($directDebit->expires_on && $directDebit->expires_on != '2099-12-31')
                    <li><strong>Expires:</strong> {{ \Carbon\Carbon::parse($directDebit->expires_on)->format('d M Y') }}</li>
                @else
                    <li><strong>Expires:</strong> Until end of service</li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="signForm" method="POST" action="{{ url('/external/resign-dd/' . $directDebit->ref) }}" novalidate>
        @csrf

        {{-- STEP 1: Signature 1 --}}
        <section id="step1" aria-label="Step 1: Signature 1">
            <div class="step-title mb-2">
                <h5 class="mb-0">Signature 1</h5>
                <span class="badge text-bg-primary">Step 1 of 2</span>
            </div>

            <canvas id="sig1" class="sigPad" aria-label="Signature pad 1"></canvas>
            <input type="hidden" name="signature" id="signature1">

            <div class="d-flex justify-content-between align-items-center mt-2">
                <button type="button" id="clear1" class="btn btn-outline-secondary btn-sm">Clear #1</button>
                <small class="text-muted">Use your finger or mouse to sign.</small>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="button" id="nextBtn" class="btn btn-primary">
                    Next
                </button>
            </div>
        </section>

        {{-- STEP 2: Signature 2 --}}
        <section id="step2" aria-label="Step 2: Signature 2" style="display:none;">
            <div class="step-title mb-2">
                <h5 class="mb-0">Signature 2</h5>
                <span class="badge text-bg-primary">Step 2 of 2</span>
            </div>

            <canvas id="sig2" class="sigPad" aria-label="Signature pad 2"></canvas>
            <input type="hidden" name="signature2" id="signature2">

            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="d-flex gap-2">
                    <button type="button" id="backBtn" class="btn btn-outline-secondary btn-sm">Back</button>
                    <button type="button" id="clear2" class="btn btn-outline-secondary btn-sm">Clear #2</button>
                </div>
                <small class="text-muted">Sign the second time to confirm.</small>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    Submit
                </button>
            </div>
        </section>

        <noscript>
            <div class="alert alert-warning mt-4">
                JavaScript is required to capture your signatures. Please enable JavaScript and reload the page.
            </div>
        </noscript>
    </form>

    {{-- Signature Pad --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@5/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const nextBtn = document.getElementById('nextBtn');
            const backBtn = document.getElementById('backBtn');
            const form = document.getElementById('signForm');

            function setupPad(canvasId, hiddenId, clearBtnId) {
                const canvas = document.getElementById(canvasId);
                const hidden = document.getElementById(hiddenId);
                const ctx = canvas.getContext('2d');
                const pad = new SignaturePad(canvas);

                const resize = () => {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = canvas.getBoundingClientRect();
                    const w = Math.max(rect.width, canvas.offsetWidth) * ratio;
                    const h = Math.max(rect.height, canvas.offsetHeight) * ratio;
                    if (w === 0 || h === 0) return;

                    let dataUrl = null;
                    if (!pad.isEmpty()) dataUrl = pad.toDataURL('image/png');

                    canvas.width = w;
                    canvas.height = h;
                    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

                    if (dataUrl) {
                        const img = new Image();
                        img.onload = () => ctx.drawImage(img, 0, 0, canvas.width / ratio, canvas.height / ratio);
                        img.src = dataUrl;
                    } else {
                        pad.clear();
                        hidden.value = '';
                    }
                };

                resize();
                window.addEventListener('resize', resize);

                pad.onEnd = () => { hidden.value = pad.toDataURL('image/png'); };

                document.getElementById(clearBtnId).addEventListener('click', () => {
                    pad.clear();
                    hidden.value = '';
                });

                pad._forceResize = resize;
                pad._canvas = canvas;
                pad._hidden = hidden;

                return { pad, hidden, resize };
            }

            // Initialize pad #1 immediately since it's visible
            let p1 = setupPad('sig1', 'signature1', 'clear1');
            let p2 = null;

            function goToStep2() {
                step1.style.display = 'none';
                step2.style.display = 'block';

                if (!p2) {
                    p2 = setupPad('sig2', 'signature2', 'clear2');
                    requestAnimationFrame(() => p2.pad._forceResize());
                } else {
                    requestAnimationFrame(() => p2.pad._forceResize());
                }
            }

            function goToStep1() {
                step2.style.display = 'none';
                step1.style.display = 'block';
                requestAnimationFrame(() => p1.pad._forceResize());
            }

            nextBtn.addEventListener('click', () => {
                if (p1.pad.isEmpty() && !p1.hidden.value) {
                    alert('Please provide the first signature before proceeding.');
                    return;
                }
                if (!p1.hidden.value) p1.hidden.value = p1.pad.toDataURL('image/png');
                goToStep2();
            });

            backBtn.addEventListener('click', () => {
                goToStep1();
            });

            form.addEventListener('submit', (e) => {
                if (!p2 || p2.pad.isEmpty() && !p2.hidden.value) {
                    e.preventDefault();
                    alert('Please provide the second signature before submitting.');
                    return;
                }
                if (!p2.hidden.value) p2.hidden.value = p2.pad.toDataURL('image/png');
            });
        });
    </script>
</body>
</html>
