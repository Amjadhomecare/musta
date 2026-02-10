<html dir="rtl" lang="ar"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Contract</title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" media="print">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<style>
/* Remove the navbar's default margin-bottom and rounded borders */ 
@page {
size: A4;
margin: 0;
}
@media print {
html, body {
width: 210mm;
height: 297mm;
}
/* ... the rest of the rules ... */
}
table {
boredr:1px solid;
}
.bordertop {
border-top: 1px solid !important;
}
 table .tr_head { background: #B68834;color: #FFF;font-weight: bold; }
</style>
  <script>
function printContent(el){
var restorepage = $('body').html();
var printcontent = $('#' + el).clone();
$('body').empty().html(printcontent);
window.print();
$('body').html(restorepage);
}
</script>
</head>
<body>


<div class="container">
  <div class="row" style="width: 210mm;text-align: center;">
  <a href="/get/full-contract-cat4/{{$con4->id}}" class="btn btn-xs btn-danger no-print pull-right" style="width: 100%;position: relative;left: -188px;margin-top: 10px;margin-bottom: 10px;">
      <i class="fa fa-print"></i> English View    </a>

    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="width: 100%;background: #dbdbdb;position: relative;left: -188px;" onclick="printContent('print');">
      <i class="fa fa-print"></i> Print    </button>
  </div>
</div>
<div class="container" style="width: 210mm" id="print">  

  <div class="row" style="border: 1px solid">
      <table class="table" style="margin-top: 2px;">
          <tbody><tr class="tr_head">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_eng_p1/mohregg.jpg">
            </td>
          </tr>
      </tbody></table>

      <table class="table">
          <tbody><tr class="tr_head">
            <td align="center" class="bordertop">
               <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_1.png">
            </td>
          </tr>
      </tbody></table>

      <table class="table" border="1">
          <tbody><tr>
            <td align="right" class="bordertop" colspan="3">
              رقم العقد :{{$con4?->Contract_ref}}            </td>
          </tr>

          <tr>
          
            <td align="right" class="bordertop">التاريخ :{{$con4?->created_at}}</td>
            <td align="right" class="bordertop"> الإمارة: : {{env('company_emirate') ?? "Dubai" }}</td>
          </td></tr>
          <tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop" colspan="3">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar1_2.png">
            </td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: -21px;width: 5%;float: right;" border="1">
          
          <tbody><tr>
            <td align="right" class="bordertop" style="background: #b68a35">
            <img src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/first.png" style="height: 73%;"></td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: -21px;width: 94%;float: right;position: relative;right: 6px;" border="1">
        <tbody><tr style="border-right: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-right: #FFF;">اسم مركز الخدمة تدبير  : {{env('company_name') ?? "NA" }}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-right: #FFF;">و ينوب عنها : {{$con4?->created_by}}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-right: #FFF;">رﻗم المنشأة : 
          </td>
          <td class="bordertop" width="50%" style="border-right: #FFF;">رﻗم الرخصة : {{env('company_License_no') ?? "NA" }} </td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-right: #FFF;">الهاتف الأرضي : {{env('company_phone') ?? "NA" }}</td>
          <td class="bordertop" width="50%" style="border-right: #FFF;">الهاتف المتحرك :  {{env('company_phone') ?? "NA" }}0</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-right: #FFF;">صندوق البريد : 36067</td>
          <td class="bordertop" width="50%" style="border-right: #FFF;">البريد الإلكتروني :  {{env('company_email') ?? "NA" }}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-right: #FFF;">العنوان (مكان العمل) : {{env('company_emirate') ?? "NA" }}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-right: #FFF;">الاسم : {{$con4?->customerInfo?->name}}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td class="bordertop" width="50%" style="border-right: #FFF;">الجنس : </td>
          <td class="bordertop" width="50%" style="border-right: #FFF;">الجنسية : {{$con4?->customerInfo?->nationality ?? NAN}}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
      
          <td class="bordertop" width="50%" style="border-right: #FFF;">رقم الهوية : {{$con4?->customerInfo?->idNumber}}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
           <td class="bordertop" width="50%" style="border-right: #FFF;">البريد الإلكتروني : {{$con4?->customerInfo?->email}} </td>
           <td class="bordertop" width="50%" style="border-right: #FFF;">هاتف : {{$con4?->customerInfo?->phone}}</td>
        </tr>

        <tr style="border-right: 1px solid #FFF;">
          <td colspan="2" class="bordertop" style="border-right: #FFF;">العنوان :{{$con4?->customerInfo?->address}} </td>
        </tr>
      </tbody></table>

      <table class="table">
          <tbody><tr>
            <td align="center" style="font-weight: bold;border-top: none;">تمهيد</td>
          </tr>
          <tr>
            <td align="right" style="border-top: none;">يرغب الطرف الثاني في أن يعمل لديه عامل خدمة مساعدة، لذا توجه الطرف الأول طالباً منه تمكينه من ذلك من خلال إلحاق هذا العامل على أن يعمل لديه تحت إدارته وإشرافه، وبعد أن أقرأ الطرفان بكامل أهليتهما للتعاقد ، فقد اتفقا على الشروط الآتية. </td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_2.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">اﺗفق الطرفان على أن يقوم الطرف الأول بتوفير عامل خدمة مساعدة بنظام تشغيل الباقة المرنـة وتقديم خدمـاتـه للطــرف           الثـاني ، وفقا لما يكلـفه به في حدود مجال عمله بمهــنة (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) ، وبيانـات العـامل هي :
            </td>
          </tr>
      </tbody></table>


      <table class="table" border="1">
          <tbody><tr>
            <td class="bordertop" colspan="3">الاسم : {{$con4->maidInfo?->name}}</td>
          </tr>

          <tr>
            <td align="right" class="bordertop" width="45%">الجنسية  : {{$con4->maidInfo?->nationality}}</td>
            <td align="right" class="bordertop">الجنس : Female</td>
            <td align="right" class="bordertop">العمر :  {{$con4->maidInfo?->age}}</td>

          </tr>

          <tr>
            <td class="bordertop" align="right" colspan="2">اللغات : 
              English              -Arabic                                        </td>
            <td class="bordertop">الحالة الاجتماعية :  {{$con4->maidInfo?->marital_status}}</td>
          </tr>
         <tr>
           <td class="bordertop" colspan="3" align="right">المهارات :</td>
         </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_3.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">مدة هذا العقد ( السـاعة/اليوم/الأسبـوع/الشـهر ) تبدأ من (....{{$con4?->created_at}}.....) ، وتنتهي في (...{{$con4?->returnInfo?->created_at}}...) و قابلة للتجديد لمدة أو مدد ، مماثلة بموجب إشعـار من الطرف الثاني للطرف الأول قبل نهاية المدة المتفق عليها ، و قد أتفق الطرفين بأن يقوم الطرف الثاني بإبلاغ الطرف الأول رغبته بالتجديد قبل انتهاء العقد الحالي بمدة (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;).</td>
          </tr>
      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
                <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_4.png">
            </td>
          </tr>
          <tr>
            <td align="right" style="border-top: none;font-weight: bold;">يدفع الطرف الثاني للطرف الأول ما يلي :</td>
          </tr>
          <tr>
            <td align="right" style="border-top: none;">
              1.  تكلفة الساعة / اليوم / الأسبوع / الشهر ، مقابل تقديم الخدمات المشار إليها أعلاه ، وفقاً لقيمة الساعة /اليوم / الأسبوع / الشهر ، والتي هي ({{$con4?->installmentInfo->last()?->amount}}) درهم وذلك بمبلغ إجمالي و قدره ( AED {{$con4?->installmentInfo->last()?->amount}}) درهم إماراتي.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              2.  المبلغ الإجمالي لعدد الساعات / الأيام / الأسابيع الفعلية مقدما قبل أداء الخدمة المطلوبة.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              دفعة مقدمة عن كل شهر ، كما يلتزم بدفع بقية الأشهر ( نقداً- شيكات – بطاقة انتمائية ) تعادل مدة العقد ، على أن يتم الخصم بشكل شهري
            </td>
          </tr>

          
      </tbody></table>



      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_5.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;font-weight: bold;">
              يلتزم الطرف الأول بالآتي :
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              1.  تقديم خدمات العامل (الباقة المرنة) المذكور أعلاه للطرف الثاني.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              2.  ضمان ما يثبت لياقة العامل وحالته الصحية والنفسية و المهنية للعمل المطلوبالقيام به.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              3.  دفع الراتب الشهري للعامل بالإضافة لباقي المستحقات القانونية المقررة.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              4.  ضمان توافر الشروط و المستلزمات، التي تحددها النظم القانونية المعمول بها داخل دولة الإمارات، في العامل لممارسة مهنة أو وظيفة أو عمل معين.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              5.  تقديم عامل بديل عن العامل المقدم خدماته بنفس المؤهلات والخبرات، للقيام بنفس العمل الذي طلب من أجله ، وذلك بناءً على رغبة الطرف الثاني في أي وقت، أو في حالة تغيب العامل عن العمل أو رفضه للعمل ، وذلك خلال 24 ساعة من وقت إبلاغ الطرف الثاني للطرف الأول بذلك.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              6.  عدم استبدال العامل بنظام الباقات الأسبوعية / الشهرية المقدم خدماته إلا بعد أخد الموافقة الكتابية من الطرف الثاني.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              7.  استبدال العامل بعامل أخر للطرف الأول كلما دعت حاجته لذلك و مرتين فقط خلال فترة التقاعد كاملة.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              8.  تعويض الطرف الثاني مقابل ما يتسبب العامل في فقده أو إتلافه أو تدميره من ممتلكات الطرف الثاني بعد ثبوت ذلك من الجهات المختصة.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              9.  توفير كل ما تفرضه النظم القانونية المعمول بها في الوزارة ومتعلقة بذات الشأن على صاحب العمل ، ما لم يتفق الطرفان على خلاف ذلك ، وفي جميع الأحوال ، وبصرف النظر عن مثال الاتفاقات بين الطرفين ( الأول و الثاني ) لا يعفي الطرف الأول من المسؤولية في حال امتناع المستفيد عن الوفاء بما التزم به، ومع تحميل الممتنع المسؤولية القانونية.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              10. أية التزامات أخرى تفرضها عليه النظم القانونية ذات العلاقة والمعمول بها في الوزارة.
            </td>
          </tr>
      </tbody></table>


      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_6.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;font-weight: bold;">
              يلتزم الطرف الثاني بالآتي :
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              1.  سداد القيمة المتفق عليها في البند الثالث.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              2.  معاملة العامل معاملة حسنة تحفظ كرامته وسلامة بدنه.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              3.  إشعار الطرف الأول بأية مخالفات ، أو أخطاء يرتكبها العامل لاتخاذ ما يلزم من إجراءات بحقه.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              4.  عدم تشغيل العامل بمهنة تختلف عن طبيعة عمله إلا برضاه وبشرط أن تكون من المهن المشمولة بالقانون.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              5.  إبلاغ الطرف الأول في حال انقطاع العامل عن العمل أو رفضه العمل خلال (24) ساعة من وقت الانقطاع أو رفض العامل مع تسليم جميع متعلقات العامل للطرف الأول.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              6.  إذا كانت مهنة العامل سائق يشترط أن تكون السيارة المسلمة له مؤمن عليها وسارية الترخيص.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              7.  توفير سكن لائق للعامل ما لم يتم الاتفاق مع الطرف الثاني على خلاف ذلك.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              8.  تقديم احتياجات العامل من وجبات الطعام و الملابس المناسبة لأداء العمل. ما لم يتم الاتفاق مع الطرف الأول على خلاف ذلك.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              9.  توفير بيئة و أدوات العمل للعامل بما يتوافق مع الأنظمة القانونية المعمول بها في الدولة.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              10. عدم تشغيل العامل لدى الغير.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              11. توفير للعامل كافة ما اتفق عليه مع الطرف للعامل.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              12. أية التزامات أخرى تفرضها عليه النظم القانونية ذات العلاقة و المعمول بها في الوزارة.
            </td>
          </tr>

      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_7.png">
            </td>
          </tr>

          

          <tr>
            <td align="right" style="border-top: none;">
              لا يعتد بأي اتفاقات خارجية تتعلق بموضوع هذا العقد ، سواء كانت سابقة ، أو لاحقة لتوقيعه ، و تعتبر كأن لم تكن.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              1.  دون الإخلال بحق الوزارة في اتخاذ الإجراءات القانونية تجاه الطرف المخل بالعقد ، في حالة حدوث خلاف بين الطرفين يتم اللجوء للوزارة لتسوية الموضوع ودياً بين الطرفين و اتخاذ ما تراه مناسباً.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              2.  فيما لم يرد به نص هذا العقد تسري أحكام القانون الاتحادي رقم ( 9 ) لسنة2022  ، بشأن عمال الخدمة المساعدة ، ولائحته التنفيذية ، و باقي النظم القانونية السارية بوزارة الموارد البشرية و التوطين في هذا الشأن ، وتكون محاكم دولة الإمارات هي جهة الاختصاص بنظر أية منازعة متعلقة بهذا العقد.
            </td>
          </tr>
          

      </tbody></table>

      <table class="table" style="margin-top: 2px;">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_8.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              1.  ينتهي هذا العقد بانتهاء المدة المتفق عليها.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              2.  يجوز للطرفين الاتفاق على إنهاء هذا العقد قبل انتهاء مدته ، بشرط أن يكون الاتفاق على الإنهاء مكتوبا.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              3.  يحق لأي من الطرفين إنهاء هذا العقد في حال إخلال الطرف الآخر بأي بند من بنوده.
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;">
              4.  في حال رفض العامل العمل ، أو رغبة الطرف الثاني في إنهاء العقد ، يلتزم الطرف الأول بإرجاع المبلغ المتبقي عن مدة الخدمة المتفق عليها.
            </td>
          </tr>

          

      </tbody></table>

      <table class="table" border="1">
          <tbody><tr style="background: #B68834;color: #FFF;font-weight: bold;">
            <td align="center" class="bordertop" colspan="2">
              <img style="width: 100%;" src="https://nextmetaerp.s3.eu-north-1.amazonaws.com/contract_arb_p4/pkgar4_9.png">
            </td>
          </tr>

          <tr>
            <td align="right" style="border-top: none;" colspan="2">
              حرر هذا العقد من ثلاث نسخ بعد أن تم توقيعه من الطرفين ، تسلم إحداها الطرف الأول و الأخرى الطرف الثاني و تودع الثالثة لدى الوزارة.
            </td>
          </tr>
          <tr>
            <th class="bordertop">اﻟطرف اﻷول</th>
            <th class="bordertop">اﻟطرف الثاني</th>
          </tr>

          <tr>
            <td align="right" class="bordertop" width="50%">الاسم :  {{env('company_name') ?? "NA" }}</td>
            <td align="right" class="bordertop">الاسم : {{$con4?->customerInfo?->name}}</td>
          </tr>

          <tr>
            <td class="bordertop">التوقيع</td>
            <td class="bordertop">التوقيع  <img src="{{$con4?->signature}}" style="width: 50%; max-width: 200px;"></td>
          </tr>
         
      </tbody></table>
      التاريخ :  {{$con4?->created_at}}  </div>
</div>





</body>

</html>