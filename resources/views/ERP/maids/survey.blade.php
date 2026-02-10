<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Maid Survey — {{ $maid->name }}</title>

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 py-10">
  <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-2xl p-8">
    
    <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center">
      Maid Feedback — {{ $maid->name }}
    </h1>
    <p class="text-gray-500 text-center mb-6">
      شكراً لمشاركتك رأيك. سيتم استخدام التقييم لتحسين الخدمة.
    </p>

    {{-- Success / Error Alerts --}}
    @if(session('ok'))
      <div class="bg-green-50 text-green-700 border border-green-300 rounded-lg p-4 mb-5 text-center">
        {{ session('ok') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="bg-red-50 text-red-700 border border-red-300 rounded-lg p-4 mb-5 text-center">
        {{ implode(' ', $errors->all()) }}
      </div>
    @endif

    <form method="POST" action="{{ route('maid.survey.store', $maid->id) }}" class="space-y-8">
      @csrf

      {{-- Overall Satisfaction --}}
      <div class="border border-gray-200 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3">
          How satisfied are you with the maid’s overall performance? <span class="text-red-500">*</span>
        </h2>
        <p class="text-sm text-gray-500 mb-4">ما مدى رضاك عن الأداء العام للعاملة المنزلية؟</p>
        <input type="hidden" name="customer_id" value="{{ $customer_id }}">

        <div class="grid grid-cols-1 gap-3">
          @foreach($SAT as $val => $label)
            <label class="flex items-center space-x-3 bg-gray-50 hover:bg-gray-100 rounded-lg px-4 py-2 cursor-pointer">
              <input type="radio" name="satisfied" value="{{ $val }}" 
                     class="text-sky-600 focus:ring-sky-500"
                     {{ old('satisfied') == $val ? 'checked' : '' }}>
              <span class="text-gray-700">{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>

      {{-- Performance Ratings --}}
      <div class="border border-gray-200 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3">
          How would you rate the maid’s performance in the following areas? <span class="text-red-500">*</span>
        </h2>
        <p class="text-sm text-gray-500 mb-4">كيف تُقيِّم أداء العاملة المنزلية في النواحي التالية؟</p>

        <div class="overflow-x-auto">
          <table class="min-w-full border border-gray-200 text-center text-sm text-gray-700">
            <thead class="bg-gray-100 text-gray-600">
              <tr>
                <th class="px-4 py-2 text-left">Area</th>
                <th class="px-2 py-2">Excellent<br>(ممتاز)</th>
                <th class="px-2 py-2">Good<br>(جيد)</th>
                <th class="px-2 py-2">Fair<br>(مقبول)</th>
                <th class="px-2 py-2">Poor<br>(سيء)</th>
                <th class="px-2 py-2">N/A<br>(لا يوجد)</th>
              </tr>
            </thead>
            <tbody>
              @php
                $fields = [
                  'perf_cleaning' => 'Cleaning (التنظيف)',
                  'perf_cooking' => 'Cooking (الطبخ)',
                  'perf_childcare' => 'Childcare, if applicable (رعاية الأطفال إن وجدت)',
                  'perf_communication' => 'Communication & Attitude (التواصل والسلوك)',
                ];
                $options = [5,4,3,2,0];
              @endphp

              @foreach($fields as $name => $title)
                <tr class="border-t border-gray-200 hover:bg-gray-50">
                  <td class="px-4 py-3 text-left font-medium">{{ $title }}</td>
                  @foreach($options as $opt)
                    <td class="py-2">
                      <input type="radio" name="{{ $name }}" value="{{ $opt }}" 
                             class="text-sky-600 focus:ring-sky-500"
                             {{ old($name) == $opt ? 'checked' : '' }}>
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      {{-- Notes --}}
      <div class="border border-gray-200 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-3">Notes (ملاحظات)</h2>
        <textarea name="note" placeholder="اكتب ملاحظاتك هنا (اختياري)"
                  class="w-full rounded-lg border border-gray-300 focus:ring-sky-500 focus:border-sky-500 p-3"
                  rows="4">{{ old('note') }}</textarea>
      </div>

      {{-- Submit --}}
      <div class="text-center">
        <button type="submit"
                class="bg-sky-600 hover:bg-sky-700 text-white font-semibold px-8 py-3 rounded-lg shadow">
          Submit / إرسال
        </button>
      </div>
    </form>
  </div>
</body>
</html>
