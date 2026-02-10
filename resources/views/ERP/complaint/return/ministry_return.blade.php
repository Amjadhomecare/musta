<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Return Document</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">نموذج إرجاع عامل مساعد</h1>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th colspan="8" class="text-center">بيانات مكتب الاستقدام</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3">الإمارة: Dubai</td>
                        <td colspan="5">اسم مكتب الاستقدام: {{env('company_name') ?? "NA" }}</td>
                    </tr>
                </tbody>
                <thead class="thead-light">
                    <tr>
                        <th colspan="8" class="text-center">بيانات صاحب العمل</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>الجنسية: {{$con1->customerInfo?->nationality}}</td>
                        <td colspan="4">رقم الهاتف: {{$con1->customerInfo?->phone}}</td>
                        <td colspan="3">اسم صاحب العمل: {{$con1?->customer}}</td>
                    </tr>
                    <tr>
                        <td colspan="4">رقم الهوية الإماراتية:{{$con1->customerInfo?->idNumber}}</td>
                    </tr>
                </tbody>
                <thead class="thead-light">
                    <tr>
                        <th colspan="8" class="text-center">بيانات العامل المساعد</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">الجنسية:{{$con1->maidInfo?->nationality}} </td>
                        <td colspan="3">رقم الهاتف: **************</td>
                        <td colspan="3">اسم العامل المساعد:{{$con1->maidInfo?->name}} </td>
                    </tr>
                    <tr>
                        <td colspan="8">رقم الجواز / الرقم الموحد: {{$con1->maidInfo?->passport_number}} </td>
                    </tr>
                </tbody>
                <thead class="thead-light">
                    <tr>
                        <th colspan="8" class="text-center">الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8">
                                                        <p>..........................................
                                                            ..................................................................................................................
                            :حالات رد مبلغ استقدام العامل المساعد لصاحب العمل خلال فترة التجربة (6 أشهر)</p>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-right">الملاحظات</th>
                                                                    <th>حالات رد مبلغ استقدام العامل المساعد لصاحب العمل خلال فترة التجربة (6 أشهر)</th>
                                                                    <th style="width: 50px;">✓</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">في حال ثبوت عدم اللياقة البدنية الصحية للعامل المساعد خلال فترة التجربة فيلتزم المكتب برد كامل اتعاب استقدام العمالة، إضافة إلى ذلك يلتزم برد أية رسوم حكومية تحملها صاحب العمل.</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">انتفاء الكفاءة المهنية وحسن السلوك الشخصي في العامل المساعد.</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">انهاء العقد من جانب صاحب العمل لعدم تحقق الشروط المتفق عليها في الاتفاق المبدئي أو العقد المبرم بين صاحب العمل ومكتب استقدام العمالة المساعدة.</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">قيام العامل المساعد برفض/توقف العمل بدون سبب يرجع لصاحب العمل.</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">انقطاع العامل المساعد عن العمل (الهروب).</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>



                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-right">الملاحظات</th>
                                                                    <th>حالات رد مبلغ استقدام العامل المساعد لصاحب العمل بعد فترة التجربة  (6) أشهر</th>
                                                                    <th style="width: 50px;">✓</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">	قيام العامل المساعد برفض العمل/توقف بدون سبب يرجع لصاحب العمل</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-right"></td>
                                                                    <td class="text-right">   انقطاع العامل المساعد عن العمل (هروب)</td>
                                                                    <td><input type="checkbox" class="form-check-input"></td>
                                                                </tr>
                                                    
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="8" class="text-right">
                                                        <div class="p-3 border-top">
                                                            <p style="color:red">في حال وجود أسباب مالم تذكر في الجدول أعلاه، لا يحق لصاحب العمل المطالبة بالمبلغ المتبقي من مبلغ الاستقدام.</p>
                                                            <p> اسباب أخرى</p>
                                                            <p> .........................................................................................................</p>
                                                            <p>يلتزم المكتب برد كامل مبلغ الاستقدام في حال تم ارجاع العامل المساعد خلال الشهر الاول.</p>
                                                            <p>يلتزم المكتب برد كامل مبلغ الاستقدام + الرسوم الحكومية في حال تم ارجاع العامل المساعد خلال فترة التجربة إذا ثبت عدم لياقة العامل الصحية لتأدية الخدمة المساعدة المطلوبة خلال فترة التجربة.</p>
                                                            <p>يلتزم المكتب برد مبلغ الاستقدام لصاحب العمل خلال 14 يوم من تاريخ إرجاع العامل الى مكتب استقدام العمالة المساعدة. أو الإبلاغ عن انقطاعه عن العمل. على أن يلتزم صاحب العمل في حال ارجـاع العامل إلغاء تصريح عمل العامل المساعد وتسليم جواز سفره.</p>

                                                            <p> 
                                يتم احتساب المبلغ الذي يلتزم مكتب استقدام العمالة المساعدة برده لصاحب العمل كالآتي(إجمالي تكلفة الاستقدام ÷ مدة عقد العامل المساعدة بالأشهر) × المدة المتبقية من مدة عقد العمل. في حال وجود أسباب مالم تذكر في الجدول أعلاه، لا يحق لصاحب العمل المطالبة بالمبلغ المتبقي من مبلغ الاستقدام
                            </p>
                                

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 32px; direction: rtl;">
                                <span>
                                    توقيع مكتب الاستقدام:
                                    <span style="display: inline-block; min-width: 120px; border-bottom: 1px dotted #000;">&nbsp;</span>
                                </span>
                                <span>
                                    تاريخ ارجاع العامل:
                                    <span style="display: inline-block; min-width: 120px; border-bottom: 1px dotted #000; text-align: center;">
                                        @if($con1->returnInfo?->created_at)
                                            {{ $con1->returnInfo->created_at }}
                                        @else
                                            &nbsp;
                                        @endif
                                    </span>
                                </span>
                                                        <span>
                                توقيع صاحب العمل:
                                      <span class="signature-line">  <img src="{{$con1?->signature}}" style="width: 50%; max-width: 200px;"></span>
           

                                    
                                    </span>
                                </div>

                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>
