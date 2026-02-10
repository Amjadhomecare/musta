<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعهد بتعديل وضع عامل مساعد</title>
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            html, body { 
                width: 210mm; 
                height: 297mm; 
                margin: 0; 
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print { display: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Tahoma, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            background: #f5f5f5;
            direction: rtl;
        }
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 10mm 15mm;
            position: relative;
        }
        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            z-index: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .watermark img { width: 280px; }
        
        .content { position: relative; z-index: 1; }
        
        /* Header */
        .header-img { width: 100%; height: auto; margin-bottom: 8px; }
        .title { 
            text-align: center; 
            font-size: 18pt; 
            font-weight: bold; 
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1a5276;
        }
        
        /* Dates Bar */
        .dates-bar {
            display: table;
            width: 100%;
            background: #eaf2f8;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .dates-bar span { margin-left: 30px; }
        .dates-bar strong { color: #1a5276; }
        
        /* Info Tables */
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        table.info th {
            background: #2c3e50;
            color: #fff;
            text-align: right;
            padding: 6px 10px;
            font-weight: bold;
        }
        table.info td {
            border: 1px solid #bdc3c7;
            padding: 5px 10px;
        }
        table.info td.label {
            background: #ecf0f1;
            color: #7f8c8d;
            width: 80px;
            font-size: 9pt;
        }
        table.info td.value {
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Terms */
        .terms {
            background: #fdfefe;
            border: 1px solid #d5d8dc;
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 10pt;
            line-height: 1.6;
            text-align: justify;
        }
        .terms p { margin-bottom: 6px; }
        .terms p:last-child { margin-bottom: 0; }
        
        /* Signature */
        .signature-row {
            display: table;
            width: 100%;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #bdc3c7;
        }
        .sig-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .sig-box label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 10pt;
        }
        .sig-line {
            border-bottom: 1px solid #2c3e50;
            min-height: 45px;
            padding-top: 10px;
        }
        .sig-line img { max-height: 40px; }
        
        /* Print Button */
        .print-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background: #3498db;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 13pt;
            border-radius: 5px;
            cursor: pointer;
            z-index: 999;
        }
        .print-btn:hover { background: #2980b9; }
        
        @media screen {
            body { padding: 20px; }
            .page { box-shadow: 0 0 15px rgba(0,0,0,0.15); }
        }
    </style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print()">🖨️ طباعة</button>

<div class="page">
    <div class="watermark">
        <img src="{{ env('logo') }}" alt="">
    </div>
    
    <div class="content">
        <!-- Header Image -->
        <img src="{{ env('contract_header') }}" alt="" class="header-img">
        
        <!-- Title -->
        <div class="title">تعهد بتعديل وضع عامل مساعد</div>
        
        <!-- Dates -->
        <div class="dates-bar">
            <span><strong>بداية التجربة :</strong> {{ $conDetails?->trial_start ?? $conDetails?->started_date }}</span>
            <span><strong>نهاية التجربة :</strong> {{ $conDetails?->trial_end ?? (new DateTime($conDetails?->started_date))->add(new DateInterval('P6D'))->format('Y-m-d') }}</span>
            <span><strong>أنا الموقع أدناه :</strong></span>
        </div>
        
        <!-- Customer Info -->
        <table class="info">
            <tr><th colspan="6">بيانات الكفيل / صاحب العمل</th></tr>
            <tr>
                <td class="label">الاسم</td>
                <td class="value">{{ $conDetails?->customerInfo?->name }}</td>
                <td class="label">الجنسية</td>
                <td class="value">UAE</td>
                <td class="label">رقم الهاتف</td>
                <td class="value">{{ $conDetails?->customerInfo?->phone }}</td>
            </tr>
            <tr>
                <td class="label">رقم الهوية</td>
                <td class="value" colspan="5">{{ $conDetails?->customerInfo?->idNumber }}</td>
            </tr>
        </table>
        
        <!-- Maid Info -->
        <table class="info">
            <tr><th colspan="6">بيطلب أطلب كفالة للعاملة المساعدة</th></tr>
            <tr>
                <td class="label">الاسم</td>
                <td class="value">{{ $conDetails?->maidInfo?->name }}</td>
                <td class="label">الجنسية</td>
                <td class="value">{{ $conDetails?->maidInfo?->nationality }}</td>
                <td class="label">جواز السفر</td>
                <td class="value">{{ $conDetails?->maidInfo?->passport_number }}</td>
            </tr>
        </table>
        
        <!-- Terms -->
        <div class="terms">
            <p>أقر بأنني استلمت العاملة المذكورة أعلاه كما أتعهد بإتمام كافة الإجراءات الحكومية المعمول بها داخل دولة الإمارات العربية المتحدة للعاملة بعد إنقضاء فترة التجربة المتفق عليها والمحددة بخمس أيام عمل.</p>
            <p>لإرجاع العاملة في خلال خمسة أيام واحتساب 100 درهم عن كل يوم.</p>
            <p>وفي حال ارجاع العاملة بعد خمسة أيام بدون استكمال الاجراءات الخاصة بها يتم خصم 210 درهم عن كل يوم اضافي ويتحمل كافة المخالفات الحكومية المترتبة على ذلك.</p>
            <p>وفي حال ارجاع العاملة بعد 14 يوم من تعديل الوضع، وبدون إستكمال الإجراءات الخاصة بالضمان الصحي للعاملة تحمل مبلغ 300 درهم غرامات الضمان الصحي وإذا تجاوزت المدة أكثر من شهر تحمل 300 درهم عن كل شهر.</p>
            <p>كما أتعهد في حالة إرجاع العاملة إلى المكتب بتسليم كافة الأغراض الشخصية والأوراق الثبوتية الخاصة بها.</p>
            <p>يلتزم بأن يكون بيانك مطابق في نفس بيانات الكفيل المظلظ على التأشيرة ، بإلتزم المتعهد باستكمال كل من التأشيرة وتعديل الوضع داخل المركز وفي حال غير ذلك لا يحق للمتعهد باسترداد المبلغ أو طلب بديل عن العاملة .</p>
            <p>يتم استرجاع المبلغ فى خلال 14 يوم من تاريخ إلغاء الأقامة ، تعديل الوضع أو تسليم بلاغ انقطاع عن العمل او إنتهاء فترة التجربة.</p>
        </div>
        
        <!-- Signature -->
        <div class="signature-row">
            <div class="sig-box">
                <label>الاسم:</label>
                <div class="sig-line">{{ $conDetails?->customerInfo?->name }}</div>
            </div>
            <div class="sig-box">
                <label>التوقيع:</label>
                <div class="sig-line">
                    @if($conDetails?->signature)
                        <img src="{{ $conDetails->signature }}" alt="">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
