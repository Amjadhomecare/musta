{{-- resources/views/upcoming/import.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Import Upcoming Installments (P4)</title>

  {{-- Tailwind CSS CDN --}}
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
  <div class="max-w-5xl mx-auto px-4 py-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-semibold">Import Upcoming Installment — Package 4</h1>
        <p class="text-sm text-gray-600 mt-1">
          Upload an Excel file (.csv) matching the required columns. Use the template below.
        </p>
      </div>
      <a
        href="https://nextmetaerp.s3.eu-north-1.amazonaws.com/documents/2025/09/mHsZCWjhgBF8wwiGYrBeVmbmENIznxmHz3uB6KTR.csv"
        class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-100 transition"
        download
      >
        <!-- Download icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0L16.5 12M12 16.5V3" />
        </svg>
        Download Template
      </a>
    </div>

    {{-- Success --}}
    @if(session('success'))
      <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif

    {{-- Errors --}}
    @if($errors->any())
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <ul class="list-disc pl-5 space-y-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Upload Card -->
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-6 mb-10">
      <form action="{{ route('upcoming.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <!-- File input + drop zone look -->
        <label for="file" class="block text-sm font-medium text-gray-700">Choose Excel File (.csv)</label>
        <div
          class="relative flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/60 p-8 hover:border-indigo-300 transition"
        >
          <div class="text-center">
            <div class="mx-auto mb-3 rounded-full bg-white shadow p-3 w-max">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0L16.5 12M12 16.5V3" />
              </svg>
            </div>
            <p class="text-sm text-gray-700">
              Drag & drop or
              <label for="file" class="font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">browse</label>
              to upload
            </p>
            <p class="mt-1 text-xs text-gray-500">.csv only • Max ~20MB (recommended)</p>
          </div>
          <input
            id="file"
            name="file"
            type="file"
            accept=".csv,.xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
            required
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
          />
        </div>

        <button
          type="submit"
          class="inline-flex items-center justify-center w-full sm:w-auto rounded-xl bg-indigo-600 px-5 py-2.5 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
        >
          Import Upcoming Installment (P4)
        </button>
      </form>
    </div>

    <!-- Columns Reference -->
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-6">
      <h2 class="text-lg font-semibold mb-4">Required Columns</h2>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Column Name</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Required</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Example Value</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Description</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">accrued_date</td>
            <td class="px-4 py-3">Yes</td>
            <td class="px-4 py-3">Date (YYYY-MM-DD)</td>
            <td class="px-4 py-3">2025-01-04</td>
            <td class="px-4 py-3">The date when the installment is due.</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">customer</td>
            <td class="px-4 py-3">Yes</td>
            <td class="px-4 py-3">String</td>
            <td class="px-4 py-3">Mr jone smith</td>
            <td class="px-4 py-3">Customer name (looked up &amp; mapped to <code>customer_id</code> internally).</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">note</td>
            <td class="px-4 py-3">No</td>
            <td class="px-4 py-3">String</td>
            <td class="px-4 py-3">Installment for January</td>
            <td class="px-4 py-3">Additional details about the installment.</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">cheque</td>
            <td class="px-4 py-3">No</td>
            <td class="px-4 py-3">String</td>
            <td class="px-4 py-3">123456</td>
            <td class="px-4 py-3">Cheque number for the payment (if applicable).</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">contract</td>
            <td class="px-4 py-3">Yes</td>
            <td class="px-4 py-3">String</td>
            <td class="px-4 py-3">p4_ABCD123</td>
            <td class="px-4 py-3">Contract reference (must exist in contract ref).</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">amount</td>
            <td class="px-4 py-3">Yes</td>
            <td class="px-4 py-3">Numeric</td>
            <td class="px-4 py-3">550</td>
            <td class="px-4 py-3">The amount to be paid.</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">created_by</td>
            <td class="px-4 py-3">No</td>
            <td class="px-4 py-3">String</td>
            <td class="px-4 py-3">admin</td>
            <td class="px-4 py-3">Optional. If omitted, the system uses the current user.</td>
          </tr>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">created_at</td>
            <td class="px-4 py-3">No</td>
            <td class="px-4 py-3">Date (YYYY-MM-DD)</td>
            <td class="px-4 py-3">2025-01-04</td>
            <td class="px-4 py-3">Creation date. If omitted, current date is used.</td>
          </tr>
          </tbody>
        </table>
      </div>

      <p class="text-xs text-gray-500 mt-4">
        Tip: Customer names are normalized (trim + uppercase) before lookup to match your database values.
      </p>
    </div>
  </div>
</body>
</html>
