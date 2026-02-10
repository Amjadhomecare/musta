{{-- resources/views/ERP/dd/dd_update_bank_details.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Update Bank Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
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

    <h1 class="mb-3">Update Customer Bank Details</h1>
    <p class="text-muted mb-4">Reference: <strong>{{ $directDebit->ref }}</strong></p>

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

    <form id="updateForm" method="POST" action="{{ route('update.dd.bank.submit', ['ref' => $ref]) }}" novalidate>
        @csrf
        
        {{-- Hidden inputs for bank details --}}
        <input type="hidden" name="paying_bank_id" id="paying_bank_id" value="{{ old('paying_bank_id', $directDebit->paying_bank_id) }}">
        <input type="hidden" name="paying_bank_name" id="paying_bank_name" value="{{ old('paying_bank_name', $directDebit->paying_bank_name) }}">

        <div class="card">
            <div class="card-body">
                <section id="step0" aria-label="Bank Details">
                    <div class="step-title mb-3">
                        <h5 class="mb-0">Bank Information</h5>
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
                        <input type="text" id="account_title_input" name="account_title" class="form-control" 
                               value="{{ old('account_title', $directDebit->account_title) }}" placeholder="Account holder name" required>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id_no_input" class="form-label">Paying ID Number (Emirates ID) <span class="text-danger">*</span></label>
                        <input type="text" id="customer_id_no_input" name="customer_id_no" class="form-control" 
                               value="{{ old('customer_id_no', $directDebit->customer_id_no) }}" placeholder="784-XXXX-XXXXXXX-X" pattern="^[0-9\-]+$" required>
                    </div>

                    <div class="mb-3">
                        <label for="iban_input" class="form-label">IBAN <span class="text-danger">*</span></label>
                        <input type="text" id="iban_input" name="iban" class="form-control" 
                               value="{{ old('iban', $directDebit->iban) }}" placeholder="AEXX XXXX XXXX XXXX XXXX XXX" pattern="^[A-Za-z0-9]+$" required>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" id="submitBtn" class="btn btn-success">
                            Update Details
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('updateForm');
            const payingBankSelect = document.getElementById('paying_bank_select');
            const payingBankIdHidden = document.getElementById('paying_bank_id');
            const payingBankNameHidden = document.getElementById('paying_bank_name');
            const accountTitleInput = document.getElementById('account_title_input');
            const customerIdNoInput = document.getElementById('customer_id_no_input');
            const ibanInput = document.getElementById('iban_input');

            // Pre-select bank if value exists
            if (payingBankIdHidden.value && payingBankNameHidden.value) {
                const searchValue = payingBankIdHidden.value + '|' + payingBankNameHidden.value;
                payingBankSelect.value = searchValue;
                // Fallback: try finding by ID only if exact match fails (e.g. if name changed slightly)
                if (!payingBankSelect.value) {
                    for (let i = 0; i < payingBankSelect.options.length; i++) {
                        if (payingBankSelect.options[i].value.startsWith(payingBankIdHidden.value + '|')) {
                            payingBankSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            }

            // Update hidden fields on select change
            payingBankSelect.addEventListener('change', () => {
                const val = payingBankSelect.value;
                if (val) {
                    const [id, name] = val.split('|');
                    payingBankIdHidden.value = id;
                    payingBankNameHidden.value = name;
                } else {
                    payingBankIdHidden.value = '';
                    payingBankNameHidden.value = '';
                }
            });

            form.addEventListener('submit', (e) => {
                if (!payingBankSelect.value) {
                    e.preventDefault();
                    alert('Please select a paying bank.');
                    payingBankSelect.focus();
                    return;
                }
                if (!accountTitleInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter the account title.');
                    accountTitleInput.focus();
                    return;
                }
                if (!customerIdNoInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter the paying ID number.');
                    customerIdNoInput.focus();
                    return;
                }
                if (!ibanInput.value.trim()) {
                    e.preventDefault();
                    alert('Please enter the IBAN.');
                    ibanInput.focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>
