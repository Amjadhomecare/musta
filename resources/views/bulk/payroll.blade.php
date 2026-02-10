<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
  <div class="w-full max-w-md">
    @if (session('success'))
      <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-center text-green-700 shadow">
        {{ session('success') }}
      </div>
    @endif

    <div class="rounded-3xl bg-white p-8 shadow-2xl">
      <div class="flex flex-col items-center">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100">
          <i class="bi bi-cloud-arrow-up text-4xl text-blue-500"></i>
        </div>
        <h2 class="mb-1 text-xl font-bold text-gray-900">Upload Payroll Sheet</h2>
        <p class="mb-2 text-center text-gray-500 text-sm">
          Upload your Excel or CSV payroll file here.
        </p>
        <a href="https://nextmetaerp.s3.eu-north-1.amazonaws.com/documents/2025/07/tsluVhOZpzeSJ7wowylsQJn7Aru2rTOBq6VqGMQa.csv"
           class="mb-4 inline-flex items-center gap-1 text-blue-500 hover:underline font-medium text-sm"
           target="_blank">
          <i class="bi bi-download"></i>
          Download template
        </a>
      </div>

      <form action="{{ route('payroll.import') }}" method="POST" enctype="multipart/form-data" class="mt-2">
        @csrf

        <label for="file" class="block cursor-pointer">
          <div class="mb-2 flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50 py-6 hover:shadow-lg transition">
            <i class="bi bi-file-earmark-spreadsheet text-3xl text-blue-400 mb-1"></i>
            <span class="text-gray-400 text-xs">
              Drag & drop or <span class="font-semibold text-blue-500">browse</span>
            </span>
            <input id="file"
                   name="file"
                   type="file"
                   class="hidden"
                   required
                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
          </div>
        </label>
        @error('file')
          <span class="text-xs text-red-500">{{ $message }}</span>
        @enderror

        <button type="submit"
                class="mt-3 w-full rounded-full bg-blue-600 py-2 font-semibold text-white shadow hover:bg-blue-700 transition">
          <i class="bi bi-upload me-1"></i>
          Import
        </button>
      </form>
    </div>
  </div>
</div>
