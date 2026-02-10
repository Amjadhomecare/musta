<!DOCTYPE html>
<html lang="en">
<head>
    @php
        use App\Models\General_journal_voucher;
        use App\Models\Category4Model;
        use Illuminate\Support\Str;

        // Closing balance (as you had)
        $closingBalance = General_journal_voucher::calculateCustomerClosingBalance($conDetails[0]->customerInfo?->name);

        // Identify customer id robustly
        $customerId = $conDetails[0]->customerInfo->id ?? $conDetails[0]->customer_id ?? null;

        // Map of invoices (vouchers) grouped by Contract_ref coming from $account
        $byContractFromAccount = collect($account)->groupBy(function ($e) {
            return optional($e?->p4Relation)->Contract_ref ?? 'N/A';
        });

        // All P4 contracts for this customer, sorted by ID ASC
        $allP4ForCustomer = Category4Model::query()
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->orderBy('id', 'asc')
            ->get();

        // Orphan contracts that appear in vouchers but not in Category4 (edge case)
        $p4Refs = $allP4ForCustomer->pluck('Contract_ref')->filter();
        $orphanRefs = $byContractFromAccount->keys()->diff($p4Refs);

        // Represent orphans as lightweight objects to keep the loop simple (pushed after real ones)
        $orphanContracts = $orphanRefs->map(function ($ref) {
            return (object)[
                'id'           => PHP_INT_MAX,   // ensures they appear after real contracts
                'Contract_ref' => $ref,
                'date'         => null,
                'extra'        => null,
                'note'         => null,
            ];
        });

        // Final ordered list: real contracts first (ASC by id), then orphans
        $allContractsOrdered = $allP4ForCustomer->concat($orphanContracts);

        // Helper map in case you need fast lookup by ref
        $contractsMap = $allP4ForCustomer->keyBy('Contract_ref');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract View</title>

    {{-- Tailwind via CDN (use Vite in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page { size: A4; margin: 20mm; }
            .no-print { display: none !important; }
            html, body { background: #fff !important; }
            .shadow, .shadow-sm, .ring-1 { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <div class="mx-auto my-6 max-w-5xl rounded-xl bg-white shadow-sm ring-1 ring-slate-200 print:shadow-none print:ring-0">

        {{-- ===== Header ===== --}}
        <header class="border-b border-slate-200 p-6 text-center">
            <img src="{{ env('contract_header') }}" alt="Company Header" class="mx-auto h-36 w-auto object-contain" />
        </header>

        {{-- ===== Top Controls + Summary ===== --}}
        <section class="p-6">
            <div class="mb-5 flex items-center justify-between">
                <button onclick="window.print()"
                    class="no-print inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Print (Package 4)
                </button>

                <h2 class="rounded-lg bg-slate-50 px-3 py-1 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">
                    Contract Details
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Left column --}}
                <div class="space-y-2 text-sm">
                    <p><span class="font-semibold text-slate-600">Date:</span>
                        <span class="ml-1">{{ $conDetails[0]->created_at }}</span>
                    </p>

                    <p><span class="font-semibold text-slate-600">Contract No:</span>
                        <a target="_blank" href="/edit-upcoming-installment/{{ $conDetails[0]->Contract_ref }}" class="ml-1 font-medium text-indigo-600 hover:underline">
                            {{ $conDetails[0]->Contract_ref }}
                        </a>
                    </p>

                    <p><span class="font-semibold text-slate-600">Customer:</span>
                        @if(!empty($conDetails[0]->customerInfo?->name))
                            <a href="{{ url('/page/invoices/' . rawurlencode($conDetails[0]->customerInfo?->name)) }}" target="_blank" class="ml-1 font-medium text-indigo-600 hover:underline">
                                {{ $conDetails[0]->customerInfo?->name ?? 'N/A' }}
                            </a>
                        @else
                            <span class="ml-1">N/A</span>
                        @endif
                    </p>

                    <p><span class="font-semibold text-slate-600">Mobile:</span>
                        <span class="ml-1">{{ $conDetails[0]->customerInfo?->phone }}</span>
                    </p>

                    <p class="mt-2">
                        <span class="rounded-lg bg-red-50 px-2 py-1 text-red-700 ring-1 ring-inset ring-red-200">
                            <span class="font-semibold">CB:</span> {{ $closingBalance }}
                        </span>
                    </p>
                </div>

                {{-- Right column --}}
                <div class="space-y-2 text-sm">
                    <p><span class="font-semibold text-slate-600">Name:</span>
                        <span class="ml-1">{{ $conDetails[0]->maidInfo->name }}</span>
                    </p>
                    <p><span class="font-semibold text-slate-600">Start Date:</span>
                        <span class="ml-1">{{ $conDetails[0]->created_at }}</span>
                    </p>
                </div>
            </div>

            {{-- ===== (Top) Return Details Table (from $conDetails[0]['returnInfo']) ===== --}}
            <div class="mt-6">
                @if(isset($conDetails[0]['returnInfo']) && !is_null($conDetails[0]['returnInfo']?->maidInfo?->name))
                    <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                        <div class="bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                            Return Details
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Returned Date</th>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Package Type</th>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Maid Return Name</th>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Contract</th>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Reason</th>
                                        <th class="px-4 py-2 font-semibold text-slate-600">Returned By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']->created_at ?? 'no return' }}</td>
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']->packagetype ?? 'no return' }}</td>
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']?->maidInfo?->name ?? 'no return' }}</td>
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']->contract ?? 'no return' }}</td>
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']->reason ?? 'no return' }}</td>
                                        <td class="px-4 py-2">{{ $conDetails[0]['returnInfo']->created_by ?? 'no return' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-sm font-semibold text-emerald-600">Active</p>
                @endif
            </div>
        </section>

        {{-- ===== Installments Table ===== --}}
        <section class="p-6">
            <div class="mb-3 text-sm font-semibold text-slate-700">Installments For {{ $conDetails[0]->maidInfo->name }}  </div>
            <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs md:text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 font-semibold text-slate-600">Accrued Date</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Invoice Status</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Amount</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Note</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Cheque</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Generated Invoice</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Generated Date</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Generated By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($conDetails as $key => $value)
                                @foreach ($value['installmentInfo'] as $installment)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2">{{ $installment->accrued_date }}</td>
                                        <td class="px-4 py-2">
                                            @if ($installment->invoice_status === 0)
                                                <span class="rounded-md bg-amber-50 px-2 py-0.5 text-amber-700 ring-1 ring-inset ring-amber-200">Pending</span>
                                            @else
                                                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-emerald-700 ring-1 ring-inset ring-emerald-200">Generated</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $installment->amount }}</td>
                                        <td class="px-4 py-2">{{ $installment->note }}</td>
                                        <td class="px-4 py-2">{{ $installment->cheque }}</td>
                                        <td class="px-4 py-2">{{ $installment->invoice }}</td>
                                        <td class="px-4 py-2">
                                            @if (!empty($installment?->invoiceRef?->creditNoteRef !== "No Data"))
                                                <a href="/view/jv/selected/{{ $installment?->invoiceRef?->creditNoteRef }}"
                                                   class="rounded-md bg-rose-50 px-2 py-0.5 text-rose-700 ring-1 ring-inset ring-rose-200 hover:underline">
                                                    {{ $installment?->invoiceRef?->creditNoteTargets[0]->amount ?? "-" }}
                                                </a>
                                            @else
                                                <span class="text-lg">✅</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">{{ $installment->updated_at }}</td>
                                        <td class="px-4 py-2">{{ $installment->updated_by }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- ===== All Details For Flexible Package (GROUPED by Contract; includes ALL contracts; ASC by id) ===== --}}
        <section class="p-6">
            <div class="mb-3 text-sm font-semibold text-slate-700">Full Details</div>

            <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs md:text-sm align-top">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 font-semibold text-slate-600">#</th>
                                {{-- Contract Info and Return Info side-by-side, shown once per contract --}}
                                <th class="px-4 py-2 font-semibold text-slate-600">Contract Information</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Return Info</th>

                                <th class="px-4 py-2 font-semibold text-slate-600">Date</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Maid</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Reference Code</th>
                                       <th class="px-4 py-2 font-semibold text-slate-600">Amount</th>
                             
                                    <th class="px-4 py-2 font-semibold text-slate-600">Credit Note Reference</th>
                                
                    
                                <th class="px-4 py-2 font-semibold text-slate-600">Notes</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Received Reference</th>
                            
                                <th class="px-4 py-2 font-semibold text-slate-600">Created By</th>
                                <th class="px-4 py-2 font-semibold text-slate-600">Created At</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @php $rowIndex = 0; @endphp

                            @foreach($allContractsOrdered as $p4)
                                @php
                                    $contractRef = $p4->Contract_ref ?? 'N/A';
                                    $rows = $byContractFromAccount->get($contractRef, collect());
                                    $rowCount = max($rows->count(), 1); // at least one row to show placeholder when no invoices
                                    // Prefer return from Category4 model; fallback to first row’s relation if needed
                                    $ret = $p4->returnInfo ?? optional(optional($rows->first())->p4Relation)->returnInfo;
                                @endphp

                                @if($rows->isEmpty())
                                    {{-- Render one placeholder row (no invoices for this contract) --}}
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2 align-top">{{ ++$rowIndex }}</td>

                                        {{-- Contract Info --}}
                                        <td class="px-4 py-2 align-top" rowspan="1">
                                            @if(!empty($contractRef) && $contractRef !== 'N/A')
                                                <div class="font-semibold">
                                                    <a href="{{ url('/edit-upcoming-installment/' . $contractRef) }}"
                                                       target="_blank"
                                                       class="text-indigo-600 hover:underline">
                                                        {{ $contractRef }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-slate-500">N/A</span>
                                            @endif

                                            <div class="mt-0.5 text-[11px] text-slate-500">
                                                {{ $p4->date instanceof \Illuminate\Support\Carbon
                                                    ? $p4->date->format('Y-m-d')
                                                    : Str::of($p4->date)->limit(10) }}
                                            </div>

                                            @if(!empty($p4->extra))
                                                <div class="mt-1 rounded-md bg-slate-50 p-2 text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200">
                                                    {{ is_string($p4->extra) ? Str::limit($p4->extra, 160) : json_encode($p4->extra) }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Return Info (includes contract NOTE) --}}
                                        <td class="px-4 py-2 align-top" rowspan="1">
                                            {{-- Contract Note (always show if exists) --}}
                                            @if(!empty($p4->note))
                                                <div class="mb-1 rounded-md bg-sky-50 p-2 text-[11px] text-sky-700 ring-1 ring-inset ring-sky-200">
                                                    <span class="font-semibold">Note:</span>
                                                    <span>{{ Str::limit($p4->note, 200) }}</span>
                                                </div>
                                            @endif

                                            {{-- Return status --}}
                                            @if($ret)
                                                <div class="mb-1 inline-flex items-center gap-2">
                                                    <span class="rounded-md bg-rose-50 px-2 py-0.5 text-rose-700 ring-1 ring-inset ring-rose-200">Returned</span>
                                                    <span class="text-[11px] text-slate-500">
                                                        {{ $ret->returned_date instanceof \Illuminate\Support\Carbon
                                                            ? $ret->returned_date->format('Y-m-d')
                                                            : Str::of($ret->returned_date)->limit(10) }}
                                                    </span>
                                                </div>
                                                @if(!empty($ret->extra))
                                                    <div class="rounded-md bg-slate-50 p-2 text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200">
                                                        {{ is_string($ret->extra) ? Str::limit($ret->extra, 160) : json_encode($ret->extra) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-emerald-600">Active</span>
                                            @endif
                                        </td>

                                        {{-- Placeholders for invoice-related columns --}}
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                        <td class="px-4 py-2 align-top">—</td>
                                    </tr>
                                @else
                                    {{-- Render each invoice row, with Contract/Return shown once via rowspan --}}
                                    @foreach($rows as $i => $entry)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-2 align-top">{{ ++$rowIndex }}</td>

                                            @if($i === 0)
                                                {{-- Contract Info (rowspan) --}}
                                                <td class="px-4 py-2 align-top" rowspan="{{ $rowCount }}">
                                                    <div class="font-semibold">
                                                        <a href="{{ url('/edit-upcoming-installment/' . $contractRef) }}"
                                                           target="_blank"
                                                           class="text-indigo-600 hover:underline">
                                                            {{ $contractRef }}
                                                        </a>
                                                    </div>
                                                    <div class="mt-0.5 text-[11px] text-slate-500">
                                                        {{ $p4->date instanceof \Illuminate\Support\Carbon
                                                            ? $p4->date->format('Y-m-d')
                                                            : Str::of($p4->date)->limit(10) }}
                                                    </div>
                                                    @if(!empty($p4->extra))
                                                        <div class="mt-1 rounded-md bg-slate-50 p-2 text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200">
                                                            {{ is_string($p4->extra) ? Str::limit($p4->extra, 160) : json_encode($p4->extra) }}
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Return Info (rowspan) + Contract Note --}}
                                                <td class="px-4 py-2 align-top" rowspan="{{ $rowCount }}">
                                                    {{-- Contract Note (always show if exists) --}}
                                                    @if(!empty($p4->note))
                                                        <div class="mb-1 rounded-md bg-sky-50 p-2 text-[11px] text-sky-700 ring-1 ring-inset ring-sky-200">
                                                            <span class="font-semibold">Note:</span>
                                                            <span>{{ Str::limit($p4->note, 200) }}</span>
                                                        </div>
                                                    @endif

                                                    {{-- Return status --}}
                                                    @if($ret)
                                                        <div class="mb-1 inline-flex items-center gap-2">
                                                            <span class="rounded-md bg-rose-50 px-2 py-0.5 text-rose-700 ring-1 ring-inset ring-rose-200">Returned</span>
                                                            <span class="text-[11px] text-slate-500">
                                                                {{ $ret->returned_date instanceof \Illuminate\Support\Carbon
                                                                    ? $ret->returned_date->format('Y-m-d')
                                                                    : Str::of($ret->returned_date)->limit(10) }}
                                                            </span>
                                                        </div>
                                                        @if(!empty($ret->extra))
                                                            <div class="rounded-md bg-slate-50 p-2 text-[11px] text-slate-600 ring-1 ring-inset ring-slate-200">
                                                                {{ is_string($ret->extra) ? Str::limit($ret->extra, 160) : json_encode($ret->extra) }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <span class="text-emerald-600">Active</span>
                                                    @endif
                                                </td>
                                            @endif

                                            {{-- Per-invoice columns --}}
                                            <td class="px-4 py-2 align-top">{{ $entry->date ?? 'N/A' }}</td>

                                            <td class="px-4 py-2 align-top">
                                                @if(!empty($entry->maid_id) && $entry->maid_id !== 'N/A')
                                                    <a href="{{ url('/maid/invoices/' . rawurlencode($entry->maidRelation?->name)) }}"
                                                       target="_blank"
                                                       class="font-medium text-indigo-600 hover:underline">
                                                        {{ $entry->maidRelation?->name ?? 'N/A' }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">N/A</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-2 align-top">
                                                <a href="{{ url('/get/invoice/cat4/' . $entry->refCode) }}"
                                                   target="_blank"
                                                   class="font-medium text-indigo-600 hover:underline">
                                                    {{ $entry->refCode ?? 'N/A' }}
                                                </a>
                                            </td>

                                            
                                            <td class="px-4 py-2 align-top">{{ number_format($entry->amount, 2) }}</td>

                                            <td class="px-4 py-2 align-top">
                                                @if(!empty($entry->creditNoteRef) && $entry->creditNoteRef !== 'No Data')
                                                    <a href="{{ url('/view/jv/selected/' . $entry->creditNoteRef) }}"
                                                       target="_blank"
                                                       class="font-semibold text-rose-600 hover:underline">
                                                        {{ $entry->creditNoteTargets[0]->amount ?? $entry->creditNoteRef }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">No Data</span>
                                                @endif
                                            </td>

                                      
                                            <td class="px-4 py-2 align-top">{{ $entry->notes ?? 'N/A' }}</td>

                                            <td class="px-4 py-2 align-top">
                                                @if(!empty($entry->receiveRef) && $entry->receiveRef !== 'No Data')
                                                    <a href="{{ url('/view/jv/selected/' . $entry->receiveRef) }}"
                                                       target="_blank"
                                                       class="text-indigo-600 hover:underline">
                                                        {{ $entry->receiveRef }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">No Data</span>
                                                @endif
                                            </td>

                                  

                                            <td class="px-4 py-2 align-top">{{ $entry->created_by ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 align-top">
                                                {{ $entry->created_at ? $entry->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                {{-- Optional group separator row --}}
                                <tr>
                                    <td colspan="13" class="bg-slate-50 py-1"></td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
