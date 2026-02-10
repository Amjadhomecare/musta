<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>T-Account for {{ $name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex items-center justify-center mb-4 space-x-4">
            <h2 class="text-2xl font-bold text-center">
                Account statement as P1: {{ $name }}
            </h2>

            @if(!empty($maid->img))
                <img src="{{  $maid->img }}" 
                    alt="{{ $name }}" 
                   class="w-20 h-20 rounded-full object-cover border shadow">
            @endif
        </div>


        <div class="grid grid-cols-2 gap-6">
            <!-- Credit Column -->
            <div>
                <h3 class="text-xl font-semibold mb-2 text-green-600">Credits</h3>
                <div class="border rounded">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-2 text-left">Date</th>
                                <th class="p-2 text-right">Amount</th>
                                <th class="p-2">RefCode & Accounts</th>
                            </tr>
                        </thead>
                        <tbody>
            @foreach($credits as $entry)
                    <tr class="border-t">
                        <td class="p-2">{{ $entry->date }}</td>
                        <td class="p-2 text-right text-green-600 font-medium">{{ number_format($entry->amount, 2) }}</td>
                        <td class="p-2">
                            <span class="font-semibold text-gray-800">{{ $entry->refCode }}</span>

                            @php
                                $related = $relatedVouchers->get($entry->refCode); // Collection or null
                            @endphp

                            @if($related && $related->isNotEmpty())
                                <ul class="mt-1 text-xs text-gray-500 list-disc list-inside">
                                    @foreach(
                                        $related
                                            ->pluck('accountLedger.ledger')   // use relation
                                            ->filter()                        // drop nulls
                                            ->unique()
                                            ->sort()
                                        as $ledgerName
                                    )
                                        <li>{{ $ledgerName }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Debit Column -->
            <div>
                <h3 class="text-xl font-semibold mb-2 text-red-600">Debits</h3>
                <div class="border rounded">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="p-2 text-left">Date</th>
                                <th class="p-2 text-right">Amount</th>
                                <th class="p-2">RefCode & Accounts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        @foreach($debits as $entry)
            <tr class="border-t">
                <td class="p-2">{{ $entry->date }}</td>
                        <td class="p-2 text-right text-red-600 font-medium">{{ number_format($entry->amount, 2) }}</td>
                        <td class="p-2">
                            <span class="font-semibold text-gray-800">{{ $entry->refCode }}</span>

                            @php
                                $related = $relatedVouchers->get($entry->refCode); // Collection or null
                            @endphp

                            @if($related && $related->isNotEmpty())
                                <ul class="mt-1 text-xs text-gray-500 list-disc list-inside">
                                    @foreach(
                                        $related
                                            ->pluck('accountLedger.ledger')   {{-- use relation instead of dropped "account" --}}
                                            ->filter()                        {{-- drop nulls --}}
                                            ->unique()
                                            ->sort()
                                        as $ledgerName
                                    )
                                        <li>{{ $ledgerName }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-6 bg-gray-50 p-4 rounded shadow-inner">
            <h3 class="text-lg font-bold mb-2">Summary</h3>
            <div class="flex justify-between text-sm">
                <div>Total Credit: <span class="font-semibold text-green-600">{{ number_format($totalCredit, 2) }}</span></div>
                <div>Total Debit: <span class="font-semibold text-red-600">{{ number_format($totalDebit, 2) }}</span></div>
                <div>Remaining Amount: 
                    <span class="font-semibold {{ $netAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($netAmount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Acknowledgment Section -->
        <div class="mt-10 bg-white border p-6 rounded shadow">
            <h3 class="text-lg font-bold mb-4">Acknowledgment</h3>
            <p class="text-sm leading-relaxed mb-6">
                I, <span class="font-semibold underline">{{ $name }}</span>, hereby acknowledge that I have received all my entitled credits as per the above statement. I confirm that there are no remaining dues or outstanding payments from the company.
            </p>

            <div class="grid grid-cols-2 gap-6 text-sm mt-8">
                <div>
                    <p class="font-semibold">Maid Signature</p>
                    <div class="border-t border-gray-400 mt-8"></div>
                </div>
                <div>
                    <p class="font-semibold">Date</p>
                    <div class="border-t border-gray-400 mt-8"></div>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="font-semibold">Staff Signature</p>
                    <div class="border-t border-gray-400 mt-8"></div>
                </div>
                <div>
                    <p class="font-semibold">Date</p>
                    <div class="border-t border-gray-400 mt-8"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
