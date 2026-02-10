<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contract View</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Optional: fine-tune Tailwind (no custom colors needed, but keeping the hook) -->
  <script>
    tailwind.config = {
      theme: { extend: {} }
    }
  </script>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden print:shadow-none print:border print:rounded-none">

      <!-- Header -->
      <header class="border-b border-gray-200">
        <img src="{{ env('contract_header') }}" alt="Company Header" class="w-full h-auto block">
      </header>

      <!-- Print button -->
      <div class="p-4 sm:p-6 print:hidden">
        <button onclick="window.print();" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 9V3h12v6h2a2 2 0 0 1 2 2v5h-4v5H6v-5H2v-5a2 2 0 0 1 2-2h2Zm2-4v4h8V5H8Zm8 12H8v3h8v-3Zm4-3v-2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v2h2v-1h12v1h2Z"/></svg>
          Print
        </button>
      </div>

      <!-- Contract details -->
      <section class="px-4 pb-6 sm:px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border border-gray-200 rounded-lg p-4">
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Date</dt>
                <dd class="text-gray-900">{{$conDetails->created_at}}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Contract No</dt>
                <dd class="text-gray-900">{{$conDetails?->contract_ref}}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Customer</dt>
                <dd class="text-gray-900">{{$conDetails?->customerInfo?->name}}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Mobile</dt>
                <dd class="text-gray-900">{{$conDetails->customerInfo?->phone}}</dd>
              </div>
            </dl>
          </div>

          <div class="border border-gray-200 rounded-lg p-4">
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Name</dt>
                <dd class="text-gray-900">{{$conDetails?->maidInfo?->name}}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">Start Date</dt>
                <dd class="text-gray-900">{{$conDetails->started_date}}</dd>
              </div>
              <div class="flex justify-between gap-4">
                <dt class="font-semibold text-gray-600">End Date</dt>
                <dd class="text-gray-900">{{$conDetails->ended_date}}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Line items table -->
        <div class="mt-6">
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm">
              <thead class="bg-emerald-600 text-white">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">NO</th>
                  <th class="px-4 py-3 text-left font-semibold">DESCRIPTION</th>
                  <th class="px-4 py-3 text-left font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr class="bg-white">
                  <td class="px-4 py-3">1</td>
                  <td class="px-4 py-3">{{$conDetails->category}}</td>
                  <td class="px-4 py-3">AED{{$conDetails->amount}}</td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                  <td class="px-4 py-3 font-semibold" colspan="2">Total</td>
                  <td class="px-4 py-3 font-semibold">AED{{$conDetails->amount}}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Contract meta -->
        <div class="mt-6 border border-gray-200 rounded-lg p-4">
          <h3 class="text-base font-semibold mb-2">Contract Detail</h3>
          <p class="text-sm text-gray-700">
            Created by: <span class="font-medium text-gray-900">{{$conDetails->created_by}}</span>
            <span class="mx-2 text-gray-400">|</span>
            Date: <span class="font-medium text-gray-900">{{$conDetails->created_at}}</span>
          </p>
        </div>

        <!-- Return info or Active -->
        <div class="mt-6">
          @if(isset($conDetails['returnInfo']) && !is_null($conDetails['returnInfo']->maid_return_name))
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-900">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Returned Date</th>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Maid Return Name</th>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Contract</th>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Customer</th>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Reason</th>
                    <th class="px-4 py-3 text-left font-semibold border-b border-gray-200">Returned By</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr class="bg-white">
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->created_at ?? 'no return' }}</td>
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->maid_return_name ?? 'no return' }}</td>
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->contract ?? 'no return' }}</td>
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->customer ?? 'no return' }}</td>
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->reason ?? 'no return' }}</td>
                    <td class="px-4 py-3">{{ $conDetails['returnInfo']->created_by ?? 'no return' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          @else
            <p class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 bg-green-50 border border-green-200 rounded-md px-3 py-2">
              <span class="text-green-600">●</span> Active
            </p>
          @endif
        </div>
      </section>

      <!-- Footer -->
      <footer class="border-t border-gray-200">
        <img src="{{ env('contract_footer') }}" alt="Footer Image" class="w-full h-auto block">
      </footer>
    </div>
  </div>
</body>
</html>
