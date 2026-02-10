{{-- resources/views/direct-debits/sign.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Direct-Debit Signature</title>
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

    <h1 class="mb-3">Sign Direct-Debit Mandate</h1>
    <p class="text-muted mb-4">Reference: <strong>{{ $directDebit->ref }}</strong></p>

    {{-- Authorization Information --}}
    <div class="card border-primary mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-shield-check me-2"></i>Automated Payment Authorization
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Please complete the information below to authorize Automated Monthly Payments.
            </p>

            <div class="alert alert-warning mb-3">
                <strong>Important:</strong> The account holder's name, Emirates ID, and signature must match the bank account provided.
                The signature will be used exclusively for BDD authorization related to the payments listed below.
            </div>

            <h6 class="fw-bold mb-2">Authorized payments:</h6>
            <ul class="list-unstyled mb-3">
                @if($directDebit->expires_on && $directDebit->expires_on != '2099-12-31')
                    <li class="mb-2">
                        <span class="badge bg-success fs-6">
                            AED {{ number_format($directDebit->fixed_amount ?? 0, 2) }}
                        </span>
                        from <strong>{{ \Carbon\Carbon::parse($directDebit->commences_on)->format('M-Y') }}</strong>
                
                    </li>
                @else
                    <li class="mb-2">
                        <span class="badge bg-success fs-6">
                            AED {{ number_format($directDebit->fixed_amount ?? 0, 2) }}
                        </span>
                        from <strong>{{ \Carbon\Carbon::parse($directDebit->commences_on)->format('M-Y') }}</strong>
                        <strong>until end of service</strong>
                    </li>
                @endif
            </ul>

            <p class="mb-0 text-muted fst-italic">
                <i class="bi bi-info-circle me-1"></i>
                Submission of this form constitutes your consent to the above payments.
            </p>
        </div>
    </div>

    {{-- Validation errors (if form reloaded by server for any reason) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="signForm" method="POST" action="{{ url('/direct-debits/signature') }}" novalidate>
        @csrf
        <input type="hidden" name="ref" value="{{ $directDebit->ref }}">

        {{-- Hidden inputs for bank details (filled by Step 0) --}}
        <input type="hidden" name="paying_bank_id" id="paying_bank_id">
        <input type="hidden" name="paying_bank_name" id="paying_bank_name">
        <input type="hidden" name="account_title" id="account_title_hidden">
        <input type="hidden" name="customer_id_no" id="customer_id_no_hidden">
        <input type="hidden" name="iban" id="iban_hidden">

        {{-- STEP 0: Bank Details --}}
        <section id="step0" aria-label="Step 0: Bank Details">
            <div class="step-title mb-3">
                <h5 class="mb-0">Bank Details</h5>
                <span class="badge text-bg-primary">Step 1 of 3</span>
            </div>

            <div class="mb-3">
                <label for="paying_bank_select" class="form-label">Paying Bank <span class="text-danger">*</span></label>
                <select id="paying_bank_select" class="form-select" required>
                    <option value="">-- Select Bank --</option>
                    <option value="790|ABU DHABI SECURITIES EXCHANGE">ABU DHABI SECURITIES EXCHANGE</option>
                    <option value="118|AMEX (Middle East) - B.S.C">AMEX (Middle East) - B.S.C</option>
                    <option value="083|Aafaq Islamic Finance PSC">Aafaq Islamic Finance PSC</option>
                    <option value="003|Abu Dhabi Commercial Bank">Abu Dhabi Commercial Bank</option>
                    <option value="050|Abu Dhabi Islamic Bank">Abu Dhabi Islamic Bank</option>
                    <option value="057|Ajman Bank">Ajman Bank</option>
                    <option value="004|Al Ahli Bank Of Kuwait K.S.C.">Al Ahli Bank Of Kuwait K.S.C.</option>
                    <option value="120|Al Ain Finance PJSC">Al Ain Finance PJSC</option>
                    <option value="053|Al Hilal Bank">Al Hilal Bank</option>
                    <option value="097|Al Maryah Community Bank">Al Maryah Community Bank</option>
                    <option value="008|Al Masraf">Al Masraf</option>
                    <option value="009|Arab Bank">Arab Bank</option>
                    <option value="048|Arab Emirates Investment Bank">Arab Emirates Investment Bank</option>
                    <option value="018|BNP Paribas">BNP Paribas</option>
                    <option value="011|Bank of Baroda">Bank of Baroda</option>
                    <option value="012|Bank of Sharjah">Bank of Sharjah</option>
                    <option value="021|Citibank NA">Citibank NA</option>
                    <option value="022|Commercial Bank International PSC">Commercial Bank International PSC</option>
                    <option value="023|Commercial Bank of Dubai">Commercial Bank of Dubai</option>
                    <option value="082|DEEM FINANCE">DEEM FINANCE</option>
                    <option value="049|Deutsche Bank">Deutsche Bank</option>
                    <option value="054|Doha Bank">Doha Bank</option>
                    <option value="100|Dubai First PJSC">Dubai First PJSC</option>
                    <option value="024|Dubai Islamic Bank">Dubai Islamic Bank</option>
                    <option value="034|Emirates Islamic Bank PJSC">Emirates Islamic Bank PJSC</option>
                    <option value="026|Emiratesnbd Bank PJSC">Emiratesnbd Bank PJSC</option>
                    <option value="081|Finance House">Finance House</option>
                    <option value="035|First Abu Dhabi Bank">First Abu Dhabi Bank</option>
                    <option value="020|HSBC Middle East">HSBC Middle East</option>
                    <option value="029|Habib Bank AG Zurich">Habib Bank AG Zurich</option>
                    <option value="028|Habib Bank Limited">Habib Bank Limited</option>
                    <option value="030|Investbank PSC">Investbank PSC</option>
                    <option value="033|Mashreqbank PSC">Mashreqbank PSC</option>
                    <option value="036|National Bank Of Bahrain">National Bank Of Bahrain</option>
                    <option value="038|National Bank Of Fujairah">National Bank Of Fujairah</option>
                    <option value="056|National Bank Of Kuwait">National Bank Of Kuwait</option>
                    <option value="042|National Bank Of Umm Al Qaiwain">National Bank Of Umm Al Qaiwain</option>
                    <option value="052|Noor Islamic Bank">Noor Islamic Bank</option>
                    <option value="040|RAK Bank">RAK Bank</option>
                    <option value="041|Sharjah Islamic Bank">Sharjah Islamic Bank</option>
                    <option value="044|Standard Chartered Bank">Standard Chartered Bank</option>
                    <option value="045|Union National Bank">Union National Bank</option>
                    <option value="046|United Arab Bank">United Arab Bank</option>
                    <option value="047|United Bank Ltd.">United Bank Ltd.</option>
                    <option value="086|Wio Bank PJSC">Wio Bank PJSC</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="account_title_input" class="form-label">Account Title <span class="text-danger">*</span></label>
                <input type="text" id="account_title_input" class="form-control" placeholder="Account holder name" required>
            </div>

            <div class="mb-3">
                <label for="customer_id_no_input" class="form-label">Paying ID Number (Emirates ID) <span class="text-danger">*</span></label>
                <input type="text" id="customer_id_no_input" class="form-control" placeholder="784-XXXX-XXXXXXX-X" pattern="^[0-9\-]+$" required>
            </div>

            <div class="mb-3">
                <label for="iban_input" class="form-label">IBAN <span class="text-danger">*</span></label>
                <input type="text" id="iban_input" class="form-control" placeholder="AEXX XXXX XXXX XXXX XXXX XXX" pattern="^[A-Za-z0-9]+$" required>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="button" id="nextBtnStep0" class="btn btn-primary">
                    Next
                </button>
            </div>
        </section>

        {{-- STEP 1 --}}
        <section id="step1" aria-label="Step 1: Signature 1" style="display:none;">
            <div class="step-title mb-2">
                <h5 class="mb-0">Signature 1</h5>
                <span class="badge text-bg-primary">Step 2 of 3</span>
            </div>

            <canvas id="sig1" class="sigPad" aria-label="Signature pad 1"></canvas>
            <input type="hidden" name="signature" id="signature1">

            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="d-flex gap-2">
                    <button type="button" id="backBtnStep1" class="btn btn-outline-secondary btn-sm">Back</button>
                    <button type="button" id="clear1" class="btn btn-outline-secondary btn-sm">Clear #1</button>
                </div>
                <small class="text-muted">Use your finger or mouse to sign.</small>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="button" id="nextBtn" class="btn btn-primary">
                    Next
                </button>
            </div>
        </section>

        {{-- STEP 2 (hidden initially) --}}
        <section id="step2" aria-label="Step 2: Signature 2" style="display:none;">
            <div class="step-title mb-2">
                <h5 class="mb-0">Signature 2</h5>
                <span class="badge text-bg-primary">Step 3 of 3</span>
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
            const step0 = document.getElementById('step0');
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const nextBtnStep0 = document.getElementById('nextBtnStep0');
            const nextBtn = document.getElementById('nextBtn');
            const backBtnStep1 = document.getElementById('backBtnStep1');
            const backBtn = document.getElementById('backBtn');
            const form = document.getElementById('signForm');

            // Bank details inputs
            const payingBankSelect = document.getElementById('paying_bank_select');
            const accountTitleInput = document.getElementById('account_title_input');
            const customerIdNoInput = document.getElementById('customer_id_no_input');
            const ibanInput = document.getElementById('iban_input');

            // Hidden inputs
            const payingBankIdHidden = document.getElementById('paying_bank_id');
            const payingBankNameHidden = document.getElementById('paying_bank_name');
            const accountTitleHidden = document.getElementById('account_title_hidden');
            const customerIdNoHidden = document.getElementById('customer_id_no_hidden');
            const ibanHidden = document.getElementById('iban_hidden');

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
                    // If still 0 (e.g., hidden), skip; we'll resize after showing
                    if (w === 0 || h === 0) return;

                    // Keep existing strokes: read as image and redraw after resize
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
                        // fresh pad
                        pad.clear();
                        hidden.value = '';
                    }
                };

                // Initial size (will be no-op if hidden)
                resize();
                window.addEventListener('resize', resize);

                pad.onEnd = () => { hidden.value = pad.toDataURL('image/png'); };

                document.getElementById(clearBtnId).addEventListener('click', () => {
                    pad.clear();
                    hidden.value = '';
                });

                // public helpers
                pad._forceResize = resize;
                pad._canvas = canvas;
                pad._hidden = hidden;

                return { pad, hidden, resize };
            }

            // Lazy-init pads (will be created when steps become visible)
            let p1 = null;
            let p2 = null;

            // Validate bank details (Step 0)
            function validateBankDetails() {
                if (!payingBankSelect.value) {
                    alert('Please select a paying bank.');
                    payingBankSelect.focus();
                    return false;
                }
                if (!accountTitleInput.value.trim()) {
                    alert('Please enter the account title.');
                    accountTitleInput.focus();
                    return false;
                }
                if (!customerIdNoInput.value.trim()) {
                    alert('Please enter the paying ID number.');
                    customerIdNoInput.focus();
                    return false;
                }
                if (!ibanInput.value.trim()) {
                    alert('Please enter the IBAN.');
                    ibanInput.focus();
                    return false;
                }
                return true;
            }

            // Copy bank details to hidden inputs
            function copyBankDetailsToHidden() {
                const bankValue = payingBankSelect.value;
                if (bankValue) {
                    const [id, name] = bankValue.split('|');
                    payingBankIdHidden.value = id;
                    payingBankNameHidden.value = name;
                }
                accountTitleHidden.value = accountTitleInput.value.trim();
                // Remove spaces and dashes from Emirates ID and IBAN
                customerIdNoHidden.value = customerIdNoInput.value.trim().replace(/[\s\-]/g, '');
                ibanHidden.value = ibanInput.value.trim().replace(/[\s\-]/g, '').toUpperCase();
            }

            // Go to Step 1 from Step 0
            function goToStep1FromStep0() {
                if (!validateBankDetails()) return;
                copyBankDetailsToHidden();

                step0.style.display = 'none';
                step1.style.display = 'block';

                // Initialize pad #1 when step becomes visible
                if (!p1) {
                    p1 = setupPad('sig1', 'signature1', 'clear1');
                    requestAnimationFrame(() => p1.pad._forceResize());
                } else {
                    requestAnimationFrame(() => p1.pad._forceResize());
                }
            }

            // Go back to Step 0 from Step 1
            function goToStep0() {
                step1.style.display = 'none';
                step0.style.display = 'block';
            }

            // Show Step 2 and lazy-init pad #2
            function goToStep2() {
                step1.style.display = 'none';
                step2.style.display = 'block';

                // Create pad #2 once step is visible (so it has real size)
                if (!p2) {
                    p2 = setupPad('sig2', 'signature2', 'clear2');
                    // Force a resize on the next frame to ensure proper dimensions
                    requestAnimationFrame(() => p2.pad._forceResize());
                } else {
                    requestAnimationFrame(() => p2.pad._forceResize());
                }
            }

            // Back to Step 1 (do NOT clear first signature)
            function goToStep1() {
                step2.style.display = 'none';
                step1.style.display = 'block';
                requestAnimationFrame(() => p1.pad._forceResize());
            }

            // NEXT button from Step 0: validate bank details and proceed
            nextBtnStep0.addEventListener('click', () => {
                goToStep1FromStep0();
            });

            // BACK button from Step 1 to Step 0
            backBtnStep1.addEventListener('click', () => {
                goToStep0();
            });

            // NEXT button from Step 1: require signature 1
            nextBtn.addEventListener('click', () => {
                if (p1.pad.isEmpty() && !p1.hidden.value) {
                    alert('Please provide the first signature before proceeding.');
                    return;
                }
                if (!p1.hidden.value) p1.hidden.value = p1.pad.toDataURL('image/png');
                goToStep2();
            });

            // BACK button from step 2
            backBtn.addEventListener('click', () => {
                goToStep1();
            });

            // Submit: require signature 2
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
