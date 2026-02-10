<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>No Objection Certificate</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Ensure true A4 pages with no browser margins */
    @page {
      size: A4;   /* 210mm x 297mm */
      margin: 0;
    }

    @media print {
      body, html {
        width: 210mm;
        margin: 0;
        padding: 0;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      /* Two-row grid: flexible content + fixed footer (110px) */
      .page {
        width: 210mm;
        height: 297mm;                 /* fixed height for each printed page */
        margin: 0;
        padding: 36px 32px;
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        overflow: hidden !important;

        display: grid !important;
        grid-template-rows: minmax(0, 1fr) 110px; /* footer height adjustable */
        page-break-after: always;
      }

      .page:last-child { page-break-after: auto; }

      .no-print { display: none !important; }

      .avoid-break {
        page-break-inside: avoid;
        break-inside: avoid;
      }
    }

    /* Screen preview mirrors print layout so positions look the same */
    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #fff;
      box-shadow: 0 8px 24px rgba(0,0,0,0.09);
      border-radius: 16px;
      border: 1px solid #e5e7eb;
      padding: 36px 32px;
      box-sizing: border-box;
      position: relative;

      display: grid;                  /* grid on screen too */
      grid-template-rows: minmax(0, 1fr) 110px;  /* same fixed footer height */
    }

    /* Explicit content area = row 1 */
    .content {
      grid-row: 1;
      min-height: 0;                 /* allow content to shrink in grid */
      overflow: visible;
      display: block;
    }

    /* Footer row container that pins signatures at a fixed position */
    .signatures {
      grid-row: 2;                    /* always occupies the footer row */
      display: flex;
      justify-content: space-between;
      gap: 16px;
      padding-top: 8px;               /* breathing room from content */
      align-items: flex-end;
    }
  </style>
</head>
<body>
@php
  /** @var \App\Models\Noc $noc */
  $maid = $noc->extra_data ?? [];
  $visaUnder = strtolower($maid['maid_visa_under'] ?? '');
  $companyName = match ($visaUnder) {
      'fc' => 'FAMILY CARE FOR DOMESTIC WORKERS SERVICES LLC',
      'h'  => 'HOMECARE FOR DOMESTIC WORKERS SERVICES LLC',
      default => (env('company_name') ?: '—'),
  };

  $headerDate = $noc->t_date ? \Carbon\Carbon::parse($noc->t_date)->format('d/m/Y') : now()->format('d/m/Y');
  $travelDate = $noc->t_date ? \Carbon\Carbon::parse($noc->t_date)->format('d/m/Y') : 'N/A';
  $returnDate = $noc->r_date ? \Carbon\Carbon::parse($noc->r_date)->format('d/m/Y') : 'N/A';

  $maidName    = $noc->maid_name ?? 'N/A';
  $nationality = $maid['nationality'] ?? 'N/A';
  $passportNo  = $maid['passport'] ?? 'N/A';
  $salary      = $maid['salary'] ?? null;
  $since       = $maid['since']  ?? null;

  $customerName = $noc->customer_name ?? 'N/A';
  $cusId        = $noc->extra_data['cus_id']    ?? 'N/A';
  $cusPhone     = $noc->extra_data['cus_phone'] ?? 'N/A';

  $country = $noc->country ?? 'N/A';
@endphp

  <!-- First Page -->
  <div class="page">
    <!-- Wrap ALL content in a single .content element -->
    <div class="content">
      <!-- Header -->
      <div class="text-left mt-40 mb-12">
        <div class="text-sm text-gray-600 font-semibold">Date:</div>
        <div class="text-lg font-bold text-blue-900">{{ $headerDate }}</div>
      </div>

      <!-- Title -->
      <div class="w-full text-center mb-8">
        <span class="bg-blue-50 px-7 py-2 rounded-full text-xl font-extrabold tracking-wide text-blue-900 border border-blue-200 shadow-sm">
          NO OBJECTION CERTIFICATE
        </span>
      </div>

      <!-- Addressed To -->
      <div class="mb-6">
        <div class="font-bold text-gray-800 mb-1">To:</div>
        <div class="text-base font-semibold text-blue-900">
          Consulate of {{ strtoupper($country) }} in Dubai
        </div>
      </div>

      <!-- Certificate Content -->
      <div class="mb-8 text-[15px] leading-7">
        <p class="mb-4">
          This is to certify that
          <span class="font-bold underline">MS. {{ $maidName }}</span>,
          <span class="capitalize">{{ $nationality }}</span> national with passport No.
          <span class="font-bold">{{ $passportNo }}</span>, employed at
          <span class="font-bold">{{ $companyName }}</span>
          @if($since)
            since <span class="font-bold">{{ $since }}</span>,
          @else
            ,
          @endif
          profession <span class="font-bold">HOUSEMAID</span>
          @if(!is_null($salary))
            with salary of <span class="font-bold">{{ number_format($salary) }} AED</span>,
          @else
            ,
          @endif
          will be traveling to <span class="font-bold">{{ $country }}</span>.
        </p>

        <p class="mb-4">
          I hereby acknowledge that I am fully aware and have no objection for her to travel outside the UAE with the
          family of <span class="font-bold">{{ $customerName }}</span>,
          Passport Number <span class="font-bold">{{ $cusId }}</span>,
          Mobile Number <span class="font-bold">{{ $cusPhone }}</span>,
          Date of traveling <span class="font-bold">{{ $travelDate }}</span>
          till <span class="font-bold">{{ $returnDate }}</span>.
        </p>

        <p class="mb-4 italic text-gray-500 border-l-4 border-blue-200 pl-3">
          This certificate is issued at the specific request of the above-mentioned individual for travel purposes only.
          The company accepts no responsibility for the use of this certificate beyond its intended purpose. Any misuse
          will render this certificate null and void.
        </p>
      </div>
    </div>

    <!-- Signature Section (fixed footer row) -->
    <div class="signatures avoid-break">
      <div class="flex flex-col items-center">
        <div class="font-semibold">HR Manager</div>
        <div></div>
        <div class="mt-2 h-8"></div>
        <div class="border-t-2 border-gray-400 w-44"></div>
        <div class="text-xs text-gray-400">Signature </div>
      </div>
    </div>
  </div>

  <!-- Second Page -->
  <div class="page">
    <!-- Wrap ALL content in a single .content element (this fixes your issue) -->
    <div class="content" dir="rtl">
      <!-- Header Date -->
      <div class="text-right mt-10 mb-8">
        <div class="text-sm text-gray-600 font-semibold">التاريخ:</div>
        <div class="text-lg font-bold text-blue-900">{{ $headerDate }}</div>
      </div>

      <!-- Title -->
      <div class="w-full text-center mb-8">
        <span class="bg-blue-50 px-7 py-2 rounded-full text-xl font-extrabold tracking-wide text-blue-900 border border-blue-200 shadow-sm">
          اخلاء مسؤولية
        </span>
      </div>

      <!-- Arabic Disclaimer -->
      <div class="mb-8 text-[16px] leading-9 text-right">
        <p class="mb-4">
          أنا الموقع أدناه السيد/ة <span class="font-bold">{{ $customerName }}</span> أقر وأعترف بعدم مطالبتي بأي مبالغ أو أي تعويض أو أرجاع المبلغ المقدم أو المطالبة ببديل 
          في حال هروب او اي ضرر صحي او جسدي قد تتعرض له نتيجة سفر العاملة 
          <span class="font-bold">{{ $maidName }}</span> الجنسية <span class="font-bold">{{ $nationality }}</span> 
          تحمل جواز رقم <span class="font-bold">{{ $passportNo }}</span> 
          عند السفر خارج دولة الامارات العربية المتحدة وسيتم اعادة مبلغ التامين عند استلام أصل الجواز و في حال عدم اعادة الجواز الاصلي بعد العودة من السفر ضمن الفترة المحددة مسبقا. 
          سيتم سحب الضمان المدفوع بقيمة <span class="font-bold">15,000 درهم اماراتي</span> في حالة تأخير أو عدم عودة الخادمة في التاريخ المحدد.
        </p>

        <p class="mt-6">ولكم جزيل الشكر والتقدير.</p>
      </div>
    </div>

    <!-- Signature Section (fixed footer row) -->
    <div class="signatures items-center avoid-break" dir="rtl">
      <div class="flex flex-col items-center">
        <div class="font-semibold">توقيع العميل :</div>
        <div class="mt-12 border-t-2 border-gray-400 w-48"></div>
      </div>
      <div class="flex flex-col items-center">
        <div class="font-semibold">توقيع الادارة :</div>
        <div class="mt-12 border-t-2 border-gray-400 w-48"></div>
      </div>
    </div>
  </div>
</body>
</html>
